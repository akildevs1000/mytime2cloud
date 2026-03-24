<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SplitShiftController extends Controller
{
    public $logFilePath = 'logs/shifts/dual_shift/controller';

    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        // $endDateString = $request->dates[1];
        if (isset($request->dates[1])) {
            $endDateString = $request->dates[1];
        } else {
            $endDateString = $request->dates[0];
        }

        $company_id = $request->company_ids[0];
        $employee_ids = $request->employee_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $response = [];

        // while ($startDate <= $currentDate && $startDate <= $endDate) {
        while ($startDate <= $endDate) {
            //$response[] = $this->render($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, true);

            // if ($company_id == 60) {
            //     $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, $request->filled("auto_render") ? false : true, $request->channel ?? "unknown");
            // } else {
            // }

            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, $request->filled("auto_render") ? false : true, $request->channel ?? "unknown");

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        // return $departmentIds = Department::where("company_id",$request->company_id)->pluck("id");
        // $employee_ids = Employee::where("department_id", 31)->pluck("system_user_id");

        return $this->render($request->company_id, $request->date, $request->shift_type_id, $request->UserIds, $request->custom_render ?? true, $request->channel ?? "unknown");
    }


    public function renderV1($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel = "unknown")
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);
        $items = [];
        $debugSummary = [];

        foreach ($employees as $row) {
            $shift = $row->schedule->shift ?? null;
            if (!$shift) continue;

            // Fetch logs and load device relationship to avoid "Undefined key" errors later
            return $allLogs = AttendanceLog::with('device')
                ->where("company_id", $id)
                ->where("UserID", $row->system_user_id)
                ->whereDate('LogTime', $date)
                ->orderBy("LogTime", 'asc')
                ->get();

            $totalMinutes = 0;
            $logsJson = [];
            $userSummary = [];

            // Define the two sessions from your shift object
            $sessions = [
                [
                    'name' => 'S1',
                    'in_s' => $shift["beginning_in"],
                    'in_e' => $shift["ending_in"],
                    'out_s' => $shift["beginning_out"],
                    'out_e' => $shift["ending_out"]
                ],
                [
                    'name' => 'S2',
                    'in_s' => $shift["beginning_in1"],
                    'in_e' => $shift["ending_in1"],
                    'out_s' => $shift["beginning_out1"],
                    'out_e' => $shift["ending_out1"]
                ]
            ];

            foreach ($sessions as $ses) {
                // STRICT WINDOW FILTERING
                $validInLog = $allLogs->filter(function ($log) use ($ses) {
                    $time = Carbon::parse($log->LogTime)->format('H:i');
                    return $time >= $ses['in_s'] && $time <= $ses['in_e'];
                })->first();

                $validOutLog = $allLogs->filter(function ($log) use ($ses) {
                    $time = Carbon::parse($log->LogTime)->format('H:i');
                    return $time >= $ses['out_s'] && $time <= $ses['out_e'];
                })->last();

                $min = 0;
                if ($validInLog && $validOutLog) {
                    $min = Carbon::parse($validInLog->LogTime)->diffInMinutes(Carbon::parse($validOutLog->LogTime));
                    $totalMinutes += $min;
                }

                // Include device keys to fix the "Undefined array key" error
                if ($validInLog || $validOutLog) {
                    $inTime = $validInLog ? Carbon::parse($validInLog->LogTime)->format('H:i') : "---";
                    $outTime = $validOutLog ? Carbon::parse($validOutLog->LogTime)->format('H:i') : "---";

                    $logsJson[] = [
                        "in"            => $inTime,
                        "out"           => $outTime,
                        "device_in"     => $validInLog ? ($validInLog->device->name ?? "Device") : "---",
                        "device_out"    => $validOutLog ? ($validOutLog->device->name ?? "Device") : "---",
                        "total_minutes" => $min,
                    ];

                    info(count($logsJson));


                    $userSummary[] = "({$ses['name']}: In $inTime, Out $outTime)";
                }
            }

            $debugSummary[] = "User {$row->system_user_id}: " . (empty($userSummary) ? "No valid logs" : implode(" ", $userSummary));


            $dayOfWeekThreeLetter = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';

            $status = Attendance::processWeekOffFunc($currentDayKey, $shift['weekoff_rules'] ?? "A", $id, $date, $row->system_user_id, $allLogs->first()) ?? "A";

            return $logsJson;


            if ($status === "A" && count($logsJson) === 1) {
                $status = Attendance::MISSING;
            }

            $items[] = [
                "employee_id"   => $row->system_user_id,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $shift->id,
                "shift_type_id" => $shift->shift_type_id,
                "total_hrs"     => $this->minutesToHours($totalMinutes),
                "status"        => $status,
                "logs"          => json_encode($logsJson, JSON_PRETTY_PRINT),
            ];
        }

        // DB Update
        if (count($items) > 0) {
            Attendance::whereIn("employee_id", array_column($items, "employee_id"))
                ->where("date", $date)
                ->where("company_id", $id)
                ->delete();
            Attendance::insert($items);
        }

        return "Done for $date. Log Summary: " . implode(" | ", $debugSummary);
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel = "unknown")
    {
        $params = [
            "company_id"    => $id,
            "date"          => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds"       => $UserIds,
        ];

        // 1. Fetch User IDs if not provided
        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        if (empty($params["UserIds"])) {
            return "[" . $date . "] No employees found to render.";
        }

        // 2. CACHING & PRE-FETCHING (Performance)
        $isHoliday = Cache::remember(
            "holiday_{$id}_{$date}",
            3600,
            fn() =>
            DB::table('holidays')->where('company_id', $id)->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->exists()
        );

        // Fetch All logs for the range in one go
        $allLogs = (new AttendanceLog)->getLogsWithInRangeNew($params);

        // Fetch Employees with their schedules
        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);
        if (count($employees) == 0) {
            $employees = (new Employee)->GetEmployeeWithShiftDetails($params);
        }

        $items = [];
        $dayOfWeek = date('D', strtotime($date));
        $currentDayKey = Attendance::DAY_MAP[$dayOfWeek] ?? '';

        // 3. MAIN LOOP
        foreach ($employees as $row) {
            $systemUserId = $row->system_user_id;
            $shift = $row->schedule->shift ?? false;
            $isOverTime = $row->schedule->isOverTime ?? false;

            // Determine Default Status (Holiday or WeekOff)
            $defaultStatus = $isHoliday ? "H" : Attendance::processWeekOffFunc(
                $currentDayKey,
                $shift['weekoff_rules'] ?? "A",
                $id,
                $date,
                $systemUserId,
                null
            );

            // Fetch User Logs from pre-fetched collection
            $userLogs = collect($allLogs[$systemUserId] ?? [])
                ->unique('LogTime')
                ->sortBy('LogTime')
                ->values();

            // 4. ABSENT / HOLIDAY RECORD (GAP FILLING)
            if ($userLogs->isEmpty()) {
                $items[] = [
                    "employee_id"   => $systemUserId,
                    "date"          => $date,
                    "company_id"    => $id,
                    "shift_id"      => $shift["id"] ?? 0,
                    "shift_type_id" => $shift["shift_type_id"] ?? 0,
                    "total_hrs"     => "00:00",
                    "status"        => $defaultStatus ?? "A",
                    "logs"          => json_encode([]),
                    "ot"            => "---"
                ];
                continue;
            }

            // 5. PAIRING LOGIC (PRESENT / MULTI-LOG)
            $logsJson = [];
            $totalMinutes = 0;
            $firstInTime = $userLogs[0]['time'] ?? '---';
            $lastOutTime = '---';

            for ($i = 0; $i < count($userLogs); $i += 2) {
                $inLog = $userLogs[$i];
                $outLog = $userLogs[$i + 1] ?? null;

                $inTime = $inLog['time'] ?? '---';
                $outTime = "---";
                $duration = 0;

                if ($outLog) {
                    $outTime = $outLog['time'] ?? '---';
                    $lastOutTime = $outTime; // Keep track of the final out
                    $startTS = strtotime($inLog['LogTime']);
                    $endTS   = strtotime($outLog['LogTime']);

                    if ($endTS > $startTS) {
                        $duration = ($endTS - $startTS) / 60;
                        $totalMinutes += $duration;
                    }
                }

                $logsJson[] = [
                    "in"            => $inTime,
                    "out"           => $outTime,
                    "device_in"     => $this->getDeviceName($inLog, ["In", "Auto", "Mobile"]),
                    "device_out"    => $outLog ? $this->getDeviceName($outLog, ["Out", "Auto", "Mobile"]) : "---",
                    "total_minutes" => $duration,
                ];
            }

            $totalHrs = $this->minutesToHours($totalMinutes);

            // Determine Final Status
            // Default: Even logs = P, Odd logs = M
            $finalStatus = (count($userLogs) % 2 !== 0) ? "M" : "P";

            // Override if there are significant attendance rules
            if ($shift) {
                // Significant Late Coming (Absent/Half Day Rule)
                if (($shift->significant_attendanc_rule_late_coming ?? 'No Action') !== 'No Action') {
                    $sigLcMins = calculateTimeDiff($firstInTime, $shift->on_duty_time, 'late', $shift->absent_min_in);
                    if ($sigLcMins > 0) {
                        $finalStatus = ($shift->significant_attendanc_rule_late_coming === "Half Day") ? "HD" : "A";
                    }
                }

                // Significant Early Going (Only if an out log exists)
                if ($lastOutTime !== "---" && ($shift->significant_attendanc_rule_early_going ?? 'No Action') !== 'No Action') {
                    $offDuty = $shift->off_duty_time;
                    if (($shift->halfday ?? '') == $dayOfWeek) {
                        $offDuty = gmdate("H:i", (strtotime($shift->halfday_working_hours) - strtotime('00:00')) + strtotime($shift->on_duty_time) - strtotime('00:00'));
                    }
                    $sigEgMins = calculateTimeDiff($lastOutTime, $offDuty, 'early', $shift->absent_min_out);
                    if ($sigEgMins > 0) {
                        $finalStatus = ($shift->significant_attendanc_rule_early_going === "Half Day") ? "HD" : "A";
                    }
                }
            }

            $items[] = [
                "employee_id"   => $systemUserId,
                "date"          => $date,
                "company_id"    => $id,
                "shift_id"      => $shift["id"] ?? 0,
                "shift_type_id" => $shift["shift_type_id"] ?? 0,
                "total_hrs"     => $totalHrs,
                "status"        => $finalStatus,
                "logs"          => json_encode($logsJson),
                "ot"            => ($isOverTime && isset($shift->working_hours))
                    ? $this->calculatedOT($totalHrs, $shift->working_hours, $shift->overtime_interval)
                    : "---"
            ];
        }

        // 6. FINAL SYNC & DATABASE OPERATIONS
        if (!empty($items)) {
            try {
                DB::beginTransaction();

                // Clear old records to avoid duplicates
                Attendance::where("company_id", $id)
                    ->where("date", $date)
                    ->whereIn("employee_id", array_column($items, 'employee_id'))
                    ->delete();

                // Chunked insert for speed
                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::insert($chunk);
                }

                // Update Log table status
                AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", $params["UserIds"])
                    ->whereDate("LogTime", $date)
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel,
                        "log_message"      => "Multi-Rendered on " . date('Y-m-d H:i:s')
                    ]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollback();
                Log::error("Multi-Render Error: " . $e->getMessage());
                return "Error: " . $e->getMessage();
            }
        }

        return "[" . $date . "] Multi-Render Complete. Records: " . count($items);
    }

    private function getDeviceName($log, $validFunctions)
    {
        if ($log['device']['name'] == "---") {
            return "Manual";
        }

        return isset($log["device"]["function"]) && in_array($log["device"]["function"], $validFunctions) ? $log["device"]["function"] : "---";
    }
}
