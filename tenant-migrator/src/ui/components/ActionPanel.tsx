import { useState } from "react";

type Action = "audit" | "verify" | "dump" | "restore" | "sync" | "dryRun";

interface Props {
  busy: boolean;
  disabled: boolean;
  onRun: (a: Action) => void;
}

export function ActionPanel({ busy, disabled, onRun }: Props) {
  const [confirmAction, setConfirmAction] = useState<Action | null>(null);

  const block = (a: Action) => busy || disabled;

  function attempt(a: Action) {
    if (a === "restore" || a === "sync") {
      setConfirmAction(a);
    } else {
      onRun(a);
    }
  }

  return (
    <>
      <div className="flex flex-wrap gap-2 items-center">
        <button className="btn-default" disabled={block("audit")}    onClick={() => attempt("audit")}>Audit (read-only)</button>
        <button className="btn-default" disabled={block("verify")}   onClick={() => attempt("verify")}>Verify (read-only)</button>
        <button className="btn-default" disabled={block("dump")}     onClick={() => attempt("dump")}>Dump preview</button>
        <button className="btn-default" disabled={block("dryRun")}   onClick={() => attempt("dryRun")}>Dry-run</button>
        <span className="ml-2 text-xs text-muted">— writes —</span>
        <button className="btn-primary" disabled={block("restore")}  onClick={() => attempt("restore")}>Restore</button>
        <button className="btn-success" disabled={block("sync")}     onClick={() => attempt("sync")}>Sync</button>
        {busy && <span className="ml-2 text-xs text-warn">running…</span>}
      </div>

      {confirmAction && (
        <div className="mt-3 panel p-3 flex items-center gap-3">
          <div className="text-sm">
            <span className="text-warn font-bold">⚠ Confirm:</span>{" "}
            About to run <span className="mono">{confirmAction}</span> against the target.
            This will <span className="text-warn">write to the database</span>. Backup taken? Run inside a transaction (rolls back on error).
          </div>
          <button className="btn-danger ml-auto" onClick={() => { onRun(confirmAction); setConfirmAction(null); }}>
            Yes, run {confirmAction}
          </button>
          <button className="btn-default" onClick={() => setConfirmAction(null)}>Cancel</button>
        </div>
      )}
    </>
  );
}
