interface Props {
  recipes: string[];
  selected: string | null;
  onSelect: (f: string) => void;
}

export function RecipePicker({ recipes, selected, onSelect }: Props) {
  return (
    <div>
      <h3 className="text-xs uppercase text-muted tracking-wide mb-1">Recipes</h3>
      {recipes.length === 0 ? (
        <div className="text-xs text-muted">No recipe YAML files in /recipes</div>
      ) : (
        <ul className="space-y-1">
          {recipes.map(f => (
            <li key={f}>
              <button
                onClick={() => onSelect(f)}
                className={`w-full text-left text-sm px-2 py-1 rounded mono truncate ${
                  selected === f ? "bg-accent/20 text-accent" : "hover:bg-panel"
                }`}
              >{f}</button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
