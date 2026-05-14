// Collision detection + deterministic remap.
//
// A "real" collision is when a source row's id is taken on the target by a
// row that does NOT belong to the same tenant. Rows on target already owned
// by THIS tenant came from a prior partial restore; ON CONFLICT DO NOTHING
// handles them and the dump stays idempotent.
//
// Remapping is deterministic: new_id = source_id + recipe.remap_offset.
// Same source row -> same new id, every run.

import { Pool } from "pg";
import { Recipe, expandPlaceholders } from "./recipe.js";
import { SchemaMap } from "./schema.js";

export interface RemapEntry {
  oldId: string;
  newId: string;
}

export type RemapByTable = Map<string, RemapEntry[]>;

interface DetectArgs {
  recipe: Recipe;
  sourcePool: Pool;
  targetPool: Pool;
  sourceSchema: SchemaMap;
  targetSchema: SchemaMap;
  // For each table, the WHERE clause used to filter source rows.
  tableFilters: Map<string, string>;
}

export async function detectCollisions(args: DetectArgs): Promise<RemapByTable> {
  const remap: RemapByTable = new Map();

  for (const [table, srcWhere] of args.tableFilters) {
    if (!args.sourceSchema.has(table) || !args.targetSchema.has(table)) continue;

    const cols = args.sourceSchema.get(table)!;
    if (!cols.some(c => c.name === "id")) continue;

    const override = args.recipe.table_overrides[table];
    if (override?.skip_remap) continue;
    if (override?.exclude)    continue;   // excluded tables aren't dumped → no need to remap them

    // Source ids that this dump will emit.
    const srcRes = await args.sourcePool.query<{ id: string }>(
      `SELECT id::text AS id FROM "${table}" WHERE ${srcWhere} ORDER BY id`
    );
    const srcIds = srcRes.rows.map(r => r.id);
    if (srcIds.length === 0) continue;

    // What counts as "foreign" on the target? For most tables: rows whose
    // company_id is not the source tenant. For the companies table: rows
    // whose own id is not the source tenant. Otherwise: any overlap counts.
    const foreignFilter = buildForeignFilter(table, args.recipe, args.targetSchema);

    // Target ids owned by another tenant.
    const tgtRes = await args.targetPool.query<{ id: string }>(
      `SELECT id::text AS id FROM "${table}" WHERE ${foreignFilter}`
    );
    const tgtSet = new Set(tgtRes.rows.map(r => r.id));

    // Collisions: source ids already owned by another tenant.
    const collisions = srcIds.filter(id => tgtSet.has(id));
    if (collisions.length === 0) continue;

    const offset = BigInt(args.recipe.remap_offset);
    const entries: RemapEntry[] = collisions.map(oldId => ({
      oldId,
      newId: (BigInt(oldId) + offset).toString()
    }));

    // Sanity check: do any of the remapped ids already collide with another
    // tenant on the target? If so, the offset isn't big enough.
    const newIds = entries.map(e => `'${e.newId}'`).join(",");
    const conflictCheck = await args.targetPool.query<{ count: string }>(
      `SELECT COUNT(*)::text AS count FROM "${table}"
        WHERE id::text IN (${newIds}) AND (${foreignFilter})`
    );
    if (Number(conflictCheck.rows[0].count) > 0) {
      throw new Error(
        `Remapped ids for table "${table}" still collide with another tenant. ` +
        `Bump remap_offset (currently ${args.recipe.remap_offset}) in the recipe.`
      );
    }

    remap.set(table, entries);
  }

  return remap;
}

function buildForeignFilter(table: string, recipe: Recipe, targetSchema: SchemaMap): string {
  // Tenant-key override (e.g. companies.id is the tenant key, not company_id).
  const override = recipe.tenant_key_overrides[table];
  if (override) {
    return expandPlaceholders(override.foreign_filter, recipe);
  }

  // Tables that have a literal company_id column.
  const cols = targetSchema.get(table);
  const hasCompanyId = !!cols?.some(c => c.name === "company_id");
  if (hasCompanyId) {
    return `company_id::text <> '${recipe.company_id}' OR company_id IS NULL`;
  }

  // No way to identify ownership — treat any overlap as foreign (conservative).
  return "TRUE";
}
