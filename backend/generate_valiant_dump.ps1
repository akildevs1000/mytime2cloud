# Generates a SQL restore file for Valiant Pacific LLC (company_id=49) data
# that is FUTURE-READY:
#   * Filters to columns present in BOTH source and target schemas.
#   * Detects PK collisions against the target DB at generation time.
#   * Remaps colliding IDs to safe new values (target_max + offset).
#   * Cascades the remapping to FK columns in dependent tables.
#   * Skips remapping for tables in $skipRemap (currently: attendance_logs,
#     attendances) -- those keep their original IDs and ON CONFLICT DO NOTHING
#     silently drops the few colliders.
#   * Wraps everything in a single transaction so any failure rolls back.

$ErrorActionPreference = "Stop"
$psql = "C:\Program Files\PostgreSQL\15\bin\psql.exe"

# --- Source: live production DB where Valiant data lives ---
$srcHost = "139.59.69.241"; $srcPort = "5432"
$srcUser = "postgres";       $srcPassword = "tZqsls7URjNC0aF"
$srcDb   = "mytime2cloud"

# --- Target: live v2 DB where Valiant data is being restored ---
$tgtHost = "139.59.69.241"; $tgtPort = "5432"
$tgtUser = "francis";        $tgtPassword = "test123"
$tgtDb   = "mytime2cloud-v2"

$companyId = 49

$srcArgs = @("-h",$srcHost,"-p",$srcPort,"-U",$srcUser,"-d",$srcDb,"-t","-A","--no-psqlrc")
$tgtArgs = @("-h",$tgtHost,"-p",$tgtPort,"-U",$tgtUser,"-d",$tgtDb,"-t","-A","--no-psqlrc")

# psql picks up PGPASSWORD per call. Wrappers below set the right one.
function Invoke-Src { param($cmd) $env:PGPASSWORD = $srcPassword; & $psql @srcArgs @cmd }
function Invoke-Tgt { param($cmd) $env:PGPASSWORD = $tgtPassword; & $psql @tgtArgs @cmd }
$env:PGPASSWORD = $srcPassword  # initial default for source-side calls

$outFile     = "d:\projects\mytime2cloud\cloud-app\backend\valiant_pacific_restore.sql"
$masterSql   = "d:\projects\mytime2cloud\cloud-app\backend\_master_dump_calls.sql"
$masterOut   = "d:\projects\mytime2cloud\cloud-app\backend\_master_dump_out.txt"

# Tables for which we deliberately do NOT remap PKs. ON CONFLICT DO NOTHING
# silently keeps the target row when an ID collides. Empty by default --
# remapping is always preferred so that no source data is lost. Add a table
# here only if you have a specific reason to drop colliding rows.
$skipRemap = @{}

# FK metadata: child_table -> { fk_column -> parent_table }.
# When parent_table has remappings, we substitute the child's FK column.
# Add entries here as new tables / new FK relationships are introduced.
$fkRefs = @{
    "employee_government_holidays" = @{ "holiday_id" = "holidays" }
    "employee_report"              = @{ "report_id"  = "report_notifications" }
}

# ---------------------------------------------------------------------------
# Table list (same WHERE clauses as before).
# ---------------------------------------------------------------------------
$companyScopedTables = @(
    @{ table = "users";                          where = "id = 609 OR company_id = $companyId" }
    @{ table = "companies";                      where = "id = $companyId" }
    @{ table = "company_branches";               where = "company_id = $companyId" }
    @{ table = "company_contacts";               where = "company_id = $companyId" }
    @{ table = "company_modules";                where = "company_id = $companyId" }
    @{ table = "branches";                       where = "company_id = $companyId" }
    @{ table = "departments";                    where = "company_id = $companyId" }
    @{ table = "sub_departments";                where = "company_id = $companyId" }
    @{ table = "designations";                   where = "company_id::text = '$companyId'" }
    @{ table = "roles";                          where = "company_id = $companyId" }
    @{ table = "assign_modules";                 where = "company_id = $companyId" }
    @{ table = "assign_permissions";             where = "company_id = $companyId" }
    @{ table = "themes";                         where = "company_id = $companyId" }
    @{ table = "salary_types";                   where = "company_id = $companyId" }
    @{ table = "leave_types";                    where = "company_id = $companyId" }
    @{ table = "leave_groups";                   where = "company_id = $companyId" }
    @{ table = "policies";                       where = "company_id = $companyId" }
    @{ table = "shifts";                         where = "company_id = $companyId" }
    @{ table = "auto_shifts";                    where = "company_id = $companyId" }
    @{ table = "time_tables";                    where = "company_id = $companyId" }
    @{ table = "timezones";                      where = "company_id = $companyId" }
    @{ table = "holidays";                       where = "company_id = $companyId" }
    @{ table = "purposes";                       where = "company_id = $companyId" }
    @{ table = "announcements_categories";       where = "company_id = $companyId" }
    @{ table = "announcements";                  where = "company_id = $companyId" }
    @{ table = "host_companies";                 where = "company_id = $companyId" }
    @{ table = "tanents";                        where = "company_id = $companyId" }
    @{ table = "floors";                         where = "company_id = $companyId" }
    @{ table = "rooms";                          where = "company_id = $companyId" }
    @{ table = "room_categories";                where = "company_id = $companyId" }
    @{ table = "zones";                          where = "company_id = $companyId" }
    @{ table = "members";                        where = "company_id = $companyId" }
    @{ table = "mail_contents";                  where = "company_id = $companyId" }
    @{ table = "payroll_formulas";               where = "company_id = $companyId" }
    @{ table = "payroll_settings";               where = "company_id = $companyId" }
    @{ table = "report_notifications";           where = "company_id = $companyId" }
    @{ table = "report_notifications_manager";   where = "company_id = $companyId" }
    @{ table = "report_notification_logs";       where = "company_id = $companyId" }
    @{ table = "device_notifications";           where = "company_id = $companyId" }
    @{ table = "device_notifications_managers";  where = "company_id = $companyId" }
    @{ table = "device_notifications_logs";      where = "company_id = $companyId" }
    @{ table = "a_i_triggers";                   where = "company_id = $companyId" }
    @{ table = "ai_feeds";                       where = "company_id = $companyId" }
    @{ table = "whatsapp_clients";               where = "company_id = $companyId" }
    @{ table = "whatsapp_notifications_logs";    where = "company_id = $companyId" }
    @{ table = "devices";                        where = "company_id = $companyId" }
    @{ table = "devices_active_settings";        where = "company_id = $companyId" }
    @{ table = "devices_active_weekly_settings"; where = "company_id = $companyId" }
    @{ table = "device_sensor_logs";             where = "company_id = $companyId" }
    @{ table = "real_time_locations";            where = "company_id = $companyId" }
    @{ table = "user_locations";                 where = "company_id = $companyId" }
    @{ table = "coordindates";                   where = "company_id = $companyId" }
    @{ table = "employees";                      where = "company_id = $companyId" }
    @{ table = "personal_infos";                 where = "company_id = $companyId" }
    @{ table = "bank_infos";                     where = "company_id = $companyId" }
    @{ table = "document_infos";                 where = "company_id = $companyId" }
    @{ table = "documents";                      where = "company_id = $companyId" }
    @{ table = "emirates_infos";                 where = "company_id = $companyId" }
    @{ table = "passports";                      where = "company_id = $companyId" }
    @{ table = "visas";                          where = "company_id = $companyId" }
    @{ table = "trade_licenses";                 where = "company_id = $companyId" }
    @{ table = "qualifications";                 where = "company_id = $companyId" }
    @{ table = "experiences";                    where = "company_id = $companyId" }
    @{ table = "verifications";                  where = "company_id = $companyId" }
    @{ table = "assigned_department_employees";  where = "company_id = $companyId" }
    @{ table = "schedule_employees";             where = "company_id = $companyId" }
    @{ table = "timezone_employees";             where = "company_id = $companyId" }
    @{ table = "employee_timezone_mappings";     where = "company_id = $companyId" }
    @{ table = "salaries";                       where = "company_id = $companyId" }
    @{ table = "allowances";                     where = "company_id = $companyId" }
    @{ table = "deductions";                     where = "company_id = $companyId" }
    @{ table = "commissions";                    where = "company_id = $companyId" }
    @{ table = "overtimes";                      where = "company_id = $companyId" }
    @{ table = "payrolls";                       where = "company_id = $companyId" }
    @{ table = "payslips";                       where = "company_id::text = '$companyId'" }
    @{ table = "leaves";                         where = "company_id = $companyId" }
    @{ table = "employee_leaves";                where = "company_id = $companyId" }
    @{ table = "employee_leave_documents";       where = "company_id = $companyId" }
    @{ table = "leave_count";                    where = "company_id = $companyId" }
    @{ table = "attendances";                    where = "company_id = $companyId" }
    @{ table = "attendance_logs";                where = "company_id = $companyId" }
    @{ table = "clockings";                      where = "company_id = $companyId" }
    @{ table = "rosters";                        where = "company_id = $companyId" }
    @{ table = "change_requests";                where = "company_id = $companyId" }
    @{ table = "visitors";                       where = "company_id::text = '$companyId'" }
    @{ table = "visitor_logs";                   where = "company_id = $companyId" }
    @{ table = "visitor_attendances";            where = "company_id = $companyId" }
    @{ table = "activities";                     where = "company_id = $companyId" }
    @{ table = "alarm_logs";                     where = "company_id = $companyId" }
    @{ table = "notifications";                  where = "company_id = $companyId" }
    @{ table = "invoices";                       where = "company_id = $companyId" }
    @{ table = "payments";                       where = "company_id = $companyId" }
)

$linkedTables = @(
    @{ table = "branch_contacts";          where = "branch_id IN (SELECT id FROM branches WHERE company_id = $companyId)" }
    @{ table = "user_branches";            where = "branch_id IN (SELECT id FROM branches WHERE company_id = $companyId) OR user_id IN (SELECT id FROM users WHERE company_id = $companyId)" }
    @{ table = "user_departments";         where = "department_id IN (SELECT id FROM departments WHERE company_id = $companyId) OR user_id IN (SELECT id FROM users WHERE company_id = $companyId)" }
    @{ table = "announcement_department";  where = "announcement_id IN (SELECT id FROM announcements WHERE company_id = $companyId)" }
    @{ table = "announcement_employee";    where = "announcement_id IN (SELECT id FROM announcements WHERE company_id = $companyId) OR employee_id IN (SELECT id FROM employees WHERE company_id = $companyId)" }
    @{ table = "assign_departments";       where = "department_id IN (SELECT id FROM departments WHERE company_id = $companyId) OR assigned_department_employee_id IN (SELECT id FROM assigned_department_employees WHERE company_id = $companyId)" }
    @{ table = "assign_employees";         where = "employee_id IN (SELECT id FROM employees WHERE company_id = $companyId) OR assigned_department_employee_id IN (SELECT id FROM assigned_department_employees WHERE company_id = $companyId)" }
    @{ table = "finger_prints";            where = "employee_id::text IN (SELECT id::text FROM employees WHERE company_id = $companyId)" }
    @{ table = "palms";                    where = "employee_id::text IN (SELECT id::text FROM employees WHERE company_id = $companyId)" }
    @{ table = "employee_leave_timelines"; where = "employee_leave_id IN (SELECT id FROM employee_leaves WHERE company_id = $companyId)" }
    @{ table = "employee_report";          where = "employee_id IN (SELECT id FROM employees WHERE company_id = $companyId)" }
    @{ table = "zone_devices";             where = "zone_id IN (SELECT id FROM zones WHERE company_id = $companyId) OR device_id IN (SELECT id FROM devices WHERE company_id = $companyId)" }
    @{ table = "access_control_time_slots";where = "device_id::text IN (SELECT id::text FROM devices WHERE company_id = $companyId)" }
    @{ table = "reasons";                  where = "branch_id IN (SELECT id FROM branches WHERE company_id = $companyId) OR user_id::text IN (SELECT id::text FROM users WHERE company_id = $companyId)" }
)

$allEntries = @($companyScopedTables) + @($linkedTables)

# ---------------------------------------------------------------------------
# Pass 1: load schema, detect collisions, compute id remappings per table.
# ---------------------------------------------------------------------------
Write-Host "Loading schema column lists..." -ForegroundColor Cyan

function Get-SchemaMap($connArgs, $password) {
    $env:PGPASSWORD = $password
    $rows = & $psql @connArgs -F "|" -c "SELECT table_name||'|'||column_name FROM information_schema.columns WHERE table_schema='public' ORDER BY table_name, ordinal_position;"
    $map = @{}
    foreach ($line in ($rows -split "`n")) {
        if ($line -match "^([^|]+)\|(.+)$") {
            $t = $matches[1].Trim()
            $c = $matches[2].Trim()
            if (-not $map.ContainsKey($t)) { $map[$t] = New-Object System.Collections.Generic.List[string] }
            $map[$t].Add($c)
        }
    }
    return $map
}

$srcSchema = Get-SchemaMap $srcArgs $srcPassword
$tgtSchema = Get-SchemaMap $tgtArgs $tgtPassword
Write-Host "Source tables: $($srcSchema.Count), Target tables: $($tgtSchema.Count)" -ForegroundColor DarkGray

Write-Host "Detecting PK collisions..." -ForegroundColor Cyan
# A "real" collision is when a source row's id is taken on target by a row that
# does NOT belong to the same tenant (company_id <> 49). Rows on target that
# DO belong to Valiant came from a prior partial restore -- those are not
# collisions, ON CONFLICT DO NOTHING handles them and the dump stays idempotent.
#
# Remapping is DETERMINISTIC: new_id = source_id + REMAP_OFFSET. The offset is
# constant per table (same value forever), so re-running the dump always
# produces the same new ids -- no duplicates if you run it twice.
$REMAP_OFFSET = 1000000000      # 1 billion: safely above any current ID
$remappings = @{}

foreach ($entry in $allEntries) {
    $t = $entry.table
    if (-not $srcSchema.ContainsKey($t) -or -not $tgtSchema.ContainsKey($t)) { continue }
    if (-not ($srcSchema[$t] -contains "id")) { continue }      # only tables with 'id' PK
    if ($skipRemap.ContainsKey($t)) { continue }                # honor user-specified skips

    # Source IDs that this dump will emit
    $env:PGPASSWORD = $srcPassword
    $srcIds = (& $psql @srcArgs -c "SELECT id::text FROM `"$t`" WHERE $($entry.where) ORDER BY id" 2>$null) -split "`n" | Where-Object { $_ -ne "" }
    if ($srcIds.Count -eq 0) { continue }

    # Build the "foreign" filter: target rows NOT owned by company 49.
    # The 'companies' table is special-cased: the tenant key is the row's own id.
    # If the table has a company_id column, exclude rows where company_id matches.
    # Otherwise treat any overlap as foreign (conservative).
    $hasCompanyId = $tgtSchema[$t] -contains "company_id"
    $foreignFilter =
        if ($t -eq "companies") { "id <> $companyId" }
        elseif ($hasCompanyId)  { "company_id::text <> '$companyId' OR company_id IS NULL" }
        else                    { "TRUE" }

    $env:PGPASSWORD = $tgtPassword
    $tgtIds = [System.Collections.Generic.HashSet[string]]::new(
        [string[]]@((& $psql @tgtArgs -c "SELECT id::text FROM `"$t`" WHERE $foreignFilter" 2>$null) -split "`n" | Where-Object { $_ -ne "" })
    )

    # Find collisions (source ids that are taken on target by some other tenant)
    $collisions = @($srcIds | Where-Object { $tgtIds.Contains($_) })
    if ($collisions.Count -eq 0) { continue }

    # Deterministic remap: new_id = source_id + REMAP_OFFSET. Same source row
    # always maps to the same new id, so re-running the dump never produces
    # duplicates. Verify the new ids don't collide with another tenant; if so
    # bail loudly (operator should bump REMAP_OFFSET to a safer value).
    $map = @{}
    foreach ($oldId in $collisions) {
        $map[$oldId] = ([long]$oldId + $REMAP_OFFSET).ToString()
    }

    $newIdsList = ($map.Values | ForEach-Object { "'" + $_ + "'" }) -join ","
    if ($newIdsList) {
        $conflictCheckSql = "SELECT COUNT(*) FROM `"$t`" WHERE id::text IN ($newIdsList) AND ($foreignFilter)"
        $env:PGPASSWORD = $tgtPassword
        $stillCollides = (& $psql @tgtArgs -c $conflictCheckSql 2>$null).Trim()
        if ([int]$stillCollides -gt 0) {
            throw "Remapped ids for table '$t' still collide with another tenant. Bump REMAP_OFFSET in the script."
        }
    }

    $remappings[$t] = $map
    Write-Host ("  {0,-32} {1} collision(s) -> deterministic offset +{2:N0}" -f $t, $collisions.Count, $REMAP_OFFSET) -ForegroundColor Yellow
}

if ($remappings.Count -eq 0) {
    Write-Host "  (no collisions found)" -ForegroundColor Green
}

# ---------------------------------------------------------------------------
# Helpers for JSONB literal construction.
# ---------------------------------------------------------------------------
function ConvertTo-JsonbLiteral($map) {
    if (-not $map -or $map.Count -eq 0) { return "'{}'::jsonb" }
    $json = ($map | ConvertTo-Json -Compress -Depth 8)
    $escaped = $json -replace "'", "''"
    return "'$escaped'::jsonb"
}

# ---------------------------------------------------------------------------
# Pass 2: build a single master SQL file with all dump calls, run psql once,
# capture output, then split by markers and write the final dump file.
# ---------------------------------------------------------------------------
Write-Host "Building master SQL..." -ForegroundColor Cyan

$sb = New-Object System.Text.StringBuilder
$null = $sb.AppendLine("-- Auto-generated: do not edit. Used to drive _valiant_dump.")
$null = $sb.AppendLine("\pset format unaligned")
$null = $sb.AppendLine("\pset tuples_only on")
$null = $sb.AppendLine()

$plannedTables = New-Object System.Collections.Generic.List[hashtable]

foreach ($entry in $allEntries) {
    $t = $entry.table
    $w = $entry.where

    if (-not $srcSchema.ContainsKey($t)) {
        $plannedTables.Add(@{ table = $t; status = "skipped: not in source"; where = $w; droppedCols = @() })
        continue
    }
    if (-not $tgtSchema.ContainsKey($t)) {
        $plannedTables.Add(@{ table = $t; status = "skipped: not in target"; where = $w; droppedCols = @() })
        continue
    }

    # Column intersection (preserves source order)
    $tgtSet = [System.Collections.Generic.HashSet[string]]::new($tgtSchema[$t])
    $cols = @($srcSchema[$t] | Where-Object { $tgtSet.Contains($_) })
    if ($cols.Count -eq 0) {
        $plannedTables.Add(@{ table = $t; status = "skipped: no shared columns"; where = $w; droppedCols = @() })
        continue
    }
    $droppedCols = @($srcSchema[$t] | Where-Object { -not $tgtSet.Contains($_) })

    # Build id_remap for THIS table
    $idRemap = $remappings[$t]
    $idRemapLit = ConvertTo-JsonbLiteral $idRemap

    # Build fk_remap for THIS table: for each FK column whose parent has remappings,
    # include parent's mapping under that column name.
    $fkMap = @{}
    if ($fkRefs.ContainsKey($t)) {
        foreach ($colName in $fkRefs[$t].Keys) {
            $parentTable = $fkRefs[$t][$colName]
            if ($remappings.ContainsKey($parentTable) -and ($cols -contains $colName)) {
                $fkMap[$colName] = $remappings[$parentTable]
            }
        }
    }
    $fkRemapLit = ConvertTo-JsonbLiteral $fkMap

    # Quote the column array for SQL
    $colArrSql = "ARRAY[" + (($cols | ForEach-Object { "'" + ($_ -replace "'", "''") + "'" }) -join ",") + "]::text[]"
    $whereSql = "'" + ($w -replace "'", "''") + "'"
    $tableSql = "'" + ($t -replace "'", "''") + "'"

    $null = $sb.AppendLine("SELECT '###BEGIN_TABLE### $t';")
    $null = $sb.AppendLine("SELECT public._valiant_dump($tableSql, $whereSql, $colArrSql, $idRemapLit, $fkRemapLit);")
    $null = $sb.AppendLine("SELECT '###END_TABLE### $t';")
    $null = $sb.AppendLine()

    $plannedTables.Add(@{ table = $t; status = "ok"; where = $w; cols = $cols; droppedCols = $droppedCols; idRemap = $idRemap; fkMap = $fkMap })
}

Set-Content -Path $masterSql -Value $sb.ToString() -Encoding UTF8

# ---------------------------------------------------------------------------
# Run master SQL, capture output.
# ---------------------------------------------------------------------------
Write-Host "Executing master dump (single psql run)..." -ForegroundColor Cyan
$env:PGPASSWORD = $srcPassword
& $psql @srcArgs -f $masterSql 2>&1 | Set-Content -Path $masterOut -Encoding UTF8 -Force
if ($LASTEXITCODE -ne 0) {
    throw "Master dump failed. See $masterOut for details."
}

# Parse output, splitting by markers.
$rawLines = Get-Content -Path $masterOut
$tableOutputs = @{}
$currentTable = $null
$currentLines = New-Object System.Collections.Generic.List[string]
foreach ($line in $rawLines) {
    if ($line -match "^###BEGIN_TABLE### (.+)$") {
        $currentTable = $matches[1].Trim()
        $currentLines = New-Object System.Collections.Generic.List[string]
    }
    elseif ($line -match "^###END_TABLE### (.+)$") {
        if ($currentTable) {
            $tableOutputs[$currentTable] = $currentLines
        }
        $currentTable = $null
    }
    elseif ($currentTable -and $line.Trim() -ne "") {
        $currentLines.Add($line)
    }
}

# ---------------------------------------------------------------------------
# Build the final dump SQL file with structured comments and grouping.
# ---------------------------------------------------------------------------
Write-Host "Writing final dump file..." -ForegroundColor Cyan

$header = @"
-- ============================================================================
-- Valiant Pacific LLC (company_id = $companyId) data restore.
-- Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
-- Source DB: $srcDb
-- Target DB schema reference: $tgtDb
--
-- This dump:
--   * Includes only columns present in both DBs.
--   * Uses INSERT ... ON CONFLICT DO NOTHING (target rows are never overwritten).
--   * Auto-remaps PKs that collided with target rows (cascades to FK columns).
--   * Skips remapping for: $($skipRemap.Keys -join ', ')
--     (those tables silently drop any colliding rows.)
--   * Wrapped in BEGIN ... COMMIT.
--
-- USAGE:
--   psql -h <host> -U <user> -d <db> -v ON_ERROR_STOP=1 -f valiant_pacific_restore.sql
-- ============================================================================

BEGIN;

SET CONSTRAINTS ALL DEFERRED;

"@

Set-Content -Path $outFile -Value $header -Encoding UTF8

if ($remappings.Count -gt 0) {
    Add-Content -Path $outFile -Value "-- ID remappings applied to avoid collisions with existing target rows:" -Encoding UTF8
    foreach ($t in $remappings.Keys | Sort-Object) {
        $entries = $remappings[$t]
        foreach ($oldId in $entries.Keys | Sort-Object { [long]$_ }) {
            Add-Content -Path $outFile -Value "--   $t.id  $oldId  ->  $($entries[$oldId])" -Encoding UTF8
        }
    }
    Add-Content -Path $outFile -Value "" -Encoding UTF8
}

function Add-Section([string]$title, [array]$entries) {
    Add-Content -Path $outFile -Value "" -Encoding UTF8
    Add-Content -Path $outFile -Value "-- ----------------------------------------------------------------------------" -Encoding UTF8
    Add-Content -Path $outFile -Value "-- $title" -Encoding UTF8
    Add-Content -Path $outFile -Value "-- ----------------------------------------------------------------------------" -Encoding UTF8

    foreach ($e in $entries) {
        $t = $e.table
        $plan = $plannedTables | Where-Object { $_.table -eq $t } | Select-Object -First 1
        if ($null -eq $plan) { continue }

        if ($plan.status -ne "ok") {
            Add-Content -Path $outFile -Value "-- ($($plan.status)) $t" -Encoding UTF8
            Write-Host ("  {0,-40} {1}" -f $t, $plan.status) -ForegroundColor DarkYellow
            continue
        }

        $rows = if ($tableOutputs.ContainsKey($t)) { $tableOutputs[$t] } else { @() }
        $count = $rows.Count
        $note = if ($plan.droppedCols.Count -gt 0) { "  (dropped: " + ($plan.droppedCols -join ", ") + ")" } else { "" }
        $remapNote = if ($plan.idRemap -and $plan.idRemap.Count -gt 0) { "  [REMAP: $($plan.idRemap.Count) id(s)]" } else { "" }

        Add-Content -Path $outFile -Value "" -Encoding UTF8
        Add-Content -Path $outFile -Value "-- Table: $t   ($count rows)$remapNote   WHERE $($plan.where)$note" -Encoding UTF8
        if ($count -gt 0) { Add-Content -Path $outFile -Value $rows -Encoding UTF8 }
        Write-Host ("  {0,-40} {1,6} rows{2}" -f $t, $count, $remapNote)
    }
}

Add-Section "Company-scoped tables (filter: company_id = $companyId)" $companyScopedTables
Add-Section "Linked / pivot tables (filter: linked to Valiant entities)" $linkedTables

Add-Content -Path $outFile -Value "" -Encoding UTF8
Add-Content -Path $outFile -Value "COMMIT;" -Encoding UTF8

# Keep intermediate files for debugging.
# Remove-Item -Path $masterSql, $masterOut -ErrorAction SilentlyContinue

Write-Host "`nDump written to: $outFile" -ForegroundColor Green
$size = (Get-Item $outFile).Length
Write-Host "Size: $([math]::Round($size/1KB,2)) KB ($size bytes)"
if ($remappings.Count -gt 0) {
    $totalRemapped = ($remappings.Values | ForEach-Object { $_.Count } | Measure-Object -Sum).Sum
    Write-Host "Remapped IDs: $totalRemapped across $($remappings.Count) table(s)" -ForegroundColor Cyan
}
