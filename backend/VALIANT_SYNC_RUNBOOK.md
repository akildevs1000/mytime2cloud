# Valiant tenant migration / sync runbook

This document is a self-contained brief so a fresh Claude Code session (or a teammate)
can pick up the multi-tenant Valiant migration work without prior context.

If you're starting a new Claude Code session, paste this whole file as your first
message, then state the task ("verify sync", "run incremental sync", "migrate to
new target X", etc.).

---

## 1. What this is

A multi-tenant Laravel 9 / PostgreSQL SaaS (`mytime2cloud`). Tenants are rows in
the `companies` table. We isolate one tenant's data (currently Valiant Pacific
LLC, `id = 49`) and migrate it from a source DB to a target DB **without
touching any other tenant's data**.

The original use case: Valiant existed only on a snapshot DB. We needed to
restore Valiant onto the live `mytime2cloud-v2` DB without affecting HYDERS PARK
(`id = 13`). After the initial migration, we now use the same tooling for
periodic incremental sync (live source → live v2 target).

---

## 2. Connection details (current as of 2026-05-05)

```
SOURCE — live tenant data
  host = 139.59.69.241   port = 5432
  db   = mytime2cloud
  user = postgres        password = tZqsls7URjNC0aF

TARGET — live v2 destination
  host = 139.59.69.241   port = 5432
  db   = mytime2cloud-v2
  user = francis         password = test123

LOCAL SNAPSHOTS (sometimes useful as a safer source)
  host = 127.0.0.1       port = 5432  user = postgres  password = tZqsls7URjNC0aF
  databases: mytime2cloud-local, mytime2cloud-local-v2,
             mytime2cloud-backup-05-may, mytime2cloud-old-database
```

PostgreSQL client used: `C:\Program Files\PostgreSQL\15\bin\psql.exe`
(server is PG 14.22 on Linux).

**The user has said both `mytime2cloud` and `mytime2cloud-v2` on 139.59.69.241
are LIVE. Treat both as production. No DELETE/UPDATE on either without explicit
per-step approval.** Reads are fine. The dump tool only does INSERTs on target
(transaction-wrapped, ON CONFLICT DO NOTHING).

---

## 3. File map (everything lives in `cloud-app/backend/`)

| File | Role | Editable? |
|---|---|---|
| [valiant_dump_helper.sql](valiant_dump_helper.sql) | Installs the `_valiant_dump()` PL/pgSQL function on the source DB. The function emits SQL `INSERT` statements with deterministic ID + FK remapping. | Rarely — only if remap semantics change |
| [generate_valiant_dump.ps1](generate_valiant_dump.ps1) | Driver. Loads schema from both DBs, computes column intersection, detects PK collisions, emits a dump file. | Yes — edit the source/target connection block at top + table list |
| [valiant_pacific_restore.sql](valiant_pacific_restore.sql) | The generated dump. 19 000+ INSERT statements wrapped in a single `BEGIN…COMMIT`. Recreated by the generator each run. | No — regenerate instead |
| [ARCHIVED_DO_NOT_RUN_valiant_cleanup_v2.sql](ARCHIVED_DO_NOT_RUN_valiant_cleanup_v2.sql) | ☠️ ARCHIVED. Was used once to remove orphan `company_id = 49` rows BEFORE Valiant became a real tenant. Running it now would delete all live Valiant data. | NEVER RUN. Template only. |
| [ARCHIVED_DO_NOT_RUN_company73_cleanup.sql](ARCHIVED_DO_NOT_RUN_company73_cleanup.sql) | ☠️ ARCHIVED. Was used once to remove phantom `company_id = 73` orphans. No co=73 rows exist anymore. | NEVER RUN. Template only. |
| [ARCHIVED_DO_NOT_RUN_ghost_cleanup.sql](ARCHIVED_DO_NOT_RUN_ghost_cleanup.sql) | ☠️ ARCHIVED. One-shot fix for 4 duplicate rows from an old non-deterministic remap algorithm. | NEVER RUN. Reference only. |
| [compare_valiant_counts.ps1](compare_valiant_counts.ps1) | Diffs every dumped table source vs target. Prints mismatches. Read-only. | Yes — edit conn block at top |

Throwaway/debug files (safe to delete):
`audit_live.sql`, `verify_user1004.sql`, `inspect_missing.sql`, `inspect_collisions.sql`,
`check_co73.sql`, `check_uniq.sql`, `verify_may4.sql`, `fetch_missing.sql`,
`sync_check.sql`, `smoketest.sql`.

---

## 4. Safety invariants (READ THIS BEFORE TOUCHING ANYTHING)

These are the safety properties that let us run on production without fear.
Anyone touching the tooling MUST preserve these.

1. **Single transaction.** The generated dump file starts with `BEGIN;` and ends
   with `COMMIT;`. Run it with `psql -v ON_ERROR_STOP=1`. Any error rolls back
   the whole thing — target DB unchanged.

2. **`ON CONFLICT DO NOTHING` on every INSERT.** The dump never overwrites an
   existing row. So:
   - Same row already in target ➜ skipped (idempotent).
   - Different row at the same PK ➜ skipped (other tenants safe).
   - Re-running the dump = no duplicates, no changes.

3. **Deterministic ID remap.** When source row's `id` collides with a row owned
   by another tenant on target, the new id is `source_id + 1,000,000,000`.
   Always the same value for the same source row, no matter when you run.
   This is what makes incremental sync work cleanly.

4. **No DELETE / UPDATE / TRUNCATE in the dump file** — ever. Destructive ops
   live only in cleanup scripts that are run separately and need explicit
   approval.

### 🛑 Cleanup scripts: ALL THREE ARE ARCHIVED. DO NOT RUN AGAIN.

The cleanup scripts in this folder were correct **only for the moment they
were originally written**. The world has moved on — running them now would
**destroy live data**. They are kept only as reference / templates.

| File | Originally did | If you run it TODAY |
|---|---|---|
| `valiant_cleanup_v2.sql` | Deleted orphan rows tagged `company_id = 49` *before* Valiant existed as a real tenant | ☠️ **Destroys all live Valiant data** (companies row + 14 740 attendance_logs + 11 employees + 4 015 attendances + everything else for co=49). Catastrophic. |
| `company73_cleanup.sql` | Deleted orphan rows tagged `company_id = 73` from a phantom tenant that never existed | ☠️ Was a no-op as of 2026-05-05 (no co=73 rows remained). If new co=73 rows appear in the future, deleting them blindly might destroy legitimate data. |
| `ghost_cleanup.sql` | One-shot fix for 4 duplicate rows from an early non-deterministic remap algorithm | ☠️ Will silently DELETE specific row IDs if they happen to exist again. Never re-run. |

**Status of the tenants these scripts referenced:**

- **HYDERS PARK (id=13)** — real tenant. Never touched by any of our work. Don't touch.
- **Valiant Pacific LLC (id=49)** — real tenant now (was orphaned before). All
  data restored. **Treat exactly like HYDERS PARK from now on: read-only unless
  the user explicitly requests otherwise.**
- **company_id = 73** — no longer exists. Was phantom orphan data from a partial
  setup. Cleaned up, gone.

### Rules a future Claude session MUST follow

1. **Never run any of the three archived cleanup scripts.** Period. The user
   said "do not run them again" — no approval at all unlocks them.

2. **Never invent new `DELETE` statements** on `company_id = 13`, `49`, or `73`.
   These are settled.

3. If a future tenant migration (e.g. tenant id=85) needs orphan cleanup
   because the new target DB has phantom rows tagged `company_id = 85`, that
   is a NEW situation. Do not reuse `valiant_cleanup_v2.sql`. Instead:
     - Write a NEW cleanup script (template: copy `valiant_cleanup_v2.sql`,
       change every `49` to the new id, save as `tenant<N>_cleanup.sql`).
     - Show it to the user and explain what it deletes.
     - Wait for explicit approval naming the new file.
     - Run it inside `BEGIN…COMMIT` with a HYDERS-PARK / Valiant tenant-counts
       check at the end so we can verify both real tenants are untouched.

4. **If a restore fails because of orphan rows or unique-index conflicts,
   report and stop.** Do not auto-clean. Show the user what's blocking and
   wait.

5. **The dump generator and restore are still safe to run** — they only do
   `INSERT … ON CONFLICT DO NOTHING` inside a transaction and never touch any
   tenant other than the one specified in `$companyId`. That's the design
   precisely so day-to-day sync doesn't need cleanup at all.

---

## 5. Standard workflows

### A) Read-only sync verification ("are all the logs synced?")

Use this when the user reports "I think this row didn't sync" or asks for a
general check.

```powershell
# Identify a specific row in source
$psql = "C:\Program Files\PostgreSQL\15\bin\psql.exe"
$env:PGPASSWORD = "tZqsls7URjNC0aF"
& $psql -h 139.59.69.241 -U postgres -d "mytime2cloud" -c @"
SELECT id, "UserID", "LogTime", company_id
FROM attendance_logs
WHERE "UserID" = '<user>' AND "LogTime" = '<timestamp>';
"@

# Count comparison across the whole tenant
# (use compare_valiant_counts.ps1 — already wired for both DBs)
powershell -ExecutionPolicy Bypass -File compare_valiant_counts.ps1
```

To enumerate exactly which IDs are missing in target (accounting for remap):

```powershell
# Pull source IDs
$env:PGPASSWORD = "tZqsls7URjNC0aF"
$srcIds = & $psql -h 139.59.69.241 -U postgres -d "mytime2cloud" -t -A -c `
  "SELECT id FROM attendance_logs WHERE company_id=49 ORDER BY id"

# Pull target IDs
$env:PGPASSWORD = "test123"
$tgtIds = & $psql -h 139.59.69.241 -U francis -d "mytime2cloud-v2" -t -A -c `
  "SELECT id FROM attendance_logs WHERE company_id=49"

$tgtSet = [System.Collections.Generic.HashSet[long]]::new()
$tgtIds -split "`n" | ? { $_ } | % { $null = $tgtSet.Add([long]$_) }

$REMAP = 1000000000
$missing = @()
$srcIds -split "`n" | ? { $_ } | % {
    $sid = [long]$_
    if (-not $tgtSet.Contains($sid) -and -not $tgtSet.Contains($sid + $REMAP)) {
        $missing += $sid
    }
}
Write-Host "Missing IDs: $($missing.Count)"
```

### B) Incremental sync (catch up new rows)

When source has gained new rows since the last restore. Idempotent — re-running
without new source rows is a zero-op.

```powershell
# 1. Install helper on source (it's a CREATE OR REPLACE FUNCTION; only does SELECT)
$env:PGPASSWORD = "tZqsls7URjNC0aF"
& $psql -h 139.59.69.241 -U postgres -d "mytime2cloud" `
        -f "valiant_dump_helper.sql"

# 2. Confirm generate_valiant_dump.ps1's connection block points at the right
#    source and target. Edit the script's $srcHost/$srcDb/$tgtHost/$tgtDb if
#    needed.

# 3. Generate dump (deletes old file, writes new one)
Remove-Item -Force valiant_pacific_restore.sql -ErrorAction SilentlyContinue
powershell -ExecutionPolicy Bypass -File generate_valiant_dump.ps1

# 4. Run on target
$env:PGPASSWORD = "test123"
& $psql -h 139.59.69.241 -U francis -d "mytime2cloud-v2" `
        -v ON_ERROR_STOP=1 -f "valiant_pacific_restore.sql"

# 5. Drop helper from source (clean up)
$env:PGPASSWORD = "tZqsls7URjNC0aF"
& $psql -h 139.59.69.241 -U postgres -d "mytime2cloud" `
        -c "DROP FUNCTION IF EXISTS public._valiant_dump(text,text,text[],jsonb,jsonb);"

# 6. Verify
powershell -ExecutionPolicy Bypass -File compare_valiant_counts.ps1
```

### C) Migrate a NEW tenant (different `companyId`) to a target DB

This is the workflow for onboarding a brand-new tenant — say tenant `id = 85`.
**Do NOT use the existing co=49 or co=73 cleanup scripts.** They are archived
and apply only to the situations they were originally written for.

1. **Take a backup of the target.** Mandatory.
   ```powershell
   $stamp = Get-Date -Format "yyyyMMdd_HHmm"
   & "C:\Program Files\PostgreSQL\15\bin\pg_dump.exe" `
       -h <target-host> -U <target-user> -d <target-db> `
       -F c -Z 6 -f "d:\backups\target_before_tenant<N>_$stamp.dump"
   ```

2. **Audit the target** (read-only). Use `audit_live.sql` as a template, with
   the new `<N>` substituted for `49`. Report to the user:
   - Existing real companies (id+name).
   - Whether `companies.id = <N>` already exists (a real tenant or an orphan?).
   - Rows tagged `company_id = <N>` that have no parent companies row.
   - Any other orphan tenants whose IDs collide with source data.

3. **If orphans found, STOP and report.** Do not auto-fix. The user decides:
   - Are these orphans real data or junk?
   - Is the right move to delete them, leave them, or migrate them under a
     different id?
   - Only after explicit instructions, write a NEW cleanup script:
     `tenant<N>_cleanup.sql`, modelled on the archived templates but with the
     correct ids. Run only after the user explicitly approves the new file by
     name.

4. **Edit the generator** ([generate_valiant_dump.ps1](generate_valiant_dump.ps1)):
   - `$companyId = <N>`
   - Update `$srcDb` / `$tgtDb` / connection details if different
   - Review the table list — is the new tenant using any tables Valiant didn't?
     (Probably not, but check; e.g. if they use `parkings` or `vehicles`.)

5. **Generate dump and restore** (workflow B steps 1, 3, 4, 5, 6).

6. **Verify** with [compare_valiant_counts.ps1](compare_valiant_counts.ps1)
   (also pointed at the new tenant + new connections — edit the conn block).

### D) [reserved]

(Workflow D was previously "different tenant" — that's now folded into C above
since the cleanup discussion is the same problem.)

### E) Rollback

If a restore went wrong (it shouldn't — transaction guarantees that — but
hypothetically):

```powershell
$env:PGPASSWORD = "<target-password>"
& "C:\Program Files\PostgreSQL\15\bin\pg_restore.exe" `
    -h <host> -U <user> -d <db> `
    --clean --if-exists `
    "d:\backups\target_before_valiant_<timestamp>.dump"
```

---

## 6. Architecture cheatsheet

### Why a custom generator instead of `pg_dump`?

`pg_dump` does the whole DB or whole tables — no row-level filter, no
cross-tenant ID remap, no column-intersection between source and target
schemas. We needed all three.

### How the remap works

- Generator queries target for `id`s owned by *other* tenants
  (`company_id <> $companyId OR company_id IS NULL`).
- Source `id`s in that set are "real collisions".
- Each colliding source `id` gets `new_id = source_id + 1,000,000,000`.
- Same source row → same new id, every run. No duplicates on re-runs.
- FK columns in dependent tables are rewritten via the same map (`fkRefs`
  in the generator declares which columns to cascade).

### What the helper SQL function does

`public._valiant_dump(table, where, cols, id_remap_jsonb, fk_remap_jsonb)`
returns text. Each row is a complete `INSERT INTO … VALUES (…) ON CONFLICT
DO NOTHING;` statement. The function:

1. Reads `information_schema.columns` for the requested column subset.
2. For each column, builds a `quote_nullable(col::text)` expression that
   safely SQL-quotes any value (NULLs become `NULL`, strings get escaped, etc.).
3. Wraps the `id` column in a `COALESCE($1->>id::text, id::text)` lookup
   so remapped IDs are substituted at SELECT time.
4. Same for any column listed in `fk_remap`.
5. Executes the dynamically-built SELECT with `RETURN QUERY EXECUTE … USING`.

The helper is installed temporarily on the SOURCE DB and dropped after the
generator finishes. It's read-only in effect (only does SELECT internally),
but it's still a schema change on the source — that's why we drop it.

### Schema column intersection

Source and target schemas drift over time (target is usually newer with
extra tables/columns). The generator:

- Loads `{table → [columns]}` from both DBs.
- For each table, emits INSERTs using only the **intersection** of columns.
- Drops columns that exist on source but not target (e.g. `companies.currency`).
- Drops entire tables that exist on source but not target (e.g. `invoices`,
  `payments` — none of which had Valiant rows anyway).
- Target-only columns become NULL on insert (their default).

This makes the dump robust against schema migrations on either side.

---

## 7. Common gotchas

| Symptom | Cause | Fix |
|---|---|---|
| Restore aborts with "column X does not exist" | Source has a column target doesn't | Generator handles this (column intersection). If it slipped through, regenerate. |
| INSERT count drops mysteriously (e.g. dump has 19k INSERTs but only 4 land) | Stale file handle on the dump file from a prior `Read` | `Remove-Item -Force valiant_pacific_restore.sql` then regenerate. Don't open it in another tool while writing. |
| Restore aborts with FK violation | Target has FK constraints source lacks. Most likely a child row referencing a remapped parent that didn't get cascaded. | Add an entry to `$fkRefs` in the generator for that child→parent relationship. |
| Restore aborts with unique-index violation that isn't the PK | A unique index like `attendance_logs_uniq(DeviceID, LogTime, UserID)` already has the same triplet from another tenant (or orphan tenant). | Audit target for orphan tenants (`audit_live.sql`). Clean the orphan rows (template: `company73_cleanup.sql`). |
| Mid-restore "duplicate key value violates unique constraint" on a remapped row | Two consecutive runs with non-deterministic remap (old algorithm). | Should not happen with current algorithm. If it does, run `ghost_cleanup.sql`-style cleanup for the duplicates and confirm `$REMAP_OFFSET` hasn't changed. |
| PowerShell strips quotes when passing JSON to psql -c | Windows command-line quoting | Always pass complex SQL via `-f file.sql`, not `-c "…"` with embedded quotes. |
| `2>&1` causes PowerShell to display NOTICE messages as red errors | PowerShell-isms | Ignore — check the actual exit code (`$LASTEXITCODE`) and the trailing line (look for `COMMIT`). |
| Restore is "running" indefinitely on remote target | Network latency × 19k INSERTs | Run in background (`run_in_background: true` for the Bash tool) and poll. Typical runtime is ~30–90s on a decent link. |

---

## 8. Pre-flight safety checks (always run before any write to live)

```powershell
$env:PGPASSWORD = "test123"
& "C:\Program Files\PostgreSQL\15\bin\psql.exe" `
    -h 139.59.69.241 -U francis -d "mytime2cloud-v2" -c "
    SELECT 'companies' AS k, COUNT(*) FROM companies
    UNION ALL SELECT 'HYDERS_users',           COUNT(*) FROM users           WHERE company_id = 13
    UNION ALL SELECT 'HYDERS_employees',       COUNT(*) FROM employees       WHERE company_id = 13
    UNION ALL SELECT 'HYDERS_attendance_logs', COUNT(*) FROM attendance_logs WHERE company_id = 13
    UNION ALL SELECT 'Valiant_employees',      COUNT(*) FROM employees       WHERE company_id = 49
    UNION ALL SELECT 'Valiant_attendance_logs',COUNT(*) FROM attendance_logs WHERE company_id = 49
    UNION ALL SELECT 'orphan_co73_logs',       COUNT(*) FROM attendance_logs WHERE company_id = 73;
"
```

Save this output. After the restore, run it again. HYDERS PARK numbers must
be identical. Valiant numbers should grow (or stay the same if no new source
rows). orphan_co73 should be 0.

---

## 9. Last-known-good state (2026-05-05)

After the last sync (2026-05-05 ~12:00):

```
mytime2cloud (live source):
  companies:       1+ (Valiant + others)
  attendance_logs co=49: 14 740

mytime2cloud-v2 (live target):
  companies:       2 (HYDERS PARK id=13, Valiant id=49)
  attendance_logs co=49: 14 740 (matches source)
  attendance_logs co=13: 1 479 (HYDERS PARK, untouched)
  orphan co=73: 0
```

A clean re-run today should produce zero new INSERTs (idempotent) unless source
has gained new rows since the last sync.

---

## 10. Bootstrapping a fresh Claude Code session

Open Claude Code in `d:\projects\mytime2cloud\cloud-app\backend\` and say:

> Read `VALIANT_SYNC_RUNBOOK.md` and confirm you understand the setup.
> Then [verify sync / run incremental sync / migrate to new target X / etc.].

Claude should:

1. Read this file end to end.
2. Acknowledge the safety invariants in section 4 (specifically the cleanup-script rules).
3. **Never run a cleanup script without an explicit per-run approval message
   that names the file**. "Do the migration" / "fix it" / a bare "yes" do NOT
   count as approval to delete data.
4. Ask before any DELETE/UPDATE on live, period.
5. Take a backup before any write to a new target.
6. Use the existing scripts; only modify them when a real schema change demands it.
7. Show counts before and after every write.
8. If a restore fails because of orphans / unique-index conflicts, **report the
   problem and stop** — do not auto-clean.

If Claude proposes anything that breaks the invariants in section 4 — stop them.
