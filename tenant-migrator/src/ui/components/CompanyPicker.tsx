import { useEffect, useState } from "react";
import type { CompanyRow } from "../types";

interface Props {
  connection:    string;          // which DB to query (target)
  selectedId:    string;
  recipeDefault: string;
  onChange:      (id: string) => void;
}

// Simple company dropdown. Loads from the chosen connection whenever it changes.
// Three states: loading | loaded (real list) | error.
export function CompanyPicker({ connection, selectedId, recipeDefault, onChange }: Props) {
  const [companies, setCompanies] = useState<CompanyRow[]>([]);
  const [error, setError]         = useState<string | null>(null);
  const [loading, setLoading]     = useState(false);

  useEffect(() => {
    if (!connection) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    setCompanies([]);
    window.api.listCompanies(connection).then(res => {
      if (cancelled) return;
      setLoading(false);
      if (Array.isArray(res)) {
        setCompanies(res);
        // If selected id isn't in the new list, snap to the first available
        // option so the dropdown always shows something real.
        if (res.length > 0 && !res.some(c => c.id === selectedId)) {
          onChange(res[0].id);
        }
      } else if (res && "error" in res) {
        setError(res.error);
      }
    });
    return () => { cancelled = true; };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [connection]);

  const overridden = companies.length > 0 && selectedId !== recipeDefault;

  return (
    <div className={`panel px-2 py-1 flex items-center gap-2 ${overridden ? "ring-1 ring-warn/60" : "ring-1 ring-border/60"}`}>
      <span className="text-[10px] uppercase tracking-wide text-muted">COMPANY</span>
      <select
        value={selectedId}
        onChange={e => onChange(e.target.value)}
        disabled={loading || !!error || companies.length === 0}
        className="bg-bg border border-border rounded text-xs mono px-1.5 py-0.5 focus:outline-none focus:border-accent min-w-[260px]"
      >
        {loading && <option>Loading…</option>}
        {!loading && error && <option>error</option>}
        {!loading && !error && companies.length === 0 && <option>No companies on source</option>}
        {!loading && !error && companies.map(c => (
          <option key={c.id} value={c.id}>{c.id} — {c.name}</option>
        ))}
      </select>
      {error && <span className="text-[10px] text-danger truncate" title={error}>{error}</span>}
    </div>
  );
}
