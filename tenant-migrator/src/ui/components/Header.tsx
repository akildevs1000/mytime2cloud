export function Header() {
  return (
    <header className="border-b border-border px-4 py-2 flex items-center gap-3">
      <div className="text-accent font-bold tracking-tight">Tenant Migrator</div>
      <div className="text-xs text-muted">multi-tenant Postgres migration / sync</div>
      <div className="ml-auto text-xs text-muted">v0.1</div>
    </header>
  );
}
