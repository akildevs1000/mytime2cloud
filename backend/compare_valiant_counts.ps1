# Compares Valiant row counts table-by-table between source and target after restore.
# Prints only tables with differences.

$ErrorActionPreference = "Stop"
$psql = "C:\Program Files\PostgreSQL\15\bin\psql.exe"

$srcPassword = "tZqsls7URjNC0aF"
$tgtPassword = "test123"
$srcArgs = @("-h","127.0.0.1",   "-p","5432","-U","postgres","-d","mytime2cloud-local","-t","-A","--no-psqlrc")
$tgtArgs = @("-h","139.59.69.241","-p","5432","-U","francis", "-d","mytime2cloud-v2",   "-t","-A","--no-psqlrc")

$companyId = 49

# Same set of tables and WHERE clauses as the dump generator.
$entries = @(
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
    @{ table = "branch_contacts";                where = "branch_id IN (SELECT id FROM branches WHERE company_id = $companyId)" }
    @{ table = "user_branches";                  where = "branch_id IN (SELECT id FROM branches WHERE company_id = $companyId) OR user_id IN (SELECT id FROM users WHERE company_id = $companyId)" }
    @{ table = "user_departments";               where = "department_id IN (SELECT id FROM departments WHERE company_id = $companyId) OR user_id IN (SELECT id FROM users WHERE company_id = $companyId)" }
    @{ table = "announcement_department";        where = "announcement_id IN (SELECT id FROM announcements WHERE company_id = $companyId)" }
    @{ table = "announcement_employee";          where = "announcement_id IN (SELECT id FROM announcements WHERE company_id = $companyId) OR employee_id IN (SELECT id FROM employees WHERE company_id = $companyId)" }
    @{ table = "assign_departments";             where = "department_id IN (SELECT id FROM departments WHERE company_id = $companyId) OR assigned_department_employee_id IN (SELECT id FROM assigned_department_employees WHERE company_id = $companyId)" }
    @{ table = "assign_employees";               where = "employee_id IN (SELECT id FROM employees WHERE company_id = $companyId) OR assigned_department_employee_id IN (SELECT id FROM assigned_department_employees WHERE company_id = $companyId)" }
    @{ table = "finger_prints";                  where = "employee_id::text IN (SELECT id::text FROM employees WHERE company_id = $companyId)" }
    @{ table = "palms";                          where = "employee_id::text IN (SELECT id::text FROM employees WHERE company_id = $companyId)" }
    @{ table = "employee_leave_timelines";       where = "employee_leave_id IN (SELECT id FROM employee_leaves WHERE company_id = $companyId)" }
    @{ table = "employee_report";                where = "employee_id IN (SELECT id FROM employees WHERE company_id = $companyId)" }
    @{ table = "zone_devices";                   where = "zone_id IN (SELECT id FROM zones WHERE company_id = $companyId) OR device_id IN (SELECT id FROM devices WHERE company_id = $companyId)" }
    @{ table = "access_control_time_slots";      where = "device_id::text IN (SELECT id::text FROM devices WHERE company_id = $companyId)" }
    @{ table = "reasons";                        where = "branch_id IN (SELECT id FROM branches WHERE company_id = $companyId) OR user_id::text IN (SELECT id::text FROM users WHERE company_id = $companyId)" }
)

$mismatches = @()
$skipped    = @()
$ok         = 0

foreach ($e in $entries) {
    $t = $e.table
    $w = $e.where
    $sql = "SELECT COUNT(*) FROM `"$t`" WHERE $w"

    $env:PGPASSWORD = $tgtPassword
    $exists = (& $psql @tgtArgs -c "SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='$t';").Trim()
    if ($exists -ne "1") {
        $skipped += [pscustomobject]@{ table=$t; reason="not in target schema" }
        continue
    }

    $env:PGPASSWORD = $srcPassword
    $src = (& $psql @srcArgs -c $sql 2>$null).Trim()
    $env:PGPASSWORD = $tgtPassword
    $tgt = (& $psql @tgtArgs -c $sql 2>$null).Trim()
    if ([string]::IsNullOrWhiteSpace($src)) { $src = "0" }
    if ([string]::IsNullOrWhiteSpace($tgt)) { $tgt = "0" }

    if ([int]$src -ne [int]$tgt) {
        $mismatches += [pscustomobject]@{
            table = $t
            src   = [int]$src
            tgt   = [int]$tgt
            lost  = [int]$src - [int]$tgt
        }
    } else {
        $ok++
    }
}

Write-Host ""
Write-Host "==================================================================" -ForegroundColor Cyan
Write-Host "Tables with row count mismatch (lost rows = blocked by ON CONFLICT)" -ForegroundColor Cyan
Write-Host "==================================================================" -ForegroundColor Cyan
if ($mismatches.Count -eq 0) {
    Write-Host "  (none)" -ForegroundColor Green
} else {
    $mismatches | Sort-Object -Property lost -Descending | Format-Table table, src, tgt, lost -AutoSize
}

Write-Host ""
Write-Host "Tables skipped (don't exist in target schema):" -ForegroundColor DarkYellow
if ($skipped.Count -eq 0) {
    Write-Host "  (none)" -ForegroundColor DarkGray
} else {
    $skipped | Format-Table -AutoSize
}

Write-Host ""
Write-Host ("Tables matching exactly: {0}" -f $ok) -ForegroundColor Green
Write-Host ("Tables with mismatch:    {0}" -f $mismatches.Count) -ForegroundColor $(if ($mismatches.Count -eq 0) { "Green" } else { "Yellow" })
Write-Host ("Tables skipped:          {0}" -f $skipped.Count) -ForegroundColor DarkYellow
