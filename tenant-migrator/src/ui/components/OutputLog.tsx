import { useEffect, useRef } from "react";
import type { ProgressEvent } from "../types";

interface Props { logs: ProgressEvent[]; onClear: () => void; }

export function OutputLog({ logs, onClear }: Props) {
  const scrollRef = useRef<HTMLDivElement>(null);
  useEffect(() => {
    if (scrollRef.current) scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
  }, [logs]);

  return (
    <div className="border-l border-border flex flex-col min-h-0">
      <div className="flex items-center px-3 py-1.5 border-b border-border">
        <h3 className="text-xs uppercase text-muted tracking-wide">Output</h3>
        <button className="ml-auto text-xs text-muted hover:text-text" onClick={onClear}>clear</button>
      </div>
      <div ref={scrollRef} className="flex-1 overflow-auto px-3 py-2 mono text-xs space-y-0.5">
        {logs.length === 0 ? (
          <div className="text-muted">No output yet.</div>
        ) : logs.map((e, i) => {
          const cls = e.kind === "error"    ? "text-danger"
                    : e.kind === "rollback" ? "text-warn"
                    : e.kind === "commit"   ? "text-success"
                    : e.kind === "info"     ? "text-text"
                    :                         "text-muted";
          return <div key={i} className={cls}>[{e.kind}] {e.message ?? ""}</div>;
        })}
      </div>
    </div>
  );
}
