# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this folder is

A TypeScript Postgres tenant-migration tool with **two front-ends sharing one engine**: a `commander` CLI and an Electron desktop app. It replaces the destructive PowerShell scripts in [../backend/](../backend/) that the [VALIANT_SYNC_RUNBOOK](../backend/VALIANT_SYNC_RUNBOOK.md) warns about. It is the recommended path for moving a single tenant's rows between Postgres databases (e.g. `mytime2cloud` → `mytime2cloud-v2`).

The user-facing usage (CLI commands, recipe schema, Electron UI overview) is in [README.md](README.md). Don't duplicate it here — the rest of this file is the stuff you can't easily learn from the README.

## Commands

```powershell
npm install                          # first run only
cp config/connections.example.yaml config/connections.yaml   # then edit credentials

npm run cli -- <subcommand>          # tsx — runs TS directly, no build step
npm run dev                          # electron-vite: hot-reloading renderer + Electron main
npm run build                        # bundles main / preload / renderer to out/
npm run lint                         # tsc --noEmit (this is the only static check)
npm run package                      # electron-builder → release/
```

There are **no tests** — `npm run lint` is the entire CI surface. Don't claim a feature works without exercising it against a real Postgres (use `local-snapshot` from [config/connections.example.yaml](config/connections.example.yaml)).

## Architecture

### One engine, two callers

[src/engine/](src/engine/) is pure logic — no Electron, no `commander`, no `console.log`. Every public function flows through [src/engine/index.ts](src/engine/index.ts). Both [src/cli/index.ts](src/cli/index.ts) and [src/electron/ipc.ts](src/electron/ipc.ts) import from there. **Never reach into individual engine files from a caller, and never let UI/CLI concerns leak into engine/.** If you find yourself needing logging in the engine, take an `onEvent` callback (see [restore.ts:90](src/engine/restore.ts#L90) for the pattern).

The Electron main process exposes its handlers through a typed IPC bridge ([src/electron/preload.ts](src/electron/preload.ts) → `window.api`). The renderer ([src/ui/](src/ui/), React 19 + Tailwind) **cannot** use Node directly — sandbox is off but `nodeIntegration: false` and `contextIsolation: true` are enforced.

### How a sync actually works

1. **Schema introspection** ([schema.ts](src/engine/schema.ts)): both source and target are read via `information_schema.columns`. This is what powers the column-intersection trick — when source has columns target doesn't, we silently drop them; when target has extra columns, the `INSERT` only names the shared ones.
2. **Filter computation** ([dump.ts:107](src/engine/dump.ts#L107)): tables come from three places, in this priority — `tenant_key_overrides` (e.g. `companies` itself), then anything with a literal `company_id` column (auto-discovered), then explicit `linked_tables` (no `company_id`, scoped via FK).
3. **Collision detection** ([collisions.ts](src/engine/collisions.ts)): for each in-scope table with an `id` column, compare source ids against target rows owned by *another* tenant. Real collisions get remapped to `source_id + remap_offset` (default 1,000,000,000). Same-tenant overlap is *not* a collision — `ON CONFLICT DO NOTHING` swallows it, which is what makes re-runs idempotent.
4. **Dump emission** ([dump.ts:155](src/engine/dump.ts#L155)): every column is cast to `::text` on the source side (`SELECT col::text AS col`) so we get a single uniform string representation for arrays/jsonb/timestamps/bytea — then re-quoted on the JS side via [quote.ts](src/engine/quote.ts). This is the central type-handling trick; don't introduce per-type code paths.
5. **FK cascade rewrites** ([dump.ts:138](src/engine/dump.ts#L138)): when a parent table got remapped, listed child columns are rewritten to point at the new parent ids in the same row emission pass.
6. **Restore** ([restore.ts](src/engine/restore.ts)): runs the dump in one transaction. Custom SQL splitter respects single-quoted strings, dollar-quoted blocks, and `--`/`/* */` comments. The dump's own `BEGIN;`/`COMMIT;` are recognized and routed to `client.query("BEGIN"/"COMMIT")`; any error inside triggers `ROLLBACK` and re-throws.

### Recipes vs UI overrides

A recipe ([recipes/*.yaml](recipes/), validated by Zod in [recipe.ts](src/engine/recipe.ts)) declares defaults: `company_id`, `source`, `target`. The Electron UI lets you override those per-run via [ipc.ts:47 `applyOverrides`](src/electron/ipc.ts#L47) — **the YAML on disk is never modified**. The CLI has no override flags; it always runs the recipe as-written. If you add a new engine input, decide upfront whether it belongs in the recipe (durable, declarative) or as a per-run override (operator-controlled).

`{{company_id}}` placeholders in any recipe SQL string (`where`, `foreign_filter`, `linked_tables[].where`, `pre_restore_sql`, `post_restore_sql`) are expanded by [`expandPlaceholders`](src/engine/recipe.ts#L71). If you add a new templated field, route it through that helper.

## Safety invariants — do not break

These are baked into [dump.ts:195 `buildHeader`](src/engine/dump.ts#L195) / [dump.ts:224 `buildFooter`](src/engine/dump.ts#L224) / [dump.ts:189](src/engine/dump.ts#L189):

1. Every dump file is `BEGIN; … COMMIT;` — partial failure rolls back the target untouched.
2. Every emitted INSERT ends in `ON CONFLICT DO NOTHING`. Existing target rows are never overwritten.
3. ID remapping is **deterministic** (`new_id = source_id + remap_offset`). Same source row → same new id every run, so re-running is safe.
4. The dump file contains **no** `DELETE` / `UPDATE` / `TRUNCATE`. Cleanup of orphan tenants is a separate, manual step (see the runbook in `../backend/`).
5. `remap_offset` defaults to 1,000,000,000 and **must not change after the first run** for a given tenant — doing so would re-remap already-migrated rows to new ids and break referential integrity.

If you're tempted to add a code path that violates any of these, stop and ask first.

## Connections & secrets

[config/connections.yaml](config/connections.yaml) is **gitignored** (see [.gitignore](.gitignore)). It holds plain-text DB passwords. There is no encryption / keychain integration yet (README "What's not built yet"). Don't paste real credentials into recipes, examples, commit messages, or chat.

The CLI/Electron both default to `config/connections.yaml` resolved from `process.cwd()` ([connection.ts:33](src/engine/connection.ts#L33)). Run commands from this folder.

`pg.Pool` instances are cached per connection name and shared across an entire session. Always finish CLI commands with `await conns.closeAll()` (see existing handlers).

## Build wiring

[electron.vite.config.ts](electron.vite.config.ts) emits three CJS bundles into `out/`: `main/main.cjs`, `preload/preload.cjs`, and `renderer/index.html`. The `main` field in [package.json](package.json) points at `out/main/main.cjs` so `electron .` works after `npm run build`. The CLI does not go through electron-vite — it runs directly via `tsx` in dev. There's a `tsconfig.cli.json` and a `build:cli` script if you ever need an emitted CLI bundle, but nothing currently uses them.

`type: "module"` is set at the package level. Engine source uses ESM with `.js` extensions in import specifiers (TS resolves them to `.ts`) — keep that style or `tsx` and `electron-vite` will diverge.
