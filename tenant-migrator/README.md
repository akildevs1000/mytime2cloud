# tenant-migrator

Multi-tenant Postgres migration / sync tool for `mytime2cloud`. Replaces the
PowerShell scripts in `cloud-app/backend/` with a single TypeScript engine
exposed via a CLI **and** an Electron desktop UI.

```
.
├── recipes/                   YAML migration recipes (one per tenant)
│   └── tenant-sync.yaml
├── config/
│   ├── connections.yaml       (gitignored) DB credentials
│   └── connections.example.yaml
├── src/
│   ├── engine/                Pure logic. Imported by both CLI and Electron.
│   ├── cli/                   `commander`-based CLI.
│   ├── electron/              main process + preload (IPC).
│   └── ui/                    React + Tailwind renderer.
└── package.json
```

## First-time setup

```bash
cd cloud-app/tenant-migrator
npm install
cp config/connections.example.yaml config/connections.yaml
# edit config/connections.yaml with real host/user/password
```

## CLI usage

```bash
# read-only checks
npm run cli -- list-connections
npm run cli -- audit  -r recipes/tenant-sync.yaml
npm run cli -- verify -r recipes/tenant-sync.yaml

# generate the SQL but don't run it
npm run cli -- dump   -r recipes/tenant-sync.yaml -o dump.sql

# run an existing dump on the recipe's target
npm run cli -- restore -r recipes/tenant-sync.yaml -f dump.sql

# all-in-one: dump + restore + verify
npm run cli -- sync -r recipes/tenant-sync.yaml --yes

# pg_dump backup (requires pg_dump on PATH or pass --pg-dump <full path>)
npm run cli -- backup -r recipes/tenant-sync.yaml -o backups
```

## Desktop app

```bash
# dev: hot-reloading renderer + Electron
npm run dev

# build production bundles
npm run build

# launch a built app
./node_modules/electron/dist/electron.exe .
# or on macOS / Linux:
./node_modules/electron/dist/electron .
```

The desktop window has:

- **Recipes** sidebar (lists `recipes/*.yaml`)
- **Connections** sidebar (lists `config/connections.yaml`)
- **Action panel** with buttons: Audit, Verify, Dump preview, Dry-run,
  Restore, Sync. Writes (Restore / Sync) require an inline confirm.
- **Result panel** showing structured output of the last action.
- **Output log** showing live progress events from the engine.

## Recipe schema

Each `recipes/*.yaml` file describes one tenant migration. See
`recipes/tenant-sync.yaml` for a fully worked example. Key fields:

- `company_id` — the tenant being migrated
- `source` / `target` — connection names from `config/connections.yaml`
- `remap_offset` — added to source ids when they collide with another tenant
  on target. Default 1,000,000,000. **Don't change after first run.**
- `table_overrides` — per-table WHERE / exclude / skip_remap toggles
- `tenant_key_overrides` — special-case the `companies` table (id is the tenant key)
- `linked_tables` — tables without `company_id`, scoped via FK
- `fk_cascades` — when a parent's id is remapped, rewrite the listed child columns
- `pre_restore_sql` / `post_restore_sql` — free-form SQL hooks (run inside the
  same transaction as the dump)

## Safety invariants

These are baked into the engine. Don't break them.

1. The dump file starts with `BEGIN;` and ends with `COMMIT;`. Any error
   rolls back — target DB is unchanged.
2. Every INSERT uses `ON CONFLICT DO NOTHING`. Existing target rows are
   never overwritten.
3. ID remapping is deterministic: `new_id = source_id + remap_offset`.
   Re-running the dump produces no duplicates.
4. The dump file contains **no** DELETE / UPDATE / TRUNCATE statements.
5. Cleanup of orphan tenants is a separate, manual step. The engine does
   not auto-cleanup. (See `cloud-app/backend/VALIANT_SYNC_RUNBOOK.md`.)

## What's not built yet

- AI co-pilot panel (deferred — see chat history with Claude Code).
- Run history view (current UI shows last result only; history persistence is TODO).
- Recipe editor (Monaco) inline in Electron (today: edit YAML in your IDE).
- Saved-connection encryption / OS-keychain integration (today: plain YAML, gitignored).
