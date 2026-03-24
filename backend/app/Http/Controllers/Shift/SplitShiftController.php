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

        $items       = [];
        $message     = "";
        $logsUpdated = 0;

        $validTypes = ["in", "out", "auto", "option", "mobile"];

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"]      = $row->schedule->shift ?? false;

            if (!$params["shift"] || !isset($params["shift"]["id"])) {
                $message .= "{$row->system_user_id} : No shift configured on date: $date; ";
                continue;
            }

            $logs = (new AttendanceLog)->getLogsWithInRangeNew($params);
            $rawData = $logs[$row->system_user_id] ?? [];

            // Clean and Re-index data
            $data = collect($rawData)
                ->unique('LogTime')
                ->filter(function ($log, $index) use ($rawData) {
                    // If OX-900, we skip the consecutive check because it often sends 'Auto'
                    if (isset($log['device']) && ($log['device']['model_number'] ?? null) != 'OX-900') {
                        $prev = $rawData[$index - 1] ?? null;
                        if (
                            in_array(strtolower($log['log_type']), ['in', 'out']) &&
                            $prev && strtolower($prev['log_type']) === strtolower($log['log_type'])
                        ) {
                            return false;
                        }
                    }
                    return true;
                })
                ->values(); // Essential: Reset indices to 0, 1, 2...

            $dayOfWeekThreeLetter = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';

            // Initial status calculation
            $status = Attendance::processWeekOffFunc($currentDayKey, $params["shift"]['weekoff_rules'] ?? "A", $id, $date, $row->system_user_id, $data->first()) ?? "A";

            $totalMinutes = 0;
            $logsJson     = [];
            $i            = 0;

            while ($i < count($data)) {
                $currentLog  = $data[$i];
                $currentTime = $currentLog['time'] ?? '---';
                $currentType = strtolower($currentLog['log_type'] ?? '');

                // 1. Identify a valid IN
                $validInTime = $this->getLogTime($currentLog, ["In", "Auto", "Option", "Mobile"], ["Manual"]);

                // Force time if type is explicitly 'in' or 'auto'
                if (in_array($currentType, ['in', 'auto', 'option', 'mobile'])) {
                    $validInTime = $currentTime;
                }

                if ($validInTime === "---") {
                    $i++;
                    continue;
                }

                // 2. Look for a matching OUT
                $nextLog      = null;
                $validOutTime = "---";

                for ($j = $i + 1; $j < count($data); $j++) {
                    $candidateLog  = $data[$j];
                    $candidateTime = $candidateLog['time'] ?? '---';
                    $candidateType = strtolower($candidateLog['log_type'] ?? '');

                    // An OUT is valid if it's a different time than IN 
                    // and is a logical "exit" type (or Auto)
                    if ($candidateTime !== $currentTime) {
                        $validOutTime = $this->getLogTime($candidateLog, ["Out", "Auto", "Option", "Mobile"], ["Manual"]);

                        if (in_array($candidateType, ['out', 'auto', 'option', 'mobile'])) {
                            $validOutTime = $candidateTime;
                        }

                        if ($validOutTime !== "---") {
                            $nextLog = $candidateLog;
                            $i = $j; // Move main loop pointer to this Out log
                            break;
                        }
                    }
                }

                // 3. Calculate Minutes
                $minutes = 0;
                if ($nextLog) {
                    $parsedIn  = strtotime($currentTime);
                    $parsedOut = strtotime($nextLog['time']);
                    if ($parsedIn > $parsedOut) $parsedOut += 86400; // Midnight cross
                    $minutes = ($parsedOut - $parsedIn) / 60;
                    $totalMinutes += $minutes;
                }

                $logsJson[] = [
                    "in"            => $validInTime,
                    "out"           => $nextLog ? $validOutTime : "---",
                    "device_in"     => $this->getDeviceName($currentLog, ["In", "Auto", "Option", "Mobile"]),
                    "device_out"    => $nextLog ? $this->getDeviceName($nextLog, ["Out", "Auto", "Option", "Mobile"]) : "---",
                    "total_minutes" => $minutes,
                ];

                $i++;
            }

            // Final Item Assembly
            $item = [
                "employee_id"   => $row->system_user_id,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                "in"            => $logsJson[0]["in"] ?? "---",
                "out"           => end($logsJson)["out"] ?? "---",
                "total_hrs"     => $this->minutesToHours($totalMinutes),
                "status"        => (empty($logsJson)) ? Attendance::MISSING : $status,
                "logs"          => json_encode($logsJson),
            ];

            // Correct status if only one punch exists (Missing Out)
            if (count($logsJson) > 0 && end($logsJson)["out"] === "---") {
                $item["status"] = Attendance::MISSING;
            }

            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT(
                    $item["total_hrs"],
                    $params["shift"]->working_hours,
                    $params["shift"]->overtime_interval
                );
            }

            $items[] = $item;
        }

        // Database operations
        try {
            if (count($items) > 0) {
                $items = collect($items)->unique('employee_id')->values()->all();

                Attendance::whereIn("employee_id", array_column($items, "employee_id"))
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->delete();

                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::insert($chunk);
                }

                $logsUpdated = AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", array_column($items, "employee_id"))
                    ->whereDate("LogTime", $date)
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel,
                    ]);
            }
        } catch (\Throwable $e) {
            $this->logOutPut($this->logFilePath, "Render Error: " . $e->getMessage());
        }

        return "[" . $date . " " . date("H:i:s") . "] Processed " . count($items) . " employees.";
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
