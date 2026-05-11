import { useEffect, useState } from "react";
import type { TenantStatus } from "../types";

interface Props {
  target:    string;
  companyId: string;
}

// Shows the state of the chosen tenant on the target DB:
//   FRESH          companies row absent + zero related rows. First-time migration.
//   PARTIAL-ORPHAN companies row absent BUT related rows exist (likely junk leftovers).
//                  The runbook calls this an "orphan tenant"; usually needs cleanup
//                  before a real migration.
//   EXISTS         companies row present. Incremental sync territory.
export function TargetStatus({ target, companyId }: Props) {
  const [status, setStatus]   = useState<TenantStatus | null>(null);
  const [error, setError]     = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!target || !companyId) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    setStatus(null);
    window.api.tenantStatus(target, companyId).then(res => {
      if (cancelled) return;
      setLoading(false);
      if (res && "error" in res) setError(res.error);
      else setStatus(res as TenantStatus);
    });
    return () => { cancelled = true; };
  }, [target, companyId]);

  if (loading) {
    return <div className="panel px-3 py-2 text-xs text-muted">checking target for company {companyId}…</div>;
  }
  if (error) {
    return <div className="panel px-3 py-2 text-xs text-danger mono">target check failed: {error}</div>;
  }
  if (!status) return null;

  const { state, companyName, rowCounts, totalRelated } = status;

  const banner = state === "fresh"          ? <FreshBanner companyId={companyId} />
              : state === "partial-orphan"  ? <OrphanBanner companyId={companyId} totalRelated={totalRelated} />
              :                                <ExistsBanner companyId={companyId} companyName={companyName ?? ""} totalRelated={totalRelated} />;

  return (
    <div className="space-y-2">
      {banner}
      {rowCounts.some(r => r.count > 0) && (
        <div className="panel px-3 py-2">
          <div className="text-[10px] uppercase tracking-wide text-muted mb-1">target row counts (signal tables)</div>
          <div className="grid grid-cols-4 gap-x-4 gap-y-1 mono text-[11px]">
            {rowCounts.filter(r => r.count > 0).map(r => (
              <div key={r.table}>
                <span className="text-muted">{r.table}:</span>{" "}
                <span className={r.count > 0 ? "text-text" : "text-muted"}>{r.count}</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

function FreshBanner({ companyId }: { companyId: string }) {
  return (
    <div className="panel px-3 py-2 ring-1 ring-success/40">
      <div className="flex items-center gap-2 text-sm">
        <span className="tag bg-success/15 text-success">FRESH</span>
        <span className="text-text">No data for company <span className="mono">{companyId}</span> on the target — this will be a brand-new migration.</span>
      </div>
      <div className="text-[11px] text-muted mt-1">
        Run <span className="mono">Sync</span> to insert the source's data into a clean slate.
      </div>
    </div>
  );
}

function ExistsBanner({ companyId, companyName, totalRelated }: { companyId: string; companyName: string; totalRelated: number }) {
  return (
    <div className="panel px-3 py-2 ring-1 ring-accent/40">
      <div className="flex items-center gap-2 text-sm">
        <span className="tag bg-accent/15 text-accent">EXISTS</span>
        <span className="text-text">
          Target already has <span className="mono">{companyName}</span> (id <span className="mono">{companyId}</span>) — {totalRelated.toLocaleString()} related rows.
        </span>
      </div>
      <div className="text-[11px] text-muted mt-1">
        Run <span className="mono">Verify</span> to see drift, or <span className="mono">Sync</span> to insert any new source rows. Existing target rows are never overwritten.
      </div>
    </div>
  );
}

function OrphanBanner({ companyId, totalRelated }: { companyId: string; totalRelated: number }) {
  return (
    <div className="panel px-3 py-2 ring-1 ring-warn/60">
      <div className="flex items-center gap-2 text-sm">
        <span className="tag bg-warn/15 text-warn">ORPHAN</span>
        <span className="text-text">
          No <span className="mono">companies</span> row at id <span className="mono">{companyId}</span>, but {totalRelated.toLocaleString()} other rows still tagged with this id.
        </span>
      </div>
      <div className="text-[11px] text-muted mt-1">
        Likely leftover from a partial setup. The runbook recommends cleanup before migrating.
        Don't proceed with Sync until you've decided what to do with the orphans.
      </div>
    </div>
  );
}
