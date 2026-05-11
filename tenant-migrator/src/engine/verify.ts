// Verification: count source vs target rows for each in-scope table.
// Read-only.

import { Pool } from "pg";
import { Recipe, expandPlaceholders } from "./recipe.js";
import { SchemaMap, tablesWithCompanyId } from "./schema.js";

export interface VerifyRow {
  table:  string;
  source: number;
  target: number;
  diff:   number;     // source - target. Negative = target has extra rows.
  status: "match" | "missing-on-target" | "extra-on-target";
}

export interface VerifyResult {
  rows:        VerifyRow[];
  matched:     number;
  missing:     number;       // tables where target has fewer
  extra:       number;       // tables where target has more
}

export async function runVerify(
  recipe: Recipe,
  sourcePool: Pool,
  targetPool: Pool,
  sourceSchema: SchemaMap,
  targetSchema: SchemaMap
): Promise<VerifyResult> {
  // Same table-filter logic as the dump generator.
  const filters = computeFilters(recipe, sourceSchema);
  const rows: VerifyRow[] = [];
  let matched = 0, missing = 0, extra = 0;

  for (const [table, where] of filters) {
    if (!sourceSchema.has(table) || !targetSchema.has(table)) continue;

    const [s, t] = await Promise.all([
      sourcePool.query<{ c: string }>(`SELECT COUNT(*)::text AS c FROM "${table}" WHERE ${where}`),
      targetPool.query<{ c: string }>(`SELECT COUNT(*)::text AS c FROM "${table}" WHERE ${where}`)
    ]);
    const src = Number(s.rows[0].c);
    const tgt = Number(t.rows[0].c);
    const diff = src - tgt;
    let status: VerifyRow["status"];
    if (diff === 0)      { status = "match";              matched++; }
    else if (diff > 0)   { status = "missing-on-target";  missing++; }
    else                 { status = "extra-on-target";    extra++;   }

    rows.push({ table, source: src, target: tgt, diff, status });
  }

  return { rows, matched, missing, extra };
}

function computeFilters(recipe: Recipe, sourceSchema: SchemaMap): Map<string, string> {
  const out = new Map<string, string>();
  for (const [t, ovr] of Object.entries(recipe.tenant_key_overrides)) {
    if (sourceSchema.has(t)) out.set(t, expandPlaceholders(ovr.where, recipe));
  }
  for (const t of tablesWithCompanyId(sourceSchema)) {
    if (out.has(t)) continue;
    const ovr = recipe.table_overrides[t];
    out.set(t, ovr?.where
      ? expandPlaceholders(ovr.where, recipe)
      : `company_id = ${recipe.company_id}`);
  }
  for (const lt of recipe.linked_tables) {
    if (out.has(lt.table)) continue;
    out.set(lt.table, expandPlaceholders(lt.where, recipe));
  }
  return out;
}
