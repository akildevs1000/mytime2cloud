# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository shape

`mytime2cloud` is a multi-tenant SaaS for attendance, access control, payroll, visitor management, and biometric device integration. This repo is a **monorepo of independently-deployed apps**, not a single project. Each subfolder has its own dependencies, run scripts, and lifecycle. There is no top-level package manager — never run `npm install` or similar at the root.

| Folder | Stack | Purpose |
|---|---|---|
| [backend/](backend/) | Laravel 9, PHP 8, PostgreSQL | API, queues, scheduler, PDF generation, device integration. **Has its own [CLAUDE.md](backend/CLAUDE.md) — read it before touching anything in there.** |
| [frontend/](frontend/) | Nuxt 2, Vue 2, Vuetify | Current admin panel (the production UI). Also hosts a pile of long-running Node scripts (log listeners, cron jobs, WhatsApp server). |
| [frontend-new/](frontend-new/) | Next.js 15, React 19, Tailwind 4, Radix UI | Replacement admin panel under active development. Builds to a static export (`output: 'export'`). |
| [employee/](employee/) | Nuxt 2, Vue 2, Vuetify, PWA | Employee-facing mobile/PWA portal (separate from the admin panel). |
| [loglistner_mqtt/](loglistner_mqtt/) | Node.js, MQTT, PostgreSQL | Standalone MQTT broker/ingester for device telemetry → `attendance_logs`. Runs as its own service. |
| [sync-calender-api/](sync-calender-api/) | Express | Tiny service that fetches UAE public holidays from Google Calendar ICS and reformats for the backend. Port 5779. |
| [summary-report/](summary-report/) | Static HTML | Pre-built report viewers (no build step). |

The bundled PHP runtime lives at [backend/php/](backend/php/) and the bundled Node runtime at [frontend/nodejs/](frontend/nodejs/). The `.bat` launchers (`run_backend.bat`, `frontend/run.bat`, etc.) prepend these to `PATH` — use them when a system PHP/Node isn't installed.

## Commands

### backend/ — Laravel API
See [backend/CLAUDE.md](backend/CLAUDE.md) for the full list. The most common:

```powershell
cd backend
php artisan serve --host=0.0.0.0     # or: serve:init (auto-detects host IP)
php artisan queue:work               # QUEUE_CONNECTION=database, most heavy work is queued
php artisan schedule:work            # Per-tenant cron tasks (Windows test loop)
./vendor/bin/phpunit                 # ⚠ phpunit.xml does NOT swap DB — tests hit the configured DB
```

### frontend/ — Nuxt 2 admin panel

```powershell
cd frontend
npm run dev                          # nuxt dev — host/port from $LOCAL_IP / $LOCAL_PORT
npm run build && npm run start       # production
```

Required env: `LOCAL_IP`, `LOCAL_PORT`, `BACKEND_URL` (e.g. `http://localhost:8000/api`), `SOCKET_ENDPOINT`, `SECRET_PASS_PHRASE`. Auth is `@nuxtjs/auth-next` against `BACKEND_URL/login`.

The folder also ships standalone Node services (run individually with `node <file>.js` or pm2):
- `log-listener.js`, `log-listener-batch.js`, `log-listener-own.js` — device log ingestion variants
- `pg-notification-listener.js` — Postgres `LISTEN/NOTIFY` bridge
- `visitor-log-listener.js`, `visitor-worker.js` — visitor module workers
- `whatsapp-server.js`, `whatsapp-server-bulk.js` — WhatsApp proxies
- `camera-log-listener.js`, `camera-xmlreader.js` — camera SDK glue
- `cron-job.js`, `cpuMonitor.js`, `worker.js` — misc background tasks

### frontend-new/ — Next.js 15 admin panel

```powershell
cd frontend-new
npm run dev                          # next dev --turbopack on 0.0.0.0:3001
npm run build                        # static export to out/
```

Static export only (`output: 'export'` in [next.config.mjs](frontend-new/next.config.mjs)) — no Next.js server features (no API routes, no SSR, no middleware). Anything dynamic must call the Laravel backend.

### employee/ — PWA

```powershell
cd employee
npm run dev                          # Nuxt 2, default port 3000
npm run build && npm run start
```

### loglistner_mqtt/ — MQTT ingester

```powershell
cd loglistner_mqtt
npm run start:mqtt                   # runs the batch listener + device SDK concurrently
# Or use run.bat which auto-restarts on crash.
```

Requires a running MQTT broker on `tcp://localhost:1883` (the `wait-on tcp:1883` guard in `start:mqtt`) and a Postgres `attendance_logs` table with the unique index documented inline:

```sql
CREATE UNIQUE INDEX IF NOT EXISTS attendance_logs_uniq
  ON attendance_logs ("DeviceID", "LogTime", "UserID");
```

### sync-calender-api/

```powershell
cd sync-calender-api
node server.js                       # serves /holidays/:year on 0.0.0.0:5779
```

## Architecture and integration points

### How the pieces connect
- `backend/` is the source of truth. All other apps are clients of its REST API (base `BACKEND_URL`).
- `frontend/` and `employee/` authenticate via `@nuxtjs/auth-next` → `POST /login` → bearer token, refreshed every 24 h. `frontend-new/` reads the API directly via `axios`.
- Device telemetry has two parallel paths:
  - **MQTT path:** devices → broker (1883) → [loglistner_mqtt/](loglistner_mqtt/) batch listener → `attendance_logs` table directly (bypasses Laravel for write throughput).
  - **HTTP/SDK path:** devices → Laravel `SDK*` controllers → models. The SDK service (`SDK_URL`, default `https://sdk.mytime2cloud.com`) is a separate dotnet binary that the Laravel backend calls for push-to-device operations.
- Realtime UI updates use a Postgres `LISTEN/NOTIFY` bridge ([frontend/pg-notification-listener.js](frontend/pg-notification-listener.js)) that fans events out over WebSockets (`SOCKET_ENDPOINT`).
- PDF reports generated in Laravel are uploaded to **DigitalOcean Spaces** (S3-compatible, credentials in backend `.env` under `DO_SPACE_*`).

### Two coexisting admin frontends
[frontend/](frontend/) (Nuxt 2 + Vuetify) is the production admin panel. [frontend-new/](frontend-new/) (Next.js 15 + Tailwind/Radix) is its in-progress replacement. Many features exist in both — when adding a new page, check both folders' [pages/](frontend/pages/) / [src/app/](frontend-new/src/app/) trees first to understand which is canonical for the feature, and match the surrounding code's stack rather than mixing.

### Tenant isolation
The whole platform is row-level multi-tenant on `companies.id`. Almost every API call carries an implicit `company_id`. Frontend code rarely needs to think about this (the bearer token's user resolves the company), but **never write a backend query without a `company_id` scope**. See [backend/CLAUDE.md](backend/CLAUDE.md) and [backend/VALIANT_SYNC_RUNBOOK.md](backend/VALIANT_SYNC_RUNBOOK.md) for the migration tooling and the destructive-SQL warnings.

## Repo-wide conventions

- The big SQL dumps in the root (`database-backup.sql`, `v2-database-backup.sql`) and in `backend/` are operational artifacts — `.gitignore` excludes `*.sql`, so any committed `.sql` here was added with `-f`. Don't run them blindly; production both `mytime2cloud` and `mytime2cloud-v2` databases are LIVE.
- The `ARCHIVED_DO_NOT_RUN_*.sql` files in `backend/` are destructive templates retained for reference only — see [backend/VALIANT_SYNC_RUNBOOK.md](backend/VALIANT_SYNC_RUNBOOK.md).
- `node_modules/` is committed in some subfolders (frontend-new ignores it but it's checked in for `frontend/`, `employee/`, `loglistner_mqtt/`). Don't reflexively delete or regenerate them — the bundled `frontend/nodejs/` runtime expects them in place.
- Root `open_in_vscode.bat` just adds the bundled PHP to PATH and opens VS Code; subfolder copies do the equivalent for that subfolder.
