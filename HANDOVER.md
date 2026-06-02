# mytime2cloud — Developer Handover

> Handover guide for a developer taking over **mytime2cloud** (`cloud-app/`).
> Read this top-to-bottom once, then keep [CLAUDE.md](CLAUDE.md) and
> [backend/CLAUDE.md](backend/CLAUDE.md) open as your day-to-day references.

---

## 1. What the product is

**mytime2cloud** is a multi-tenant SaaS for **attendance, access control, payroll,
visitor management, and biometric device integration**. It is sold to many companies
("tenants"); every tenant is a row in the `companies` table and almost all data is
row-level scoped by `company_id`.

The GitHub repo is **https://github.com/akildevs1000/ideahrms.git** (branch `main`).

---

## 2. Repo layout (this folder = `cloud-app/`)

This is a **monorepo of independently-deployed apps**. There is **no top-level package
manager** — each folder installs and runs on its own. Never run `npm install` /
`composer install` at the root.

| Folder | Stack | What it is |
|---|---|---|
| [backend/](backend/) | Laravel 9, PHP 8, **PostgreSQL** | The API + queues + scheduler + PDF/report engine + device integration. **The source of truth.** Everything else is a client of its REST API. Has its own [CLAUDE.md](backend/CLAUDE.md). |
| [frontend/](frontend/) | Nuxt 2, Vue 2, Vuetify | **The production admin panel.** Also hosts many long-running Node services (log listeners, WhatsApp servers, cron jobs). |
| [loglistner_mqtt/](loglistner_mqtt/) | Node + MQTT + PostgreSQL | Standalone service: device telemetry → broker (`tcp://localhost:1883`) → writes `attendance_logs` directly (bypasses Laravel for throughput). |
| [sync-calender-api/](sync-calender-api/) | Express | Tiny service: fetches UAE public holidays from a Google Calendar ICS. Runs on port `5779`. |
| [tenant-migrator/](tenant-migrator/) | Electron + Vite | Desktop tool for tenant data migration/sync. Has its own [CLAUDE.md](tenant-migrator/CLAUDE.md) and `README.md`. |
| [summary-report/](summary-report/) | Static HTML | Pre-built report viewers, no build step. |

> The wider `D:\projects\mytime2cloud` workspace also holds dozens of satellite apps
> (mobile apps, desktop builds, the new Next.js admin panel, SDK glue, etc.). See the
> root `D:\projects\mytime2cloud\CLAUDE.md` for that map. **Day-to-day product work is
> in `cloud-app/`.**

### Bundled runtimes
You don't need a system PHP/Node to run the core apps:
- PHP 8 is bundled at [backend/php/](backend/php/)
- Node is bundled at [frontend/nodejs/](frontend/nodejs/)
- The `.bat` launchers (`backend/run_backend.bat`, `frontend/run.bat`, etc.) prepend
  these to `PATH`. `open_in_vscode.bat` opens VS Code with the bundled PHP on PATH.

---

## 3. Production / server access

Connection details and the SSH key are committed in **[README/INSTRUCTIONS.md](README/INSTRUCTIONS.md)**
(and a duplicate in [details/README.md](details/README.md)):

- **Server:** `139.59.69.241`, port `53217`, user `root`
- **SSH key:** [README/ssh_login_key.ppk](README/ssh_login_key.ppk) (PuTTY format)
- **App path on server:** `/var/www/mytime2cloud`
- Databases `mytime2cloud` and `mytime2cloud-v2` are **LIVE production** on this host.

> ⚠️ **Treat these as production secrets.** Rotate the server passphrase and any API
> keys after the handover. Reads against prod are fine; **never run unreviewed
> `DELETE`/`UPDATE`/`TRUNCATE`** against the live databases.

Process management on prod is **pm2**. The Nuxt admin panel is pm2 app id `19`
(see the `refresh` script in `frontend/package.json`). Laravel runs queue workers and
a per-minute scheduler via cron.

---

## 4. First-time local setup

### Prerequisites
- Use the **bundled** PHP/Node (above), or install PHP 8 + Composer and Node 16+.
- **PostgreSQL** running locally (production DB is `pgsql`, *not* MySQL — ignore the
  MySQL defaults in `backend/.env.example`, that file is the stock Laravel template).
- Optionally an **MQTT broker** on `tcp://localhost:1883` if you work on device ingestion.

### backend/ (Laravel API)
```powershell
cd backend
# .env already exists locally; if starting fresh, copy and fill it (see §5)
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0       # or: php artisan serve:init  (auto-detects host IP)
php artisan queue:work                 # REQUIRED — most heavy work is queued (PDF, device push, mail)
php artisan schedule:work              # per-tenant cron tasks (Windows test loop)
```
The `attendance_logs` table needs this unique index for the MQTT ingester:
```sql
CREATE UNIQUE INDEX IF NOT EXISTS attendance_logs_uniq
  ON attendance_logs ("DeviceID", "LogTime", "UserID");
```

### frontend/ (Nuxt 2 admin panel — the production UI)
```powershell
cd frontend
npm run dev                            # nuxt dev; host/port from $LOCAL_IP / $LOCAL_PORT
npm run build && npm run start         # production
```
Auth is `@nuxtjs/auth-next` → `POST {BACKEND_URL}/login` → bearer token (refreshed ~24h).

### loglistner_mqtt/
```powershell
cd loglistner_mqtt
npm run start:mqtt                     # waits for broker on 1883, then batch listener + SDK
# or run.bat (auto-restarts on crash)
```

### sync-calender-api/
```powershell
cd sync-calender-api
node server.js                         # /holidays/:year on 0.0.0.0:5779
```

> `node_modules/` is **committed** for `frontend/` and `loglistner_mqtt/` (the bundled
> Node runtime expects them in place). Don't reflexively delete/regenerate them.

---

## 5. Environment variables

Each app has its own `.env`. The **real local `.env` files already exist** — copy them
from prod or ask the outgoing developer for values. Key groups:

### `backend/.env`
- **App:** `APP_KEY`, `APP_URL`, `APP_LINK`, `BASE_URL`, `VERSION`
- **DB (PostgreSQL):** `DB_CONNECTION=pgsql`, `DB_HOST/PORT/DATABASE/USERNAME/PASSWORD`
  — plus a second `Desktop` connection (`DB_DESKTOP_*`) pointing at the remote
  `device_encrypter` DB.
- **Queue/cache:** `QUEUE_CONNECTION=database`, `CACHE_DRIVER`, `SESSION_DRIVER`
- **Biometric SDK:** `SDK_URL`, `SDK_PORT`, `SDK_ENDPOINT`, `SDK_*_COMM_URL`
  (local `192.168.2.27:8080`, prod `https://sdk.mytime2cloud.com`)
- **Camera SDK:** `CAMERA_SDK_URL`, `CAMERA_SDK_LOGIN_USERNAME/PASSWORD`
- **Object storage (DigitalOcean Spaces, via S3 driver):** `DO_SPACE_KEY/SECRET/BUCKET/REGION/ENDPOINT`, `DO_URL`, `BUCKET_URL` (the `AWS_*` keys are blank)
- **WhatsApp:** `WHATSAPP_URL`, `WHATSAPP_CLIENT_ID`, `ADMIN_WHATSAPP_NUMBER`, `WHATSAPP_PROXY_CLIENT`
- **Mail:** `MAIL_*`, `IS_MAIL`, `ADMIN_MAIL_RECEIVERS`
- **Maps / AI:** `LOCATIONIQ_KEY`, `GOOGLE_MAPS_KEY`, `CLAUDE_API_KEY`, `DEEPSEEK_API_KEY`
- **Search:** `WILD_CARD` (`LIKE` for pgsql) — keep consistent with the DB driver.

### `frontend/.env`
`BACKEND_URL` (e.g. `http://localhost:8000/api`), `LOCAL_IP`, `LOCAL_PORT`,
`SOCKET_ENDPOINT`, `SDK_ENDPOINT`, `SECRET_PASS_PHRASE`, `EMPLOYEE_APP_URL`,
`WHATSAPP_ENDPOINT`, `MQTT_SOCKET_HOST`, plus DB creds used by the bundled Node services.

### `loglistner_mqtt/.env`
Postgres connection + MQTT broker settings for the ingester.

---

## 6. How the pieces connect

```
                 devices (biometric / camera / access control)
                   │                          │
        MQTT path  │                          │  HTTP/SDK path
                   ▼                          ▼
        broker :1883 → loglistner_mqtt   Laravel SDK* controllers ──► SDK service
                   │   (batch write)          │                       (dotnet, SDK_URL)
                   ▼                          ▼
              attendance_logs  ◄────────  backend/ (Laravel API)  ──► DigitalOcean Spaces
                                               │                       (PDFs, photos)
                          Postgres LISTEN/NOTIFY│
                                               ▼
              frontend/pg-notification-listener.js → WebSockets (SOCKET_ENDPOINT)
                                               ▼
                          frontend/ admin panel  &  employee/mobile clients
```

- **`backend/` is the source of truth.** All apps are REST clients of `BACKEND_URL`.
- **Device telemetry has two parallel paths:** MQTT (high-throughput, writes
  `attendance_logs` directly) and HTTP/SDK (through Laravel `SDK*` controllers + jobs).
- **Realtime UI** uses a Postgres `LISTEN/NOTIFY` → WebSocket bridge in `frontend/`.
- **Reports:** Laravel renders Blade → dompdf → merges → uploads to DO Spaces → zips.
- **Background work:** queue driver is `database`; ~25 jobs in `backend/app/Jobs/`.
  Run `php artisan queue:work` or nothing heavy happens.

---

## 7. Background services you must keep running (prod)

| Where | What | Why |
|---|---|---|
| backend | `php artisan queue:work` | PDF generation, device pushes, photo upload, WhatsApp/email blasts |
| backend | `php artisan schedule:work` (cron every minute on prod) | **Dynamic per-tenant** scheduler: daily reports, payslips, holiday/leave sync, OX900 device pull (`task:sync_attendance_ox900_logs`), `attendance:rectify` |
| backend | `php artisan heartbeat:listener` | device heartbeats (`heartbeat-listener.bat`) |
| frontend | `log-listener*.js`, `pg-notification-listener.js`, `visitor-*.js`, `whatsapp-server*.js`, `camera-*.js`, `cron-job.js` | device log ingestion, realtime bridge, visitor/WhatsApp/camera workers (run with `node <file>` or pm2) |
| loglistner_mqtt | `npm run start:mqtt` | MQTT device-log ingestion |
| sync-calender-api | `node server.js` | holiday sync |

The scheduler is **dynamic**: it loads `Company::pluck('id')` on every run, so adding a
tenant automatically registers their scheduled tasks. No code change needed per tenant.

---

## 8. Key conventions & gotchas (read before coding)

- **Always scope queries by `company_id`.** Multi-tenancy is enforced *manually* with
  `where('company_id', …)`, not by a package. A missing scope leaks data across tenants.
- **`routes/api.php` is include-only.** Add routes to (or create) a topical file under
  `backend/routes/` and `include()` it — don't add routes to `api.php` directly.
- **Controllers are thin.** Multi-model logic / side effects (notifications, queue
  dispatches, file generation) go in `app/Services/` or a Job, not the controller.
- **Billing** goes through `app/Services/Billing/` (`InvoiceService` etc.) — don't
  reimplement invoice numbering or PDF rendering elsewhere.
- **Two admin frontends exist** across the workspace: `frontend/` (Nuxt 2, production)
  and the new Next.js panel (in the wider workspace). Check which is canonical for a
  feature before adding pages; match the surrounding stack.
- **Tests are sparse and dangerous:** `phpunit.xml` does **not** swap to sqlite, so
  tests run against whatever `DB_CONNECTION` is configured. Don't run them against a
  real/prod DB.
- **Destructive SQL:** the `ARCHIVED_DO_NOT_RUN_*.sql` files in `backend/` and the big
  `*.sql` dumps in this folder are operational artifacts / templates. **Never execute
  them blindly.** See [backend/VALIANT_SYNC_RUNBOOK.md](backend/VALIANT_SYNC_RUNBOOK.md)
  for the tenant-migration tooling and the destructive-SQL warnings.
- **API throttling:** the API middleware group throttles at 200 req/sec; `CheckToken`
  guards device/SDK endpoints.

---

## 9. Common operational commands

```powershell
# backend/ — ad-hoc report generation
php artisan pdf:generate {company_id}
php artisan pdf:access-control-report-generate {company_id} {YYYY-MM-DD}   # date = yesterday
php artisan task:generate_daily_report {company_id}

# queues
php artisan queue:work
php artisan queue:restart
php artisan queue:failed

# migrations / seeders
php artisan migrate
php artisan db:seed --class=StatusSeeder
```

There are ~70 Artisan commands in `backend/app/Console/Commands/` (organized by domain
subfolder) and ~50 route files in `backend/routes/`. Browse those to find existing
functionality before writing new code.

---

## 10. Where to go next

1. [backend/CLAUDE.md](backend/CLAUDE.md) — the API architecture, domain shape, jobs,
   scheduler, integrations. **The most important reference.**
2. [backend/VALIANT_SYNC_RUNBOOK.md](backend/VALIANT_SYNC_RUNBOOK.md) — tenant
   migration/sync invariants and destructive-SQL warnings.
3. [CLAUDE.md](CLAUDE.md) — the monorepo map (this folder).
4. `D:\projects\mytime2cloud\CLAUDE.md` — the full surrounding workspace (mobile apps,
   desktop builds, SDKs, the new Next.js panel).
5. Postman collection: `Postman-Mytime2cloud-New Device-MQTT-API` (repo root).
6. Device/brand reference: `MYTIME2CLOUD-NEW Brand Device.docx` (repo root).

---

## 11. Handover checklist for the new developer

- [ ] Get the real `.env` files for `backend/`, `frontend/`, `loglistner_mqtt/`.
- [ ] Confirm SSH access to `139.59.69.241:53217` with the provided key.
- [ ] Get GitHub access to `akildevs1000/ideahrms`.
- [ ] **Rotate** the server passphrase + API keys (Claude, DeepSeek, DO Spaces,
      WhatsApp, LocationIQ, Google Maps) after access is confirmed.
- [ ] Verify access to the DigitalOcean Spaces account (report/photo storage).
- [ ] Verify the WhatsApp proxy and SDK service (`sdk.mytime2cloud.com`) credentials.
- [ ] Stand up a local Postgres + (optional) MQTT broker and run the backend + frontend.
- [ ] Confirm pm2 process list on prod (admin panel = app id 19; queue workers; cron).
```
