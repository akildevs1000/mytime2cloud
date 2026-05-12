// IPC handlers — the bridge between renderer and engine.
// All engine operations live here. Renderer asks via window.api, this code
// answers.

import { ipcMain, BrowserWindow, app } from "electron";
import { existsSync, mkdirSync, readdirSync, writeFileSync } from "node:fs";
import { dirname, resolve } from "node:path";
import {
  ConnectionManager,
  loadRecipe,
  loadSchema,
  buildDump,
  runRestore,
  runVerify,
  runAudit,
  type Recipe,
  type DumpResult,
  type VerifyResult,
  type AuditResult,
  type RestoreResult
} from "../engine/index.js";

let connections: ConnectionManager | null = null;

// Where to look for recipes/ and config/connections.yaml.
//   dev (electron-vite): the project folder (process.cwd()).
//   packaged portable:   PORTABLE_EXECUTABLE_DIR (set by the launcher; the dir
//                        the user actually double-clicked).
//   packaged nsis:       app.getPath("userData") — %APPDATA%\Tenant Migrator\.
//                        Install dir (Program Files) is read-only for non-
//                        admins, so we can't keep editable config there.
function baseDir(): string {
  if (!app.isPackaged) return process.cwd();
  if (process.env.PORTABLE_EXECUTABLE_DIR) return process.env.PORTABLE_EXECUTABLE_DIR;
  return app.getPath("userData");
}

function recipesDir(): string {
  return resolve(baseDir(), "recipes");
}

function connectionsFile(): string {
  return resolve(baseDir(), "config/connections.yaml");
}

// First-run scaffold: if the operator launched a fresh install and neither
// folder exists yet, create them with a documented example so they know what
// to fill in. We never write connections.yaml itself (passwords) — only the
// .example.yaml template.
const CONNECTIONS_EXAMPLE_YAML = `# Copy this file to connections.yaml (same folder) and fill in real values.
# Each connection is a named entry. Recipes reference these by name.

connections:
  local-snapshot:
    host: 127.0.0.1
    port: 5432
    user: postgres
    password: CHANGE_ME
    database: mytime2cloud-local

  live-source:
    host: CHANGE_ME
    port: 5432
    user: postgres
    password: CHANGE_ME
    database: mytime2cloud
    readonly: true

  live-target:
    host: CHANGE_ME
    port: 5432
    user: CHANGE_ME
    password: CHANGE_ME
    database: mytime2cloud-v2
`;

function scaffoldIfMissing(): { scaffolded: boolean; path: string } {
  const base       = baseDir();
  const cfgDir     = resolve(base, "config");
  const recDir     = resolve(base, "recipes");
  const examplePath = resolve(cfgDir, "connections.example.yaml");
  let scaffolded = false;
  if (!existsSync(cfgDir)) { mkdirSync(cfgDir, { recursive: true }); scaffolded = true; }
  if (!existsSync(recDir)) { mkdirSync(recDir, { recursive: true }); scaffolded = true; }
  if (!existsSync(examplePath)) { writeFileSync(examplePath, CONNECTIONS_EXAMPLE_YAML, "utf8"); scaffolded = true; }
  return { scaffolded, path: base };
}

function getConnections(): ConnectionManager {
  if (!connections) connections = ConnectionManager.load(connectionsFile());
  return connections;
}

function emitProgress(event: { kind: string; message?: string; ordinal?: number; total?: number }) {
  for (const w of BrowserWindow.getAllWindows()) {
    w.webContents.send("engine:progress", event);
  }
}

// Returns an onEvent callback for runRestore that throttles per-statement
// updates: emit progress every N statements OR every X ms, whichever first.
// Non-statement events (begin/commit/error/rollback) pass through untouched.
function makeRestoreProgressEmitter(label: string, everyN = 500, everyMs = 5000) {
  let lastOrdinal = 0;
  let lastTime    = Date.now();
  let startTime   = Date.now();
  return (e: { kind: string; ordinal?: number; total?: number; message?: string; affected?: number }) => {
    if (e.kind === "begin") {
      startTime = Date.now();
      emitProgress({ kind: "info", message: `${label}: begin (${e.total ?? "?"} statements)` });
      return;
    }
    if (e.kind === "statement" && e.ordinal && e.total) {
      const now = Date.now();
      if (e.ordinal - lastOrdinal >= everyN || now - lastTime >= everyMs) {
        const pct      = ((e.ordinal / e.total) * 100).toFixed(1);
        const elapsed  = Math.floor((now - startTime) / 1000);
        const rate     = elapsed > 0 ? Math.round(e.ordinal / elapsed) : 0;
        const remain   = rate > 0 ? Math.ceil((e.total - e.ordinal) / rate) : 0;
        emitProgress({
          kind:    "info",
          message: `${label}: ${e.ordinal.toLocaleString()}/${e.total.toLocaleString()} (${pct}%)` +
                   `  ${rate}/s  eta ${remain}s`,
          ordinal: e.ordinal,
          total:   e.total
        });
        lastOrdinal = e.ordinal;
        lastTime    = now;
      }
      return;
    }
    if (e.kind === "commit" || e.kind === "rollback" || e.kind === "error") {
      emitProgress({ kind: e.kind, message: e.message, ordinal: e.ordinal, total: e.total });
    }
  };
}

// Per-call overrides from the UI. The recipe YAML is never modified.
export interface Overrides {
  source?:    string;     // connection name
  target?:    string;     // connection name
  companyId?: string;     // tenant id (e.g. "85"); overrides recipe.company_id
}

function applyOverrides(recipe: Recipe, overrides?: Overrides): Recipe {
  const conns = getConnections();
  const next: Recipe = { ...recipe };

  if (overrides) {
    const known = new Set(conns.list());
    if (overrides.source && !known.has(overrides.source)) {
      throw new Error(`Unknown source connection "${overrides.source}". Known: ${[...known].join(", ")}`);
    }
    if (overrides.target && !known.has(overrides.target)) {
      throw new Error(`Unknown target connection "${overrides.target}". Known: ${[...known].join(", ")}`);
    }
    if (overrides.companyId !== undefined && !/^\d+$/.test(String(overrides.companyId))) {
      throw new Error(`Invalid company_id override "${overrides.companyId}" — must be a positive integer.`);
    }
    if (overrides.source)    next.source     = overrides.source;
    if (overrides.target)    next.target     = overrides.target;
    if (overrides.companyId) next.company_id = String(overrides.companyId);
  }

  // Guard: a readonly connection must never be used as a write target. This
  // catches any caller (UI override or recipe default) that picks a source-only
  // DB as the destination.
  if (conns.get(next.target).readonly) {
    throw new Error(
      `Connection "${next.target}" is marked readonly and cannot be used as a target. ` +
      `Pick a different target or remove "readonly: true" from config/connections.yaml.`
    );
  }

  if (overrides && (overrides.source || overrides.target || overrides.companyId)) {
    emitProgress({
      kind:    "info",
      message: `using overrides: source=${next.source}, target=${next.target}, company_id=${next.company_id} ` +
               `(recipe defaults: ${recipe.source} / ${recipe.target} / ${recipe.company_id})`
    });
  }
  return next;
}

export function registerIpcHandlers() {
  scaffoldIfMissing();

  // -----------------------------------------------------------------------
  // recipes:list / recipes:load
  // -----------------------------------------------------------------------
  ipcMain.handle("recipes:list", async () => {
    const dir = recipesDir();
    try {
      return readdirSync(dir)
        .filter(f => f.endsWith(".yaml") || f.endsWith(".yml"))
        .sort();
    } catch (err) {
      return { error: `${(err as Error).message} (looked in ${dir})` };
    }
  });

  ipcMain.handle("recipes:load", async (_e, file: string) => {
    try {
      const path = resolve(recipesDir(), file);
      const recipe = loadRecipe(path);
      return { recipe, path };
    } catch (err) {
      return { error: (err as Error).message };
    }
  });

  // -----------------------------------------------------------------------
  // companies:list — query the given source connection's `companies` table
  // -----------------------------------------------------------------------
  ipcMain.handle("companies:list", async (_e, sourceConnection: string) => {
    try {
      const conns = getConnections();
      if (!conns.list().includes(sourceConnection)) {
        throw new Error(`Unknown connection "${sourceConnection}"`);
      }
      const pool = conns.pool(sourceConnection);
      const r = await pool.query<{ id: string; name: string }>(
        "SELECT id::text AS id, COALESCE(name, '(unnamed)') AS name FROM companies ORDER BY id"
      );
      return r.rows;
    } catch (err) {
      return { error: (err as Error).message };
    }
  });

  // -----------------------------------------------------------------------
  // tenant:status — quick snapshot of a tenant on a given DB.
  //
  //   companyExists  true if `companies.id = X` exists
  //   companyName    the name (when companyExists)
  //   rowCounts      counts for a small set of "signal" tables
  //
  // Used by the UI to tell you "this is a fresh migration" vs "this tenant
  // already has data on target".
  // -----------------------------------------------------------------------
  ipcMain.handle("tenant:status", async (_e, connection: string, companyId: string) => {
    try {
      const conns = getConnections();
      if (!conns.list().includes(connection)) {
        throw new Error(`Unknown connection "${connection}"`);
      }
      if (!/^\d+$/.test(String(companyId))) {
        throw new Error(`Invalid company_id "${companyId}"`);
      }
      const pool = conns.pool(connection);

      const cmp = await pool.query<{ id: string; name: string }>(
        "SELECT id::text AS id, name FROM companies WHERE id::text = $1",
        [String(companyId)]
      );
      const companyExists = (cmp.rowCount ?? 0) > 0;
      const companyName = cmp.rows[0]?.name ?? null;

      // Signal tables: a small set that tells you quickly whether anything
      // tenant-related lives on this DB.
      const signalTables = [
        "employees", "users", "branches", "company_branches",
        "attendances", "attendance_logs", "activities", "devices"
      ];

      const rowCounts: { table: string; count: number }[] = [];
      for (const t of signalTables) {
        try {
          const r = await pool.query<{ c: string }>(
            `SELECT COUNT(*)::text AS c FROM "${t}" WHERE company_id::text = $1`,
            [String(companyId)]
          );
          rowCounts.push({ table: t, count: Number(r.rows[0].c) });
        } catch {
          // Table or company_id column might not exist — skip silently.
        }
      }

      const totalRelated = rowCounts.reduce((s, x) => s + x.count, 0);
      let state: "fresh" | "partial-orphan" | "exists";
      if (!companyExists && totalRelated === 0)       state = "fresh";
      else if (!companyExists && totalRelated > 0)    state = "partial-orphan";
      else                                            state = "exists";

      return { state, companyExists, companyName, rowCounts, totalRelated };
    } catch (err) {
      return { error: (err as Error).message };
    }
  });

  // -----------------------------------------------------------------------
  // connections:list
  // -----------------------------------------------------------------------
  ipcMain.handle("connections:list", async () => {
    try {
      const c = getConnections();
      return c.list().map(name => {
        const cfg = c.get(name);
        return {
          name,
          host: cfg.host, port: cfg.port,
          user: cfg.user, database: cfg.database,
          readonly: cfg.readonly === true
        };
      });
    } catch (err) {
      return { error: (err as Error).message };
    }
  });

  // -----------------------------------------------------------------------
  // engine:audit
  // -----------------------------------------------------------------------
  ipcMain.handle("engine:audit", async (_e, file: string, overrides?: Overrides): Promise<AuditResult | { error: string }> => {
    try {
      emitProgress({ kind: "info", message: "audit: loading recipe & schemas" });
      const recipe = applyOverrides(loadRecipe(resolve(recipesDir(), file)), overrides);
      const conns  = getConnections();
      const tgt    = conns.pool(recipe.target);
      const src    = conns.pool(recipe.source);
      const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
      const result = await runAudit(tgt, srcSchema, tgtSchema);
      emitProgress({ kind: "info", message: "audit: done" });
      return result;
    } catch (err) {
      const msg = (err as Error).message;
      emitProgress({ kind: "error", message: msg });
      return { error: msg };
    }
  });

  // -----------------------------------------------------------------------
  // engine:verify
  // -----------------------------------------------------------------------
  ipcMain.handle("engine:verify", async (_e, file: string, overrides?: Overrides): Promise<VerifyResult | { error: string }> => {
    try {
      emitProgress({ kind: "info", message: "verify: loading recipe & schemas" });
      const recipe = applyOverrides(loadRecipe(resolve(recipesDir(), file)), overrides);
      const conns  = getConnections();
      const src    = conns.pool(recipe.source);
      const tgt    = conns.pool(recipe.target);
      const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
      const result = await runVerify(recipe, src, tgt, srcSchema, tgtSchema);
      emitProgress({ kind: "info", message: `verify: ${result.matched} matched, ${result.missing} missing, ${result.extra} extra` });
      return result;
    } catch (err) {
      const msg = (err as Error).message;
      emitProgress({ kind: "error", message: msg });
      return { error: msg };
    }
  });

  // -----------------------------------------------------------------------
  // engine:dump
  // -----------------------------------------------------------------------
  ipcMain.handle("engine:dump", async (_e, file: string, overrides?: Overrides): Promise<DumpResult | { error: string }> => {
    try {
      emitProgress({ kind: "info", message: "dump: loading recipe & schemas" });
      const recipe = applyOverrides(loadRecipe(resolve(recipesDir(), file)), overrides);
      const conns  = getConnections();
      const src    = conns.pool(recipe.source);
      const tgt    = conns.pool(recipe.target);
      const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
      emitProgress({ kind: "info", message: "dump: building" });
      const result = await buildDump({ recipe, sourcePool: src, targetPool: tgt, sourceSchema: srcSchema, targetSchema: tgtSchema });
      const total = result.tableStats.reduce((a, b) => a + b.rows, 0);
      const remap = [...result.remap.values()].reduce((a, b) => a + b.length, 0);
      emitProgress({ kind: "info", message: `dump: ${total} INSERTs across ${result.tableStats.filter(s => s.status === "ok").length} tables, ${remap} remapped` });
      return result;
    } catch (err) {
      const msg = (err as Error).message;
      emitProgress({ kind: "error", message: msg });
      return { error: msg };
    }
  });

  // -----------------------------------------------------------------------
  // engine:restore
  // -----------------------------------------------------------------------
  ipcMain.handle("engine:restore", async (_e, file: string, dryRun = false, overrides?: Overrides): Promise<RestoreResult | { error: string; dryRun?: boolean }> => {
    try {
      emitProgress({ kind: "info", message: dryRun ? "dry-run: building dump" : "restore: building dump" });
      const recipe = applyOverrides(loadRecipe(resolve(recipesDir(), file)), overrides);
      const conns  = getConnections();
      const src    = conns.pool(recipe.source);
      const tgt    = conns.pool(recipe.target);
      const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
      const dump = await buildDump({ recipe, sourcePool: src, targetPool: tgt, sourceSchema: srcSchema, targetSchema: tgtSchema });
      if (dryRun) {
        const total = dump.tableStats.reduce((a, b) => a + b.rows, 0);
        emitProgress({ kind: "info", message: `dry-run: would emit ${total} INSERTs (no writes performed)` });
        return { inserted: 0, skipped: 0, errors: 0, durationMs: 0 };
      }
      emitProgress({ kind: "info", message: "restore: running on target" });
      const result = await runRestore(tgt, dump.sql, makeRestoreProgressEmitter("restore"));
      emitProgress({ kind: "info", message: `restore: inserted=${result.inserted}, skipped=${result.skipped}, errors=${result.errors}, ${result.durationMs} ms` });
      return result;
    } catch (err) {
      const msg = (err as Error).message;
      emitProgress({ kind: "error", message: msg });
      return { error: msg };
    }
  });

  // -----------------------------------------------------------------------
  // engine:sync
  // -----------------------------------------------------------------------
  ipcMain.handle("engine:sync", async (_e, file: string, overrides?: Overrides) => {
    try {
      const recipe = applyOverrides(loadRecipe(resolve(recipesDir(), file)), overrides);
      const conns  = getConnections();
      const src    = conns.pool(recipe.source);
      const tgt    = conns.pool(recipe.target);
      emitProgress({ kind: "info", message: "sync: dump" });
      const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
      const dump = await buildDump({ recipe, sourcePool: src, targetPool: tgt, sourceSchema: srcSchema, targetSchema: tgtSchema });
      emitProgress({ kind: "info", message: "sync: restore" });
      const r = await runRestore(tgt, dump.sql, makeRestoreProgressEmitter("sync: restore"));
      emitProgress({ kind: "info", message: "sync: verify" });
      const v = await runVerify(recipe, src, tgt, srcSchema, tgtSchema);
      emitProgress({ kind: "info", message: `sync: done — inserted=${r.inserted}, matched=${v.matched}, missing=${v.missing}, extra=${v.extra}` });
      return { dumpStats: dump.tableStats, restore: r, verify: v };
    } catch (err) {
      const msg = (err as Error).message;
      emitProgress({ kind: "error", message: msg });
      return { error: msg };
    }
  });
}

export async function disposeIpcResources() {
  if (connections) {
    await connections.closeAll();
    connections = null;
  }
}
