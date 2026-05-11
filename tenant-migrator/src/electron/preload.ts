// Preload script: exposes a typed `window.api` to the renderer. Renderer
// CANNOT use Node directly; it must call through these IPC channels.

import { contextBridge, ipcRenderer } from "electron";

export interface Overrides {
  source?:    string;
  target?:    string;
  companyId?: string;
}

const api = {
  // ----- recipes & connections -----
  listRecipes:     () => ipcRenderer.invoke("recipes:list"),
  loadRecipe:      (file: string) => ipcRenderer.invoke("recipes:load", file),
  listConnections: () => ipcRenderer.invoke("connections:list"),
  listCompanies:   (sourceConnection: string) => ipcRenderer.invoke("companies:list", sourceConnection),
  tenantStatus:    (connection: string, companyId: string) => ipcRenderer.invoke("tenant:status", connection, companyId),

  // ----- engine commands (overrides optional) -----
  audit:    (recipeFile: string, overrides?: Overrides) => ipcRenderer.invoke("engine:audit",   recipeFile, overrides),
  verify:   (recipeFile: string, overrides?: Overrides) => ipcRenderer.invoke("engine:verify",  recipeFile, overrides),
  dump:     (recipeFile: string, overrides?: Overrides) => ipcRenderer.invoke("engine:dump",    recipeFile, overrides),
  restore:  (recipeFile: string, dryRun?: boolean, overrides?: Overrides) =>
              ipcRenderer.invoke("engine:restore", recipeFile, dryRun, overrides),
  sync:     (recipeFile: string, overrides?: Overrides) => ipcRenderer.invoke("engine:sync", recipeFile, overrides),

  // ----- streaming progress -----
  onProgress: (cb: (event: { kind: string; message?: string; ordinal?: number; total?: number }) => void) => {
    const listener = (_e: unknown, payload: any) => cb(payload);
    ipcRenderer.on("engine:progress", listener);
    return () => ipcRenderer.removeListener("engine:progress", listener);
  }
};

contextBridge.exposeInMainWorld("api", api);

export type Api = typeof api;
