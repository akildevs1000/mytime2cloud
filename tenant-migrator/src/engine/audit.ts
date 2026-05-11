// Audit: read-only inspection of a target DB. Lists tenants, finds orphan
// rows (company_id values that don't have a corresponding companies row),
// reports schema diffs vs source.

import { Pool } from "pg";
import { SchemaMap } from "./schema.js";

export interface TenantRow { id: number | string; name: string }
export interface OrphanRow { company_id: string; rows: number; sample_table: string }
export interface SchemaDiff { onlyInSource: string[]; onlyInTarget: string[] }

export interface AuditResult {
  tenants:    TenantRow[];
  orphans:    OrphanRow[];
  schemaDiff: SchemaDiff;
}

export async function runAudit(
  targetPool:   Pool,
  sourceSchema: SchemaMap,
  targetSchema: SchemaMap
): Promise<AuditResult> {
  // Real tenants on target.
  const tenantRes = await targetPool.query<TenantRow>(
    "SELECT id, name FROM companies ORDER BY id"
  );

  // For each table with company_id, find any company_id values not in companies.
  // Aggregate per-company_id.
  const tablesWithCo: string[] = [];
  for (const [t, cols] of targetSchema) {
    if (cols.some(c => c.name === "company_id")) tablesWithCo.push(t);
  }

  const orphanCounts = new Map<string, { rows: number; sampleTable: string }>();
  for (const t of tablesWithCo) {
    try {
      const r = await targetPool.query<{ cid: string; n: string }>(
        `SELECT al.company_id::text AS cid, COUNT(*)::text AS n
           FROM "${t}" al
          WHERE al.company_id IS NOT NULL
            AND NOT EXISTS (SELECT 1 FROM companies c WHERE c.id::text = al.company_id::text)
          GROUP BY al.company_id::text`
      );
      for (const row of r.rows) {
        const k = row.cid;
        const n = Number(row.n);
        const cur = orphanCounts.get(k);
        if (!cur || cur.rows < n) {
          orphanCounts.set(k, { rows: (cur?.rows ?? 0) + n, sampleTable: t });
        } else {
          cur.rows += n;
        }
      }
    } catch {
      // Table type might not let us cast — skip silently.
    }
  }
  const orphans: OrphanRow[] = [...orphanCounts.entries()]
    .map(([company_id, v]) => ({ company_id, rows: v.rows, sample_table: v.sampleTable }))
    .sort((a, b) => Number(a.company_id) - Number(b.company_id));

  // Schema diff: which tables exist on one side only.
  const onlyInSource: string[] = [];
  const onlyInTarget: string[] = [];
  for (const t of sourceSchema.keys()) if (!targetSchema.has(t)) onlyInSource.push(t);
  for (const t of targetSchema.keys()) if (!sourceSchema.has(t)) onlyInTarget.push(t);

  return {
    tenants: tenantRes.rows,
    orphans,
    schemaDiff: { onlyInSource: onlyInSource.sort(), onlyInTarget: onlyInTarget.sort() }
  };
}
