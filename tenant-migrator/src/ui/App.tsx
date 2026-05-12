import { useEffect, useState } from "react";
import type { ConnectionRow, ProgressEvent, RecipeMeta, Overrides } from "./types";
import { Header } from "./components/Header";
import { ConnectionList } from "./components/ConnectionList";
import { RecipePicker } from "./components/RecipePicker";
import { RecipeSummary } from "./components/RecipeSummary";
import { ActionPanel } from "./components/ActionPanel";
import { OutputLog } from "./components/OutputLog";
import { ResultPanel } from "./components/ResultPanel";

export default function App() {
  const [connections, setConnections]   = useState<ConnectionRow[]>([]);
  const [recipes, setRecipes]           = useState<string[]>([]);
  const [selectedRecipe, setSelectedRecipe] = useState<string | null>(null);
  const [recipeMeta, setRecipeMeta]     = useState<RecipeMeta | null>(null);
  const [sourceOverride, setSourceOverride]   = useState<string | null>(null);
  const [targetOverride, setTargetOverride]   = useState<string | null>(null);
  const [companyOverride, setCompanyOverride] = useState<string | null>(null);
  const [logs, setLogs]                 = useState<ProgressEvent[]>([]);
  const [busy, setBusy]                 = useState(false);
  const [result, setResult]             = useState<unknown>(null);

  // initial load
  useEffect(() => {
    (async () => {
      const [conns, recs] = await Promise.all([
        window.api.listConnections(),
        window.api.listRecipes()
      ]);
      if (Array.isArray(conns)) setConnections(conns);
      else if (conns && "error" in conns) {
        setLogs(prev => [...prev, { kind: "error", message: `connections: ${conns.error}` }]);
      }
      if (Array.isArray(recs)) {
        setRecipes(recs);
        if (recs.length > 0) selectRecipe(recs[0]);
      } else if (recs && "error" in recs) {
        setLogs(prev => [...prev, { kind: "error", message: `recipes: ${recs.error}` }]);
      }
    })();

    const off = window.api.onProgress(e => {
      setLogs(prev => [...prev, { ...e, kind: e.kind }]);
    });
    return off;
  }, []);

  async function selectRecipe(file: string) {
    setSelectedRecipe(file);
    setRecipeMeta(null);
    setSourceOverride(null);
    setTargetOverride(null);
    setCompanyOverride(null);
    const res = await window.api.loadRecipe(file);
    if ("error" in res) {
      setLogs(prev => [...prev, { kind: "error", message: res.error }]);
    } else {
      setRecipeMeta(res.recipe);
    }
  }

  // Effective values — override if set, else recipe default.
  const source    = sourceOverride  ?? recipeMeta?.source     ?? "";
  const target    = targetOverride  ?? recipeMeta?.target     ?? "";
  const companyId = companyOverride ?? recipeMeta?.company_id ?? "";

  function buildOverrides(): Overrides | undefined {
    if (!recipeMeta) return undefined;
    const o: Overrides = {};
    if (sourceOverride  && sourceOverride  !== recipeMeta.source)     o.source    = sourceOverride;
    if (targetOverride  && targetOverride  !== recipeMeta.target)     o.target    = targetOverride;
    if (companyOverride && companyOverride !== recipeMeta.company_id) o.companyId = companyOverride;
    return Object.keys(o).length === 0 ? undefined : o;
  }

  async function runAction(name: "audit" | "verify" | "dump" | "restore" | "sync" | "dryRun") {
    if (!selectedRecipe) return;
    setBusy(true); setResult(null);
    const overrides = buildOverrides();
    setLogs(prev => [...prev, { kind: "info", message: `▶ ${name} started${overrides ? " (with overrides)" : ""}` }]);
    try {
      let res: unknown;
      if (name === "audit")    res = await window.api.audit(selectedRecipe, overrides);
      if (name === "verify")   res = await window.api.verify(selectedRecipe, overrides);
      if (name === "dump")     res = await window.api.dump(selectedRecipe, overrides);
      if (name === "restore")  res = await window.api.restore(selectedRecipe, false, overrides);
      if (name === "sync")     res = await window.api.sync(selectedRecipe, overrides);
      if (name === "dryRun")   res = await window.api.restore(selectedRecipe, true, overrides);
      setResult({ kind: name, value: res });
    } finally {
      setBusy(false);
    }
  }

  function resetOverrides() {
    setSourceOverride(null);
    setTargetOverride(null);
    setCompanyOverride(null);
  }

  return (
    <div className="flex flex-col h-full">
      <Header />
      <div className="flex flex-1 min-h-0">
        {/* Left rail */}
        <aside className="w-72 border-r border-border p-4 space-y-4 overflow-y-auto">
          <RecipePicker
            recipes={recipes}
            selected={selectedRecipe}
            onSelect={selectRecipe}
          />
          <ConnectionList connections={connections} />
        </aside>

        {/* Main */}
        <main className="flex-1 flex flex-col min-w-0">
          <div className="p-4 border-b border-border">
            <RecipeSummary
              recipe={recipeMeta}
              fileName={selectedRecipe}
              connections={connections}
              source={source}
              target={target}
              companyId={companyId}
              onSourceChange={setSourceOverride}
              onTargetChange={setTargetOverride}
              onCompanyChange={setCompanyOverride}
              onResetOverrides={resetOverrides}
            />
          </div>
          <div className="p-4 border-b border-border">
            <ActionPanel busy={busy} disabled={!selectedRecipe} onRun={runAction} />
          </div>
          <div className="flex-1 grid grid-cols-2 min-h-0">
            <ResultPanel result={result} />
            <OutputLog logs={logs} onClear={() => setLogs([])} />
          </div>
        </main>
      </div>
    </div>
  );
}
