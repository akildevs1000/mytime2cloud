// Recipe loader + schema validator.
// A recipe describes ONE tenant migration / sync. The engine reads it and
// follows it deterministically.

import { readFileSync } from "node:fs";
import { z } from "zod";
import YAML from "yaml";

// ---------------------------------------------------------------------------
// Schema
// ---------------------------------------------------------------------------
const TableOverride = z.object({
  where:       z.string().optional(),       // override the auto-generated WHERE clause
  exclude:     z.boolean().optional(),      // skip the table entirely
  skip_remap:  z.boolean().optional()       // never remap colliding ids on this table
});

const LinkedTable = z.object({
  table: z.string(),
  where: z.string()
});

const FkCascade = z.object({
  child:  z.string(),
  column: z.string(),
  parent: z.string()
});

const TenantKeyOverride = z.object({
  // For tables where the row's own id IS the tenant key (e.g. `companies`).
  where:           z.string(),  // e.g. "id = {{company_id}}"
  foreign_filter:  z.string()   // e.g. "id <> {{company_id}}"
});

export const RecipeSchema = z.object({
  name:         z.string(),
  description:  z.string().optional(),
  company_id:   z.union([z.string(), z.number()]).transform(v => String(v)),
  remap_offset: z.number().int().positive().default(1_000_000_000),
  source:       z.string(),                 // connection name
  target:       z.string(),                 // connection name

  table_overrides:      z.record(TableOverride).default({}),
  linked_tables:        z.array(LinkedTable).default([]),
  fk_cascades:          z.array(FkCascade).default([]),
  tenant_key_overrides: z.record(TenantKeyOverride).default({}),

  pre_restore_sql:  z.string().optional(),
  post_restore_sql: z.string().optional()
});

export type Recipe = z.infer<typeof RecipeSchema>;

// ---------------------------------------------------------------------------
// Loader
// ---------------------------------------------------------------------------
export function loadRecipe(path: string): Recipe {
  const raw = readFileSync(path, "utf8");
  const parsed = YAML.parse(raw);
  const result = RecipeSchema.safeParse(parsed);
  if (!result.success) {
    const issues = result.error.issues
      .map(i => `  ${i.path.join(".")}: ${i.message}`)
      .join("\n");
    throw new Error(`Invalid recipe at ${path}:\n${issues}`);
  }
  return result.data;
}

// Substitute {{company_id}} placeholders in a SQL/text string.
export function expandPlaceholders(text: string, recipe: Recipe): string {
  return text.replaceAll("{{company_id}}", recipe.company_id);
}
