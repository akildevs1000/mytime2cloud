// Schema introspection. We read information_schema to learn which tables exist
// in source and target, what columns each has, and what the types are.
// This is what makes the column-intersection trick work.

import { Pool } from "pg";

export interface ColumnInfo {
  name: string;
  dataType: string;     // e.g. "integer", "character varying", "jsonb"
  ordinalPosition: number;
}

export type SchemaMap = Map<string, ColumnInfo[]>;  // tableName -> ordered cols

export async function loadSchema(pool: Pool): Promise<SchemaMap> {
  const { rows } = await pool.query<{
    table_name: string;
    column_name: string;
    data_type: string;
    ordinal_position: number;
  }>(`
    SELECT table_name, column_name, data_type, ordinal_position
    FROM information_schema.columns
    WHERE table_schema = 'public'
    ORDER BY table_name, ordinal_position
  `);

  const map: SchemaMap = new Map();
  for (const r of rows) {
    if (!map.has(r.table_name)) map.set(r.table_name, []);
    map.get(r.table_name)!.push({
      name:            r.column_name,
      dataType:        r.data_type,
      ordinalPosition: r.ordinal_position
    });
  }
  return map;
}

// All tables that have a column literally named `company_id`.
export function tablesWithCompanyId(schema: SchemaMap): string[] {
  const out: string[] = [];
  for (const [t, cols] of schema) {
    if (cols.some(c => c.name === "company_id")) out.push(t);
  }
  return out.sort();
}

// Intersect source columns with target columns (preserving source order).
export function columnIntersection(
  source: SchemaMap,
  target: SchemaMap,
  table: string
): { shared: string[]; droppedFromSource: string[] } {
  const src = source.get(table);
  const tgt = target.get(table);
  if (!src || !tgt) return { shared: [], droppedFromSource: [] };
  const tgtNames = new Set(tgt.map(c => c.name));
  const shared:  string[] = [];
  const dropped: string[] = [];
  for (const c of src) {
    if (tgtNames.has(c.name)) shared.push(c.name);
    else dropped.push(c.name);
  }
  return { shared, droppedFromSource: dropped };
}
