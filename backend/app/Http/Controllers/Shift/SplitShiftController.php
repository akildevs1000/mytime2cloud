<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

            $response[] = $this->render_new($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, $request->filled("auto_render") ? false : true, $request->channel ?? "unknown");


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

    public function render_new($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $isRequestFromAutoshift = false, $channel = "unknown")
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds" => $UserIds,
        ];

        // 1. Fetch UserIds if not provided manually
        if (!$custom_render) {
            $params["UserIds"] = AttendanceLog::where("company_id", $params["company_id"])
                ->when(!$params["custom_render"], fn($q) => $q->where("checked", false))
                ->where("LogTime", ">=", $params["date"])
                ->where("LogTime", "<=", date("Y-m-d", strtotime($params["date"] . " +1 day")))
                ->whereHas("schedule", fn($q) => $q->where("isAutoShift", $isRequestFromAutoshift))
                ->distinct("UserID")
                ->pluck('UserID')
                ->toArray();
        }

        if (empty($params["UserIds"])) return "[" . $date . "] No employees found.";

        // 2. CACHING & DATA FETCHING
        $isHoliday = Cache::remember(
            "holiday_{$id}_{$date}",
            3600,
            fn() =>
            DB::table('holidays')->where('company_id', $id)->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->exists()
        );

        $allSchedules = Cache::remember(
            "schedules_company_{$id}",
            600,
            fn() =>
            ScheduleEmployee::with('shift')->where("company_id", $id)->get()->groupBy("employee_id")
        );

        // Fetch logs for 2 days to handle night shifts
        $logEndDate = date("Y-m-d", strtotime($params["date"] . " +1 day"));
        $logsEmployees = AttendanceLog::with(["device"])
            ->where("company_id", $params["company_id"])
            ->where("LogTime", ">=", $params["date"] . " 00:00:00")
            ->where("LogTime", "<=", $logEndDate . " 23:59:59")
            ->whereIn("UserID", $params["UserIds"])
            ->orderBy("LogTime", "asc")
            ->get()
            ->groupBy('UserID');

        $items = [];
        $dayOfWeek = date('D', strtotime($date));
        $currentDayKey = Attendance::DAY_MAP[$dayOfWeek] ?? '';

        // 3. MAIN PROCESSING LOOP
        foreach ($params["UserIds"] as $employeeId) {
            $key = $employeeId;
            // Clean logs: Remove duplicates by time and ensure we only have valid types
            $employeeLogs = $logsEmployees->get($key, collect([]))
                ->unique('LogTime')
                ->values();

            $empSchedules = $allSchedules->get($key, collect([]));
            $matchedSchedule = $empSchedules->first(fn($sch) => in_array($dayOfWeek, $sch->shift->days ?? [])) ?: $empSchedules->first();
            $shiftData = $matchedSchedule->shift ?? null;

            $defaultStatus = $isHoliday ? "H" : Attendance::processWeekOffFunc($currentDayKey, $shiftData['weekoff_rules'] ?? "A", $id, $date, $key, null);

            $item = [
                "total_hrs" => "00:00",
                "in" => "---",
                "out" => "---",
                "ot" => "00:00",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "company_id" => $params["company_id"],
                "employee_id" => $key,
                "shift_id" => $shiftData->id ?? 0,
                "shift_type_id" => $shiftData->shift_type_id ?? 0,
                "status" => $defaultStatus ?? "A",
                "late_coming" => "---",
                "early_going" => "---",
            ];

            if ($employeeLogs->isNotEmpty()) {
                $logsJson = [];
                $totalMinutes = 0;
                $i = 0;

                // MULTI-PUNCH PAIRING LOGIC
                while ($i < $employeeLogs->count()) {
                    $currentLog = $employeeLogs[$i];
                    $currentType = strtolower($currentLog->log_type ?? '');

                    // 1. Find the IN log
                    if (in_array($currentType, ['in', 'auto', 'option', 'mobile', null])) {
                        $inTime = $currentLog->time;
                        $outTime = "---";
                        $nextLog = null;

                        // 2. Find the NEXT log as the OUT
                        for ($j = $i + 1; $j < $employeeLogs->count(); $j++) {
                            $candidate = $employeeLogs[$j];
                            if ($candidate->time !== $inTime) {
                                $nextLog = $candidate;
                                $outTime = $candidate->time;
                                $i = $j; // Move main index to this log
                                break;
                            }
                        }

                        $minutes = 0;
                        if ($nextLog) {
                            $parsedIn = strtotime($inTime);
                            $parsedOut = strtotime($outTime);
                            if ($parsedIn > $parsedOut) $parsedOut += 86400;
                            $minutes = ($parsedOut - $parsedIn) / 60;
                            $totalMinutes += $minutes;
                        }

                        $logsJson[] = [
                            "in" => $inTime,
                            "out" => $outTime,
                            "device_in" => $currentLog->device->name ?? "---",
                            "device_out" => $nextLog->device->name ?? "---",
                            "total_minutes" => $minutes
                        ];
                    }
                    $i++;
                }

                if (!empty($logsJson)) {
                    $item["in"] = $logsJson[0]["in"];
                    $item["out"] = end($logsJson)["out"];
                    $item["device_id_in"] = $employeeLogs->first()->DeviceID;
                    $item["device_id_out"] = ($item["out"] !== "---") ? $employeeLogs->last()->DeviceID : "---";
                    $item["total_hrs"] = $this->minutesToHours($totalMinutes);

                    // Status Handling
                    if (end($logsJson)["out"] === "---") {
                        $item["status"] = "M"; // Missing Out
                    } else {
                        $item["status"] = "P"; // Present

                        // Late Coming / Early Going Logic
                        if ($shiftData) {
                            if ($shiftData->attendanc_rule_late_coming !== 'No Action') {
                                $item["late_coming"] = $this->calculatedLateComing($item["in"], $shiftData->on_duty_time, $shiftData->late_time);
                                if ($item["late_coming"] != "---") $item["status"] = "LC";
                            }

                            $offDuty = $shiftData->off_duty_time;
                            if ($shiftData->attendanc_rule_early_going !== 'No Action') {
                                $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $offDuty, $shiftData->early_time);
                                if ($item["early_going"] != "---") $item["status"] = "EG";
                            }
                        }
                    }

                    // Overtime
                    if ($matchedSchedule->isOverTime && $shiftData) {
                        $otTime = $this->calculatedOT($item["total_hrs"], $shiftData->working_hours, $shiftData->overtime_interval);
                        $item["ot"] = ($otTime == "---") ? "00:00" : $otTime;
                    }

                    $item["logs"] = json_encode($logsJson);
                }
            }

            $items[] = $item;
        }

        // 5. BATCH UPDATE
        try {
            DB::beginTransaction();
            Attendance::where("company_id", $id)->whereIn("employee_id", $params["UserIds"])->where("date", $date)->delete();
            foreach (array_chunk($items, 200) as $chunk) Attendance::insert($chunk);
            DB::commit();
            $message = "[$date] Render successful for " . count($items) . " employees.";
        } catch (\Throwable $e) {
            DB::rollback();
            $message = "[$date] Error: " . $e->getMessage();
        }

        return $message;
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel)
    {
        $params = [
            "company_id"    => $id,
            "date"          => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds"       => $UserIds,
        ];

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        // Update shift ID for No logs
        if (count($employees) == 0) {
            $employees = (new Employee)->GetEmployeeWithShiftDetails($params);
            foreach ($employees as $key => $value) {
                if ($value->schedule->shift && $value->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id"      => $value->schedule->shift["id"],
                        "shift_type_id" => $value->schedule->shift["shift_type_id"],
                    ];
                    $model1 = Attendance::query();
                    $model1->whereIn("employee_id", $UserIds);
                    $model1->where("date", $params["date"]);
                    $model1->where("company_id", $params["company_id"]);
                    $model1->update($data1);
                }
            }
        }

        $items       = [];
        $message     = "";
        $logsUpdated = 0;

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"]      = $row->schedule->shift ?? false;

            $logs = (new AttendanceLog)->getLogsWithInRangeNew($params);
            $data = $logs[$row->system_user_id] ?? [];

            // Fix indexing by calling values() after filter
            $data = collect($data)
                ->unique('LogTime')
                ->filter(function ($log, $index) use ($data) {
                    $prev = $data[$index - 1] ?? null;

                    if (isset($log['device']) && ($log['device']['model_number'] ?? null) != 'OX-900') {
                        return true;
                    }

                    if (
                        in_array($log['log_type'], ['In', 'Out']) &&
                        $prev &&
                        $prev['log_type'] === $log['log_type']
                    ) {
                        return false;
                    }

                    return true;
                })
                ->values(); // Reset array keys to 0, 1, 2...

            $dayOfWeekThreeLetter = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';
            $status = Attendance::processWeekOffFunc($currentDayKey, $params["shift"]['weekoff_rules'] ?? "A", $id, $date, $row->system_user_id, $data->first());

            if (!count($data)) {
                if ($row->schedule->shift && $row->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id"      => $row->schedule->shift["id"],
                        "shift_type_id" => $row->schedule->shift["shift_type_id"],
                        "status"        => $status ?? "A",
                    ];
                    Attendance::query()
                        ->where("employee_id", $row->system_user_id)
                        ->where("date", $params["date"])
                        ->where("company_id", $params["company_id"])
                        ->update($data1);
                }
                $message .= "{$row->system_user_id}   has No Logs to render";
                continue;
            }

            if (!$params["shift"]["id"]) {
                $message .= "{$row->system_user_id} : No shift configured on date: $date";
                continue;
            }

            $item = [
                "total_hrs"     => 0,
                "in"            => "---",
                "out"           => "---",
                "ot"            => "---",
                "device_id_in"  => "---",
                "device_id_out" => "---",
                "date"          => $params["date"],
                "company_id"    => $params["company_id"],
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                "status"        => $status ?? "A",
            ];

            $totalMinutes = 0;
            $logsJson     = [];
            $i            = 0;

            while ($i < count($data)) {
                $currentLog  = $data[$i];
                $currentTime = $currentLog['time'] ?? '---';
                $currentType = strtolower($currentLog['log_type'] ?? '');

                // 1. Validate IN (Supports Auto/Option/Mobile)
                $validInTime = $this->getLogTime(
                    $currentLog,
                    ["In", "Auto", "Option", "in", "auto", "option", "Mobile", "mobile"],
                    ["Manual", "manual", "MANUAL"]
                );

                if ($currentType == "in" || $currentType == "auto") {
                    $validInTime = $currentTime;
                }

                if ($validInTime === "---") {
                    $i++;
                    continue;
                }

                // 2. Search for valid OUT
                $nextLog      = null;
                $validOutTime = "---";

                for ($j = $i + 1; $j < count($data); $j++) {
                    $candidateLog  = $data[$j];
                    $candidateTime = $candidateLog['time'] ?? '---';
                    $candidateType = strtolower($candidateLog['log_type'] ?? '');

                    if ($candidateTime !== '---' && $candidateTime !== $currentTime) {
                        $validOutTime = $this->getLogTime(
                            $candidateLog,
                            ["Out", "Auto", "Option", "out", "auto", "option", "Mobile", "mobile"],
                            ["Manual", "manual", "MANUAL"]
                        );

                        // Accept 'out' or 'auto' as a valid exit
                        if ($candidateType == "out" || $candidateType == "auto") {
                            $validOutTime = $candidateTime;
                        }

                        if ($validOutTime !== "---") {
                            $nextLog = $candidateLog;
                            $i = $j; // Jump to this log
                            break;
                        }
                    }
                }

                // 3. Calculation
                $minutes = 0;
                if ($nextLog) {
                    $parsedIn  = strtotime($currentTime);
                    $parsedOut = strtotime($nextLog['time'] ?? '---');
                    if ($parsedIn > $parsedOut) {
                        $parsedOut += 86400;
                    }
                    $minutes = ($parsedOut - $parsedIn) / 60;
                    $totalMinutes += $minutes;
                }

                $logsJson[] = [
                    "in"            => $validInTime,
                    "out"           => $nextLog ? $validOutTime : "---",
                    "device_in"     => $this->getDeviceName($currentLog, ["In", "Auto", "Option", "in", "auto", "option", "Mobile", "mobile"]),
                    "device_out"    => $nextLog ? $this->getDeviceName($nextLog, ["Out", "Auto", "Option", "out", "auto", "option", "Mobile", "mobile"]) : "---",
                    "total_minutes" => $minutes,
                ];

                $i++;
            }

            // Status Logic
            if (count($logsJson) > 0) {
                // If the very last log pair has no 'out', it's MISSING, otherwise use $status or PRESENT
                $item["status"] = (end($logsJson)["out"] === "---") ? Attendance::MISSING : ($status ?? Attendance::PRESENT);
            } else {
                $item["status"] = $status ?? Attendance::MISSING;
            }

            $item["employee_id"] = $row->system_user_id;
            $item["total_hrs"]   = $this->minutesToHours($totalMinutes);

            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT(
                    $item["total_hrs"],
                    $params["shift"]->working_hours,
                    $params["shift"]->overtime_interval
                );
            }

            $item["logs"] = json_encode($logsJson, JSON_PRETTY_PRINT);
            $items[]      = $item;
        }

        $items = collect($items)->unique('employee_id')->values()->all();

        try {
            if (count($items) > 0) {
                Attendance::query()
                    ->whereIn("employee_id", array_column($items, "employee_id"))
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->delete();

                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::query()->insert($chunk);
                }

                $message = "[" . $date . " " . date("H:i:s") . "] Multi Shift.   Affected Ids: " . json_encode($UserIds) . " " . $message;

                $logsUpdated = AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", $UserIds ?? [])
                    ->where("LogTime", ">=", $date)
                    ->where("LogTime", "<=", date("Y-m-d", strtotime($date . "+1 day")))
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel,
                        "log_message"      => substr($message, 0, 200),
                    ]);
            }
        } catch (\Throwable $e) {
            $this->logOutPut($this->logFilePath, $e->getMessage());
        }

        $this->logOutPut($this->logFilePath, ["UserIds" => $UserIds, "params" => $params, "items" => $items]);
        $this->logOutPut($this->logFilePath, "[" . $date . " " . date("H:i:s") . "] " . "$logsUpdated " . " updated logs");
        $this->logOutPut($this->logFilePath, $message);

        return "[" . $date . " " . date("H:i:s") . "] " . $message;
    }

    private function getLogTime($log, $validFunctions, $manualDeviceID)
    {
        // return $log && $log['time'] ? $log['time'] : "---";

        if (isset($log["device"]["function"]) && in_array($log["device"]["function"], $validFunctions)) {
            return $log['time'];
        } else if (in_array($log["DeviceID"], $manualDeviceID)) {
            return $log['time'];
        }

        return "---";
    }
    private function getDeviceName($log, $validFunctions)
    {
        if ($log['device']['name'] == "---") {
            return "Manual";
        }

        return isset($log["device"]["function"]) && in_array($log["device"]["function"], $validFunctions) ? $log["device"]["function"] : "---";
    }
}
