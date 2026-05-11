import type { ConnectionRow } from "../types";

interface Props {
  label: string;
  connections: ConnectionRow[];
  value: string;            // currently selected connection name
  recipeDefault: string;    // the value declared in the recipe YAML
  onChange: (name: string) => void;
  flavour?: "source" | "target";
}

// A tiny inline label + dropdown. Highlights when overridden vs the recipe.
export function ConnectionPicker({ label, connections, value, recipeDefault, onChange, flavour }: Props) {
  const overridden = value !== recipeDefault;
  const cfg = connections.find(c => c.name === value);
  const live = !!cfg && /139\.59\.69\.241|prod|live/i.test(`${cfg.host} ${cfg.name ?? ""}`);

  const ringClass = overridden
    ? "ring-1 ring-warn/60"
    : "ring-1 ring-border/60";

  const labelClass =
    flavour === "target" && live ? "text-danger"
    : flavour === "source" && live ? "text-warn"
    : "text-muted";

  // Hide readonly connections from the Target dropdown so they cannot be
  // selected as a write destination. The IPC layer also rejects them server-side.
  const visible = flavour === "target"
    ? connections.filter(c => !c.readonly)
    : connections;

  return (
    <div className={`panel px-2 py-1 flex items-center gap-2 ${ringClass}`}>
      <span className={`text-[10px] uppercase tracking-wide ${labelClass}`}>{label}</span>
      <select
        value={value}
        onChange={e => onChange(e.target.value)}
        className="bg-bg border border-border rounded text-xs mono px-1.5 py-0.5 focus:outline-none focus:border-accent"
      >
        {visible.map(c => (
          <option key={c.name} value={c.name}>{c.name}</option>
        ))}
      </select>
      {cfg && <span className="text-[10px] text-muted mono">{cfg.user}@{cfg.host}/{cfg.database}</span>}
      {overridden && (
        <span className="text-[10px] text-warn">(override; default {recipeDefault})</span>
      )}
    </div>
  );
}
