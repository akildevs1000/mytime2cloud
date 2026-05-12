// Type declarations for the renderer. Keeps the renderer code TS-friendly
// without importing the engine directly (renderer is sandboxed).

export interface ConnectionRow {
  name: string;
  host: string;
  port: number;
  user: string;
  database: string;
  readonly?: boolean;       // hidden from Target dropdown when true
}

export interface AuditTenant { id: number | string; name: string; }
export interface AuditOrphan { company_id: string; rows: number; sample_table: string; }
export interface AuditResult {
  tenants: AuditTenant[];
  orphans: AuditOrphan[];
  schemaDiff: { onlyInSource: string[]; onlyInTarget: string[] };
}

export interface VerifyRow {
  table: string;
  source: number;
  target: number;
  diff: number;
  status: "match" | "missing-on-target" | "extra-on-target";
}
export interface VerifyResult {
  rows: VerifyRow[];
  matched: number;
  missing: number;
  extra: number;
}

export interface RecipeMeta {
  name: string;
  description?: string;
  company_id: string;
  remap_offset: number;
  source: string;
  target: string;
}

export interface ProgressEvent {
  kind: string;          // "info" | "begin" | "commit" | "rollback" | "error" | ...
  message?: string;
  ordinal?: number;
  total?: number;
}

export interface RestoreResult {
  inserted: number;
  skipped: number;
  errors: number;
  durationMs: number;
}

export interface Overrides {
  source?:    string;
  target?:    string;
  companyId?: string;
}

export interface CompanyRow {
  id:   string;
  name: string;
}

export interface TenantStatus {
  state:         "fresh" | "partial-orphan" | "exists";
  companyExists: boolean;
  companyName:   string | null;
  rowCounts:     { table: string; count: number }[];
  totalRelated:  number;
}

declare global {
  interface Window {
    api: {
      listRecipes:     () => Promise<string[] | { error: string }>;
      loadRecipe:      (file: string) => Promise<{ recipe: RecipeMeta; path: string } | { error: string }>;
      listConnections: () => Promise<ConnectionRow[] | { error: string }>;
      listCompanies:   (sourceConnection: string) => Promise<CompanyRow[] | { error: string }>;
      tenantStatus:    (connection: string, companyId: string) => Promise<TenantStatus | { error: string }>;
      audit:    (file: string, overrides?: Overrides) => Promise<AuditResult | { error: string }>;
      verify:   (file: string, overrides?: Overrides) => Promise<VerifyResult | { error: string }>;
      dump:     (file: string, overrides?: Overrides) => Promise<{ tableStats: any[]; remap: any } | { error: string }>;
      restore:  (file: string, dryRun?: boolean, overrides?: Overrides) => Promise<RestoreResult | { error: string }>;
      sync:     (file: string, overrides?: Overrides) => Promise<{ dumpStats?: any[]; restore?: RestoreResult; verify?: VerifyResult; error?: string }>;
      onProgress: (cb: (event: ProgressEvent) => void) => () => void;
    };
  }
}
