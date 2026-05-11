# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`mytime2cloud` backend — a multi-tenant Laravel 9 / PHP 8 SaaS for attendance, access control, payroll, visitor management, and biometric device integration. Tenants are rows in `companies`; nearly every domain table carries `company_id` and queries are expected to scope on it.

PostgreSQL is the production database (see `.env` `DB_CONNECTION=pgsql`), even though [config/database.php](config/database.php) defaults to sqlite. A second `Desktop` connection points at a remote `device_encrypter` DB used by the device-side desktop client.

## Common commands

PHP 8 lives in the bundled `php/` folder. Use the same `php` everywhere.

```powershell
# Dev server (also: run_backend.bat → serve:init picks the host IP automatically)
php artisan serve --host=0.0.0.0
php artisan serve:init

# Queues — most heavy work (PDF generation, device pushes, photo upload, WhatsApp, reports) runs as queued jobs
php artisan queue:work
php artisan queue:restart
php artisan queue:failed

# Scheduler — every minute on prod (cron). On Windows for testing:
php artisan schedule:work

# Heartbeat listener — long-running, see heartbeat-listener.bat
php artisan heartbeat:listener

# Tests (PHPUnit 9 — note phpunit.xml does NOT swap DB to sqlite, tests hit the configured DB)
./vendor/bin/phpunit
./vendor/bin/phpunit --filter EmployeeStoreFromDeviceTest
./vendor/bin/phpunit tests/Feature/EmployeeStoreFromDeviceTest.php

# Migrations / seeders
php artisan migrate
php artisan db:seed --class=StatusSeeder

# Frequent ad-hoc operational commands
php artisan pdf:generate {company_id}
php artisan pdf:access-control-report-generate {company_id} {YYYY-MM-DD}   # date should be yesterday
php artisan task:generate_daily_report {company_id}
php artisan pdf:generate-template4 {company_id} "{Tenant Name}" {start} {end} {emp_id} {dept_id} {desig_id}
```

## Architecture

### Request flow
- All HTTP entry points are in [routes/api.php](routes/api.php), which is just `include()`s of ~50 topical route files in [routes/](routes/) (e.g. `attendance.php`, `device.php`, `payroll.php`, `community/*`, `alarm/*`). When adding a new resource group, create a new file under `routes/` and `include()` it from `api.php`.
- API middleware group throttles at **200 req/sec** ([app/Http/Kernel.php](app/Http/Kernel.php)). The `CheckToken` middleware guards device/SDK endpoints. Sanctum is installed but `EnsureFrontendRequestsAreStateful` is currently commented out.
- ~130 controllers in [app/Http/Controllers/](app/Http/Controllers/) plus subfolders (`API/`, `Dashboards/`, `Desktop/`, `ContactForm/`, `Camera2.php`). Most are thin — they call into `Services/` (Attendance, Billing) or directly into Eloquent models.

### Domain shape
- ~107 Eloquent models in [app/Models/](app/Models/) plus `Community/` (room/floor/tanent/parking — visitor/community-living domain). Permissions use `spatie/laravel-permission` (see `config/permission.php` and the `roles`/`permissions` tables).
- Multi-tenancy is **not** package-managed — it is enforced manually with `where('company_id', …)` in queries. When writing new code, **always scope by `company_id`**. The Valiant migration runbook ([VALIANT_SYNC_RUNBOOK.md](VALIANT_SYNC_RUNBOOK.md)) is the source of truth on tenant isolation invariants.

### Background work
- Queue driver is `database` (`QUEUE_CONNECTION=database`). Long-running operations (PDF generation, S3/DO Spaces uploads, biometric SDK pushes, WhatsApp/email blasts) live in [app/Jobs/](app/Jobs/) (~25 jobs) and must be dispatched, not run inline.
- The scheduler ([app/Console/Kernel.php](app/Console/Kernel.php)) is **dynamic** — on every `schedule()` call it loads `Company::pluck('id')` and `PayrollSetting::get(...)` and registers per-tenant tasks in a loop. Adding a tenant automatically adds their daily reports, payslip generation, holiday/leave sync, AI streak checks, etc. Per-minute jobs include `task:sync_attendance_ox900_logs` (OX900 device pull) and `attendance:rectify`.
- ~70 Artisan commands in [app/Console/Commands/](app/Console/Commands/) organized by domain subfolder (`Shift/`, `AccessControl/`, `AI/`, `Test/`, `V1/`, `Automation/Alerts/`, `Automation/Multi/`).

### External integrations
- **Biometric/access-control SDK** — `SDK_URL` (currently `192.168.2.27:8080` for local, `https://sdk.mytime2cloud.com` for prod). The `SDK*` controllers and `ProcessSDKCommand`, `PushUserToDevice`, `SyncDateTimeToDevice*` jobs talk to it.
- **Camera SDK** — `CAMERA_SDK_URL` for face/photo capture (`Camera2`, `DeviceCameraController`, `OxsaiPhotoUpload`).
- **WhatsApp proxy** — `WhatsappJob`, `SendWhatsappMessageJob`, `whatsapp:proxy-health-check`.
- **DigitalOcean Spaces** — primary object storage (PDF reports, photos). Credentials in `.env` under `DO_SPACE_*`. AWS S3 keys are blank — Spaces is used through the S3 driver.
- **MQTT** ([routes/mqtt_mytime.php](routes/mqtt_mytime.php)) for realtime device telemetry.

### PDF / report pipeline
PDFs are generated through `barryvdh/laravel-dompdf` + `webklex/laravel-pdfmerger`. The flow is: scheduler/HTTP request → dispatches `GenerateAttendanceReport*` / `ReportsPDFGeneratorJob` → renders Blade in [resources/views/](resources/views/) → merges with `ReportsPDFMergeJob` → uploads to DO Spaces → bundles with `ZipReportBatchJob`. Logs land in `storage/logs/`; old report files are cleaned by `task:files-delete-old-log-files` daily at 23:30.

### Billing
[app/Services/Billing/](app/Services/Billing/) (`InvoiceService`, `InvoicePdfService`, `InvoiceMailer`, `InvoiceNumberService`) is the canonical entry point — controllers should not reimplement invoice numbering or PDF rendering. Sequence is per-company via the `invoice_sequences` table.

## Tenant migration / sync (READ BEFORE TOUCHING THESE FILES)

[VALIANT_SYNC_RUNBOOK.md](VALIANT_SYNC_RUNBOOK.md) is a self-contained brief for the multi-tenant data-migration tooling. Key points:

- Three SQL files prefixed `ARCHIVED_DO_NOT_RUN_*` are destructive — they would now delete live tenant data. **Never execute them.** Treat as templates only.
- The dump generator ([generate_valiant_dump.ps1](generate_valiant_dump.ps1) → [valiant_pacific_restore.sql](valiant_pacific_restore.sql)) is idempotent: single transaction, `ON CONFLICT DO NOTHING` on every INSERT, deterministic ID remap (`source_id + 1_000_000_000` on collision). Re-running is safe.
- Both `mytime2cloud` and `mytime2cloud-v2` on `139.59.69.241` are LIVE production. Reads are fine; never run unreviewed `DELETE`/`UPDATE`/`TRUNCATE`. If a restore fails on orphan or unique-index conflicts, **stop and report** — do not auto-clean.
- `compare_valiant_counts.ps1` is the read-only diff between source and target tenants.

## Conventions specific to this codebase

- `routes/api.php` is an include-only file. Don't add routes there — add them to or create a topical file under `routes/` and include it.
- Controllers commonly call Eloquent directly. If logic spans multiple models or has side effects (notifications, queue dispatches, file generation), put it in `app/Services/` or a dedicated Job.
- Use the `Notification::create([...])` snippet pattern from [README.md](README.md) when emitting in-app notifications (fields: `data`, `action`, `model`, `user_id`, `company_id`, `redirect_url`).
- Helper functions live in [app/helper.php](app/helper.php), autoloaded via composer.
- The `WILD_CARD` env var (`LIKE` for pgsql, `ILIKE` would be the case-insensitive variant) is referenced by search code — keep it consistent with the active DB driver.
- Tests are sparse (only `Feature/EmployeeStoreFromDeviceTest.php` and `Feature/ExampleTest.php` plus an empty `Unit/`). The PHPUnit config does **not** swap the DB to sqlite/in-memory, so tests run against whatever `DB_CONNECTION` is configured — be careful before running them against a non-test DB.
