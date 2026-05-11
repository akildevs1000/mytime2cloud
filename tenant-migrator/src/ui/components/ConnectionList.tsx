import type { ConnectionRow } from "../types";

interface Props { connections: ConnectionRow[]; }

export function ConnectionList({ connections }: Props) {
  return (
    <div>
      <h3 className="text-xs uppercase text-muted tracking-wide mb-1">Connections</h3>
      {connections.length === 0 ? (
        <div className="text-xs text-muted">config/connections.yaml not loaded</div>
      ) : (
        <ul className="space-y-1 text-xs mono">
          {connections.map(c => (
            <li key={c.name} className="border border-border rounded px-2 py-1.5">
              <div className="text-text font-bold">{c.name}</div>
              <div className="text-muted">{c.user}@{c.host}:{c.port}</div>
              <div className="text-muted">db: {c.database}</div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
