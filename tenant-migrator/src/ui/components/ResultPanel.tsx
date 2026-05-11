import type { AuditResult, VerifyResult, RestoreResult } from "../types";

interface Props { result: any; }

export function ResultPanel({ result }: Props) {
  return (
    <div className="flex flex-col min-h-0">
      <div className="flex items-center px-3 py-1.5 border-b border-border">
        <h3 className="text-xs uppercase text-muted tracking-wide">Result</h3>
        {result?.kind && (
          <span className="ml-2 tag bg-panel border border-border text-muted">{result.kind}</span>
        )}
      </div>
      <div className="flex-1 overflow-auto p-3 text-sm">
        {!result ? (
          <div className="text-muted text-xs">Run an action to see the result here.</div>
        ) : result.value && "error" in result.value ? (
          <ErrorBox message={result.value.error} />
        ) : (
          <DispatchedView kind={result.kind} value={result.value} />
        )}
      </div>
    </div>
  );
}

function DispatchedView({ kind, value }: { kind: string; value: any }) {
  if (kind === "audit")   return <AuditView v={value} />;
  if (kind === "verify")  return <VerifyView v={value} />;
  if (kind === "dump")    return <DumpView v={value} />;
  if (kind === "restore" || kind === "dryRun") return <RestoreView v={value} dryRun={kind === "dryRun"} />;
  if (kind === "sync")    return <SyncView v={value} />;
  return <pre className="mono text-xs">{JSON.stringify(value, null, 2)}</pre>;
}

function ErrorBox({ message }: { message: string }) {
  return <div className="panel p-3 text-danger text-xs mono whitespace-pre-wrap">{message}</div>;
}

function AuditView({ v }: { v: AuditResult }) {
  return (
    <div className="space-y-3">
      <Section title="Real tenants">
        {v.tenants.length === 0 ? <Empty/> : (
          <table className="text-xs mono">
            <thead className="text-muted"><tr><th className="text-left pr-4">id</th><th className="text-left">name</th></tr></thead>
            <tbody>{v.tenants.map(t => <tr key={t.id}><td className="pr-4">{t.id}</td><td>{t.name}</td></tr>)}</tbody>
          </table>
        )}
      </Section>
      <Section title={`Orphan tenants (${v.orphans.length})`}>
        {v.orphans.length === 0 ? <Empty/> : (
          <div className="max-h-64 overflow-auto">
            <table className="text-xs mono">
              <thead className="text-muted sticky top-0 bg-bg"><tr>
                <th className="text-left pr-4">company_id</th><th className="text-left pr-4">rows</th><th className="text-left">sample</th>
              </tr></thead>
              <tbody>{v.orphans.map(o => (
                <tr key={o.company_id}>
                  <td className="pr-4">{o.company_id}</td>
                  <td className="pr-4">{o.rows}</td>
                  <td>{o.sample_table}</td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        )}
      </Section>
      <Section title="Schema diff">
        {v.schemaDiff.onlyInSource.length === 0 && v.schemaDiff.onlyInTarget.length === 0
          ? <div className="text-success text-xs">identical</div>
          : (
            <div className="text-xs space-y-1">
              {v.schemaDiff.onlyInSource.length > 0 &&
                <div><span className="text-muted">Only in source:</span>{" "}<span className="mono">{v.schemaDiff.onlyInSource.join(", ")}</span></div>}
              {v.schemaDiff.onlyInTarget.length > 0 &&
                <div><span className="text-muted">Only in target:</span>{" "}<span className="mono">{v.schemaDiff.onlyInTarget.join(", ")}</span></div>}
            </div>
          )}
      </Section>
    </div>
  );
}

function VerifyView({ v }: { v: VerifyResult }) {
  const mismatches = v.rows.filter(r => r.status !== "match");
  return (
    <div className="space-y-3">
      <div className="flex gap-3 text-xs">
        <Stat label="matched"  value={v.matched}  ok />
        <Stat label="missing"  value={v.missing}  warn={v.missing > 0} />
        <Stat label="extra"    value={v.extra}    warn={v.extra > 0} />
      </div>
      <Section title={mismatches.length === 0 ? "All match" : `Mismatches (${mismatches.length})`}>
        {mismatches.length === 0 ? <div className="text-success text-xs">target is in sync with source</div> : (
          <div className="max-h-96 overflow-auto">
            <table className="text-xs mono w-full">
              <thead className="text-muted sticky top-0 bg-bg">
                <tr><th className="text-left pr-4">table</th><th className="text-right pr-4">src</th><th className="text-right pr-4">tgt</th><th className="text-right pr-4">diff</th><th className="text-left">status</th></tr>
              </thead>
              <tbody>
                {mismatches.map(r => (
                  <tr key={r.table}>
                    <td className="pr-4">{r.table}</td>
                    <td className="pr-4 text-right">{r.source}</td>
                    <td className="pr-4 text-right">{r.target}</td>
                    <td className={`pr-4 text-right ${r.diff > 0 ? "text-warn" : "text-accent"}`}>{r.diff}</td>
                    <td>{r.status}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </Section>
    </div>
  );
}

function DumpView({ v }: { v: { tableStats: any[]; remap: any } }) {
  const stats = v.tableStats ?? [];
  const totalRows = stats.reduce((a, b) => a + (b.rows ?? 0), 0);
  const totalRemap = stats.reduce((a, b) => a + (b.remapped ?? 0), 0);
  return (
    <div className="space-y-3">
      <div className="flex gap-3 text-xs">
        <Stat label="tables" value={stats.filter((s: any) => s.status === "ok").length} />
        <Stat label="rows"   value={totalRows} />
        <Stat label="remapped IDs" value={totalRemap} />
      </div>
      <Section title="Per-table">
        <div className="max-h-96 overflow-auto">
          <table className="text-xs mono w-full">
            <thead className="text-muted sticky top-0 bg-bg">
              <tr><th className="text-left pr-4">table</th><th className="text-right pr-4">rows</th><th className="text-right pr-4">remapped</th><th className="text-left">status</th></tr>
            </thead>
            <tbody>
              {stats.map((s: any) => (
                <tr key={s.table} className={s.status !== "ok" ? "text-muted" : ""}>
                  <td className="pr-4">{s.table}</td>
                  <td className="pr-4 text-right">{s.rows}</td>
                  <td className="pr-4 text-right">{s.remapped || ""}</td>
                  <td>{s.status}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Section>
    </div>
  );
}

function RestoreView({ v, dryRun }: { v: RestoreResult; dryRun?: boolean }) {
  return (
    <div className="space-y-3">
      {dryRun && <div className="text-warn text-xs">DRY RUN — nothing was written to the target.</div>}
      <div className="flex gap-3 text-xs">
        <Stat label="inserted" value={v.inserted} ok={v.inserted > 0} />
        <Stat label="skipped"  value={v.skipped} />
        <Stat label="errors"   value={v.errors} warn={v.errors > 0} />
        <Stat label="duration" value={`${v.durationMs} ms`} />
      </div>
    </div>
  );
}

function SyncView({ v }: { v: any }) {
  return (
    <div className="space-y-3">
      {v.restore && (
        <Section title="Restore">
          <RestoreView v={v.restore} />
        </Section>
      )}
      {v.verify && (
        <Section title="Verify">
          <VerifyView v={v.verify} />
        </Section>
      )}
    </div>
  );
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="panel p-3">
      <div className="text-xs uppercase tracking-wide text-muted mb-2">{title}</div>
      {children}
    </div>
  );
}

function Empty() { return <div className="text-muted text-xs">none</div>; }

function Stat({ label, value, ok, warn }: { label: string; value: any; ok?: boolean; warn?: boolean }) {
  const cls = warn ? "text-warn" : ok ? "text-success" : "text-text";
  return (
    <div className="panel px-3 py-2 min-w-[100px]">
      <div className="text-[10px] uppercase tracking-wide text-muted">{label}</div>
      <div className={`mono ${cls}`}>{value}</div>
    </div>
  );
}
