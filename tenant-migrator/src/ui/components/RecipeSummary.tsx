import type { ConnectionRow, RecipeMeta } from "../types";
import { ConnectionPicker } from "./ConnectionPicker";
import { CompanyPicker }    from "./CompanyPicker";
import { TargetStatus }     from "./TargetStatus";

interface Props {
  recipe:        RecipeMeta | null;
  fileName:      string | null;
  connections:   ConnectionRow[];
  source:        string;
  target:        string;
  companyId:     string;
  onSourceChange:    (name: string) => void;
  onTargetChange:    (name: string) => void;
  onCompanyChange:   (id: string) => void;
  onResetOverrides:  () => void;
}

export function RecipeSummary({
  recipe, fileName, connections,
  source, target, companyId,
  onSourceChange, onTargetChange, onCompanyChange, onResetOverrides
}: Props) {
  if (!fileName) return <div className="text-muted text-sm">Pick a recipe on the left.</div>;
  if (!recipe)   return <div className="text-muted text-sm">Loading {fileName}…</div>;

  const overridden = source !== recipe.source
                  || target !== recipe.target
                  || companyId !== recipe.company_id;

  return (
    <div className="space-y-2">
      <div className="flex items-baseline gap-3 flex-wrap">
        <div className="text-lg font-semibold">{recipe.name}</div>
        <div className="text-xs mono text-muted">{fileName}</div>
        <div className="ml-auto flex gap-2 items-center text-xs">
          <span className="tag bg-panel text-muted border border-border">offset +{recipe.remap_offset.toLocaleString()}</span>
          {overridden && (
            <button onClick={onResetOverrides} className="text-warn underline text-xs">
              reset to recipe defaults
            </button>
          )}
        </div>
      </div>
      {recipe.description && <div className="text-xs text-muted">{recipe.description}</div>}

      <div className="flex flex-wrap gap-2 items-center">
        <ConnectionPicker
          flavour="source"
          label="SOURCE"
          connections={connections}
          value={source}
          recipeDefault={recipe.source}
          onChange={onSourceChange}
        />
        <span className="text-muted text-xs">→</span>
        <ConnectionPicker
          flavour="target"
          label="TARGET"
          connections={connections}
          value={target}
          recipeDefault={recipe.target}
          onChange={onTargetChange}
        />
      </div>

      <div>
        <CompanyPicker
          connection={source}
          selectedId={companyId}
          recipeDefault={recipe.company_id}
          onChange={onCompanyChange}
        />
        <div className="text-[10px] text-muted mt-1">
          Lists companies present on the <span className="text-text">source</span> DB (the one you're migrating from).
        </div>
      </div>

      {target && companyId && (
        <TargetStatus target={target} companyId={companyId} />
      )}

      {overridden && (
        <div className="text-[11px] text-warn">
          ⚠ Overrides are in-memory only — the YAML file is not modified.
          Click <span className="underline">reset</span> above to revert.
        </div>
      )}
    </div>
  );
}
