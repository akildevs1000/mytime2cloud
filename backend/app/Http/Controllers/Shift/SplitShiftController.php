<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;

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

            $response[] = $this->renderV1($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, $request->filled("auto_render") ? false : true, $request->channel ?? "unknown");


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
            $allLogs = AttendanceLog::with('device')
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
