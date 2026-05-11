import { defineConfig, externalizeDepsPlugin } from "electron-vite";
import react from "@vitejs/plugin-react";
import { resolve } from "node:path";

export default defineConfig({
  main: {
    plugins: [externalizeDepsPlugin()],
    build: {
      lib: {
        entry: resolve(__dirname, "src/electron/main.ts"),
        formats: ["cjs"],
        fileName: () => "main.cjs"
      }
    },
    resolve: {
      alias: { "@engine": resolve(__dirname, "src/engine") }
    }
  },
  preload: {
    plugins: [externalizeDepsPlugin()],
    build: {
      lib: {
        entry: resolve(__dirname, "src/electron/preload.ts"),
        formats: ["cjs"],
        fileName: () => "preload.cjs"
      }
    }
  },
  renderer: {
    root: resolve(__dirname, "src/ui"),
    build: {
      rollupOptions: {
        input: resolve(__dirname, "src/ui/index.html")
      }
    },
    plugins: [react()],
    resolve: {
      alias: { "@ui": resolve(__dirname, "src/ui") }
    }
  }
});
