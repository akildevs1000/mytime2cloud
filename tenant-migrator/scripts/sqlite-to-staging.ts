// SQLite → staging Postgres copier.
// Reads tables from a SQLite file and INSERTs them into a Postgres staging DB
// whose schema already mirrors the live target (see plan / CLAUDE.md).
// No DDL, no tenant filtering, no id remapping — that's the engine's job on
// the staging → live target hop.

import Database from "better-sqlite3";
import type { PoolClient } from "pg";
import { ConnectionManager } from "../src/engine/connection.js";
import { loadSchema, type SchemaMap } from "../src/engine/schema.js";

const SKIP_TABLES = new Set([
  "migrations",
  "permissions",
  "personal_access_tokens",
  "timezone_default_jsons",
  "shift_types",
  "device_statuses",
  "modules"
]);

const BATCH_SIZE = 500;

type Status =
  | "copied"
  | "skipped-system"
  | "skipped-not-in-target"
  | "skipped-empty"
  | "error";

interface TableReport {
  table:       string;
  status:      Status;
  rowsCopied?: number;
  rowsSource?: number;
  error?:      string;
}

async function main(): Promise<void> {
  const [sqlitePath, connName] = process.argv.slice(2);
  if (!sqlitePath || !connName) {
    console.error("Usage: tsx scripts/sqlite-to-staging.ts <sqlite-file> <connection-name>");
    process.exit(1);
  }

  const conns = ConnectionManager.load();
  const target = conns.get(connName);
  if (target.readonly) {
    console.error(`Connection "${connName}" is marked readonly. Refusing to write.`);
    process.exit(1);
  }
  const pool = conns.pool(connName);

  console.log(`Loading target schema from "${connName}" (${target.database})...`);
  const targetSchema = await loadSchema(pool);
  console.log(`  ${targetSchema.size} tables in target`);

  console.log(`Opening SQLite: ${sqlitePath}`);
  const sqlite = new Database(sqlitePath, { readonly: true, fileMustExist: true });

  const sqliteTables = sqlite
    .prepare("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    .all() as { name: string }[];
  console.log(`  ${sqliteTables.length} tables in SQLite`);
  console.log("");

  const report: TableReport[] = [];

  // One client / one transaction for the whole run.
  // session_replication_role=replica suppresses FK & trigger checks during the
  // bulk load (same mechanism pg_restore uses) — staging FKs are non-deferrable
  // and tables come in alphabetical order, not topological. Safe because the
  // staging DB is throwaway; the engine re-validates referential integrity on
  // the staging → live target hop.
  const client = await pool.connect();
  try {
    await client.query("BEGIN");
    await client.query("SET session_replication_role = 'replica'");

    for (const { name: table } of sqliteTables) {
      if (SKIP_TABLES.has(table)) {
        report.push({ table, status: "skipped-system" });
        continue;
      }
      if (!targetSchema.has(table)) {
        report.push({ table, status: "skipped-not-in-target" });
        continue;
      }
      const totalRows = (sqlite.prepare(`SELECT COUNT(*) AS n FROM "${table}"`).get() as { n: number }).n;
      if (totalRows === 0) {
        report.push({ table, status: "skipped-empty", rowsSource: 0 });
        continue;
      }
      try {
        const copied = await copyTable(sqlite, client, targetSchema, table);
        report.push({ table, status: "copied", rowsCopied: copied, rowsSource: totalRows });
        console.log(`  ${pad(table, 40)} ${copied}/${totalRows} copied`);
      } catch (e) {
        const msg = e instanceof Error ? e.message : String(e);
        report.push({ table, status: "error", rowsSource: totalRows, error: msg });
        console.log(`  ${pad(table, 40)} ERROR: ${msg}`);
        await client.query("ROLLBACK");
        throw new Error(`copyTable failed for "${table}": ${msg}`);
      }
    }

    await client.query("SET session_replication_role = 'origin'");
    await client.query("COMMIT");
  } finally {
    client.release();
    sqlite.close();
    await conns.closeAll();
  }

  console.log("\n=== REPORT ===");
  const groups: Record<Status, TableReport[]> = {
    "copied":                [],
    "skipped-system":        [],
    "skipped-not-in-target": [],
    "skipped-empty":         [],
    "error":                 []
  };
  for (const r of report) groups[r.status].push(r);

  console.log(`copied:                ${groups["copied"].length}`);
  console.log(`skipped (system seed): ${groups["skipped-system"].length}  (${groups["skipped-system"].map(r => r.table).join(", ")})`);
  console.log(`skipped (not in tgt):  ${groups["skipped-not-in-target"].length}`);
  console.log(`skipped (empty):       ${groups["skipped-empty"].length}`);
  console.log(`errored:               ${groups["error"].length}`);

  if (groups["error"].length > 0) {
    console.error("\nErrors:");
    for (const r of groups["error"]) {
      console.error(`  ${r.table}: ${r.error}`);
    }
    process.exit(2);
  }
}

async function copyTable(
  sqlite:       Database.Database,
  client:       PoolClient,
  targetSchema: SchemaMap,
  table:        string
): Promise<number> {
  const targetCols = targetSchema.get(table)!;
  const targetColSet  = new Set(targetCols.map(c => c.name));
  const targetColType = new Map(targetCols.map(c => [c.name, c.dataType]));

  const sqliteCols = (sqlite.prepare(`PRAGMA table_info("${table}")`).all() as { name: string }[])
    .map(c => c.name);
  const sharedCols = sqliteCols.filter(c => targetColSet.has(c));
  if (sharedCols.length === 0) throw new Error("no shared columns");

  const colList = sharedCols.map(quoteIdent).join(", ");
  const rows = sqlite.prepare(`SELECT ${colList} FROM "${table}"`).all() as Record<string, unknown>[];

  let copied = 0;
  for (let i = 0; i < rows.length; i += BATCH_SIZE) {
    const batch  = rows.slice(i, i + BATCH_SIZE);
    const values: unknown[] = [];
    const tuples: string[]  = [];
    let pi = 1;
    for (const row of batch) {
      const placeholders: string[] = [];
      for (const col of sharedCols) {
        values.push(coerce(row[col], targetColType.get(col)!));
        placeholders.push(`$${pi++}`);
      }
      tuples.push(`(${placeholders.join(", ")})`);
    }
    const sql = `INSERT INTO "${table}" (${colList}) VALUES ${tuples.join(", ")} ON CONFLICT DO NOTHING`;
    const result = await client.query(sql, values);
    copied += result.rowCount ?? 0;
  }
  return copied;
}

function coerce(v: unknown, targetType: string): unknown {
  if (v === null || v === undefined) return null;

  if (targetType === "bigint" || targetType === "integer" || targetType === "smallint") {
    if (typeof v === "string" && v.trim() !== "" && !Number.isNaN(Number(v))) return Number(v);
    if (typeof v === "boolean") return v ? 1 : 0;
    return v;
  }

  if (targetType === "boolean") {
    if (typeof v === "number") return v !== 0;
    if (typeof v === "string") return v === "1" || v.toLowerCase() === "true";
    return v;
  }

  return v;
}

function quoteIdent(name: string): string {
  return `"${name.replace(/"/g, '""')}"`;
}

function pad(s: string, n: number): string {
  return s.length >= n ? s : s + " ".repeat(n - s.length);
}

main().catch(e => {
  console.error("FATAL:", e instanceof Error ? e.stack ?? e.message : e);
  process.exit(1);
});
