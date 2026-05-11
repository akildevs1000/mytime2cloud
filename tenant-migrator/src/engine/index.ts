// Public engine API. The CLI and the Electron main process both go through here.

export { ConnectionManager, type ConnectionEntry } from "./connection.js";
export { loadRecipe, type Recipe }                  from "./recipe.js";
export { loadSchema, tablesWithCompanyId, type SchemaMap, type ColumnInfo } from "./schema.js";
export { detectCollisions, type RemapByTable, type RemapEntry } from "./collisions.js";
export { buildDump, type DumpResult, type TableStats }          from "./dump.js";
export { runRestore, splitStatements, type RestoreEvent, type RestoreResult } from "./restore.js";
export { runVerify, type VerifyResult, type VerifyRow }         from "./verify.js";
export { runAudit, type AuditResult, type TenantRow, type OrphanRow, type SchemaDiff } from "./audit.js";
