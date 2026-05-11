// Dump generator. For each in-scope table, fetches matching source rows and
// emits one INSERT ... VALUES (...) ON CONFLICT DO NOTHING; statement per row.
// Applies id remapping (deterministic, source_id + offset) and FK cascade
// rewrites for child columns whose parent table was remapped.

import { Pool } from "pg";
import { Recipe, expandPlaceholders } from "./recipe.js";
import { SchemaMap, columnIntersection, tablesWithCompanyId } from "./schema.js";
import { detectCollisions, RemapByTable } from "./collisions.js";
import { quoteIdent, quoteLiteral } from "./quote.js";

export interface DumpResult {
  sql:        string;            // the full restore SQL ready to run
  tableStats: TableStats[];      // per-table summary
  remap:      RemapByTable;      // ids that were remapped
  droppedCols: Map<string, string[]>; // cols dropped (in source, missing in target)
}

export interface TableStats {
  table:    string;
  rows:     number;
  remapped: number;
  status:   "ok" | "skipped-no-source" | "skipped-no-target" | "skipped-no-cols" | "excluded";
  where?:   string;
  droppedCols?: string[];
}

interface BuildArgs {
  recipe:        Recipe;
  sourcePool:    Pool;
  targetPool:    Pool;
  sourceSchema:  SchemaMap;
  targetSchema:  SchemaMap;
}

export async function buildDump(args: BuildArgs): Promise<DumpResult> {
  const { recipe, sourcePool, targetPool, sourceSchema, targetSchema } = args;

  // 1. Decide what tables are in scope and what their WHERE clauses are.
  const tableFilters = computeTableFilters(recipe, sourceSchema);

  // 2. Detect collisions and build the remap map.
  const remap = await detectCollisions({
    recipe, sourcePool, targetPool, sourceSchema, targetSchema, tableFilters
  });

  // 3. Build {fk_column -> {old_id: new_id}} per child table from cascades.
  const fkRemapByChild = buildFkRemap(recipe, remap);

  // 4. Emit SQL for each table in order.
  const stats: TableStats[] = [];
  const droppedCols = new Map<string, string[]>();
  const out: string[] = [];

  out.push(buildHeader(recipe, remap));

  for (const [table, where] of tableFilters) {
    if (recipe.table_overrides[table]?.exclude) {
      stats.push({ table, rows: 0, remapped: 0, status: "excluded" });
      continue;
    }
    if (!sourceSchema.has(table)) {
      stats.push({ table, rows: 0, remapped: 0, status: "skipped-no-source" });
      continue;
    }
    if (!targetSchema.has(table)) {
      stats.push({ table, rows: 0, remapped: 0, status: "skipped-no-target" });
      continue;
    }

    const { shared, droppedFromSource } = columnIntersection(sourceSchema, targetSchema, table);
    if (shared.length === 0) {
      stats.push({ table, rows: 0, remapped: 0, status: "skipped-no-cols" });
      continue;
    }
    if (droppedFromSource.length > 0) droppedCols.set(table, droppedFromSource);

    const tableRemap   = mapFromEntries(remap.get(table));
    const fkRemap      = fkRemapByChild.get(table);
    const inserts      = await emitTableInserts(
      sourcePool, table, where, shared, tableRemap, fkRemap
    );
    out.push(`\n-- Table: ${table}   (${inserts.length} rows)   WHERE ${where}` +
             (droppedFromSource.length ? `   (dropped: ${droppedFromSource.join(", ")})` : "") +
             (tableRemap?.size ? `   [REMAP: ${tableRemap.size} id(s)]` : ""));
    out.push(...inserts);

    stats.push({
      table,
      rows: inserts.length,
      remapped: tableRemap?.size ?? 0,
      status: "ok",
      where,
      droppedCols: droppedFromSource.length ? droppedFromSource : undefined
    });
  }

  out.push(buildFooter(recipe));

  return { sql: out.join("\n"), tableStats: stats, remap, droppedCols };
}

// ---------------------------------------------------------------------------
// Internals
// ---------------------------------------------------------------------------

function computeTableFilters(recipe: Recipe, sourceSchema: SchemaMap): Map<string, string> {
  // Order matters only for human-readable output.
  const out = new Map<string, string>();

  // 1) Companies first (special tenant-key override).
  for (const [table, override] of Object.entries(recipe.tenant_key_overrides)) {
    if (sourceSchema.has(table)) {
      out.set(table, expandPlaceholders(override.where, recipe));
    }
  }

  // 2) All tables with a company_id column.
  for (const t of tablesWithCompanyId(sourceSchema)) {
    if (out.has(t)) continue;            // already added (e.g. companies if it had company_id)
    const ovr = recipe.table_overrides[t];
    if (ovr?.where) {
      out.set(t, expandPlaceholders(ovr.where, recipe));
    } else {
      out.set(t, `company_id = ${recipe.company_id}`);
    }
  }

  // 3) Linked tables (manually configured).
  for (const lt of recipe.linked_tables) {
    if (out.has(lt.table)) continue;
    out.set(lt.table, expandPlaceholders(lt.where, recipe));
  }

  return out;
}

function buildFkRemap(recipe: Recipe, remap: RemapByTable): Map<string, Map<string, Map<string, string>>> {
  // child -> column -> (old -> new)
  const out = new Map<string, Map<string, Map<string, string>>>();
  for (const c of recipe.fk_cascades) {
    const parentEntries = remap.get(c.parent);
    if (!parentEntries || parentEntries.length === 0) continue;
    if (!out.has(c.child)) out.set(c.child, new Map());
    out.get(c.child)!.set(c.column, mapFromEntries(parentEntries)!);
  }
  return out;
}

function mapFromEntries(entries: { oldId: string; newId: string }[] | undefined): Map<string, string> | undefined {
  if (!entries || entries.length === 0) return undefined;
  return new Map(entries.map(e => [e.oldId, e.newId]));
}

async function emitTableInserts(
  pool: Pool,
  table: string,
  where: string,
  cols: string[],
  idRemap: Map<string, string> | undefined,
  fkRemap: Map<string, Map<string, string>> | undefined
): Promise<string[]> {
  // Cast every column to text on the SOURCE side so we get a uniform string
  // representation (handles arrays, jsonb, timestamps, bytea -- everything).
  // We re-quote on the JS side using PG's standard text format.
  const selectList = cols.map(c => `${quoteIdent(c)}::text AS ${quoteIdent(c)}`).join(", ");
  const sql = `SELECT ${selectList} FROM "${table}" WHERE ${where}`;
  const { rows } = await pool.query(sql);

  const colList = cols.map(quoteIdent).join(",");
  const inserts: string[] = [];

  for (const row of rows) {
    const values: string[] = [];
    for (const col of cols) {
      let v: unknown = row[col];
      if (col === "id" && idRemap && v !== null && v !== undefined) {
        const newId = idRemap.get(String(v));
        if (newId) v = newId;
      }
      const fk = fkRemap?.get(col);
      if (fk && v !== null && v !== undefined) {
        const newV = fk.get(String(v));
        if (newV) v = newV;
      }
      values.push(quoteLiteral(v));
    }
    inserts.push(
      `INSERT INTO ${quoteIdent(table)} (${colList}) VALUES (${values.join(",")}) ON CONFLICT DO NOTHING;`
    );
  }
  return inserts;
}

function buildHeader(recipe: Recipe, remap: RemapByTable): string {
  const lines: string[] = [
    "-- ============================================================================",
    `-- ${recipe.name}: tenant data restore (company_id = ${recipe.company_id})`,
    `-- Generated: ${new Date().toISOString()}`,
    `-- Source: ${recipe.source}    Target: ${recipe.target}`,
    `-- Remap offset: ${recipe.remap_offset}`,
    "--",
    "-- Wrapped in BEGIN..COMMIT.  Every INSERT uses ON CONFLICT DO NOTHING.",
    "-- No DELETE / UPDATE / TRUNCATE anywhere in this file.",
    "-- ============================================================================",
    ""
  ];
  if (remap.size > 0) {
    lines.push("-- ID remappings applied (deterministic: source_id + remap_offset):");
    for (const [t, entries] of [...remap.entries()].sort()) {
      for (const e of entries) lines.push(`--   ${t}.id  ${e.oldId}  ->  ${e.newId}`);
    }
    lines.push("");
  }
  lines.push("BEGIN;");
  lines.push("SET CONSTRAINTS ALL DEFERRED;");
  if (recipe.pre_restore_sql?.trim()) {
    lines.push("\n-- pre_restore_sql --");
    lines.push(recipe.pre_restore_sql);
  }
  return lines.join("\n");
}

function buildFooter(recipe: Recipe): string {
  const lines: string[] = [];
  if (recipe.post_restore_sql?.trim()) {
    lines.push("\n-- post_restore_sql --");
    lines.push(recipe.post_restore_sql);
  }
  lines.push("\nCOMMIT;");
  return lines.join("\n");
}
