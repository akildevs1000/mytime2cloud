// Electron main process. Creates the window, registers IPC handlers, owns
// the engine connection-pool lifecycle.

import { app, BrowserWindow } from "electron";
import { resolve } from "node:path";
import { registerIpcHandlers, disposeIpcResources } from "./ipc.js";

// __dirname is provided by Node when bundle is CJS (electron.vite.config.ts).

let mainWindow: BrowserWindow | null = null;

async function createWindow() {
  mainWindow = new BrowserWindow({
    width:           1280,
    height:          820,
    minWidth:        1024,
    minHeight:       640,
    backgroundColor: "#0d1117",
    title:           "Tenant Migrator",
    autoHideMenuBar: true,
    webPreferences: {
      preload:           resolve(__dirname, "../preload/preload.cjs"),
      contextIsolation:  true,
      nodeIntegration:   false,
      sandbox:           false
    }
  });

  if (process.env.ELECTRON_RENDERER_URL) {
    // Dev: vite dev server
    await mainWindow.loadURL(process.env.ELECTRON_RENDERER_URL);
    mainWindow.webContents.openDevTools({ mode: "detach" });
  } else {
    // Prod: built renderer
    await mainWindow.loadFile(resolve(__dirname, "../renderer/index.html"));
  }

  mainWindow.on("closed", () => { mainWindow = null; });
}

app.whenReady().then(async () => {
  registerIpcHandlers();
  await createWindow();

  app.on("activate", async () => {
    if (BrowserWindow.getAllWindows().length === 0) await createWindow();
  });
});

app.on("window-all-closed", async () => {
  await disposeIpcResources();
  if (process.platform !== "darwin") app.quit();
});

app.on("before-quit", async () => {
  await disposeIpcResources();
});
