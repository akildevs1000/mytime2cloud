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

        $items = [];
        $message = "";

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift ?? false;

            if (!$params["shift"]) {
                $message .= "{$row->system_user_id}: No shift; ";
                continue;
            }

            /** * 1. Fetch ALL logs using log_date.
             * We remove whereHas("schedule") to ensure no logs are hidden.
             */
            $logs = AttendanceLog::where("company_id", $id)
                ->where("UserID", $row->system_user_id)
                ->where("log_date", $date) // Using log_date column as requested
                ->orderBy("LogTime", 'asc')
                ->get()
                ->load("device");

            $data = $logs->values();
            $count = $data->count();

            // Standard Week-off / Holiday check
            $dayOfWeekThreeLetter = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';
            $status = Attendance::processWeekOffFunc($currentDayKey, $params["shift"]->weekoff_rules ?? "A", $id, $date, $row->system_user_id, $data->first()) ?? "A";

            $totalMinutes = 0;
            $logsJson     = [];
            $i = 0;

            /**
             * 2. Sequential Pairing
             * Takes every log found. If 3 exist:
             * Pair 1: Log 1 (In) - Log 2 (Out)
             * Pair 2: Log 3 (In) - "---" (Out)
             */
            while ($i < $count) {
                $currentLog = $data[$i];
                $nextLog    = $data[$i + 1] ?? null;

                $inTimeRaw  = $currentLog->LogTime;
                $outTimeRaw = $nextLog ? $nextLog->LogTime : "---";
                $minutes    = 0;

                if ($nextLog) {
                    $parsedIn  = strtotime($inTimeRaw);
                    $parsedOut = strtotime($outTimeRaw);

                    // Handle midnight cross if log_date is the same but times wrap
                    if ($parsedOut < $parsedIn) {
                        $parsedOut += 86400;
                    }

                    $minutes = ($parsedOut - $parsedIn) / 60;
                    $totalMinutes += $minutes;

                    $logsJson[] = [
                        "in"            => date("H:i", $parsedIn),
                        "out"           => date("H:i", $parsedOut),
                        "device_in"     => $currentLog->device->name ?? "---",
                        "device_out"    => $nextLog->device->name ?? "---",
                        "total_minutes" => (int)$minutes,
                    ];
                    $i += 2;
                } else {
                    // Odd numbered log (Entry with no Exit)
                    $logsJson[] = [
                        "in"            => date("H:i", strtotime($inTimeRaw)),
                        "out"           => "---",
                        "device_in"     => $currentLog->device->name ?? "---",
                        "device_out"    => "---",
                        "total_minutes" => 0,
                    ];
                    $i += 1;
                }
            }

            // 3. Prepare result item
            $item = [
                "employee_id"   => $row->system_user_id,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $params["shift"]->id ?? 0,
                "shift_type_id" => $params["shift"]->shift_type_id ?? 0,
                "total_hrs"     => $this->minutesToHours($totalMinutes),
                "in"            => isset($logsJson[0]) ? $logsJson[0]['in'] : "---",
                "out"           => count($logsJson) > 0 ? (end($logsJson)['out'] !== "---" ? end($logsJson)['out'] : "---") : "---",
                "status"        => ($count > 0 && $count % 2 !== 0) ? Attendance::MISSING : ($count > 0 ? Attendance::PRESENT : $status),
                "logs"          => json_encode($logsJson),
            ];

            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
            }

            $items[] = $item;
        }

        // 4. Bulk Save
        if (count($items) > 0) {
            Attendance::where("company_id", $id)
                ->where("date", $date)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->delete();

            foreach (array_chunk($items, 100) as $chunk) {
                Attendance::insert($chunk);
            }

            // Update logs to checked
            AttendanceLog::where("company_id", $id)
                ->whereIn("UserID", $UserIds)
                ->where("log_date", $date)
                ->update([
                    "checked" => true,
                    "checked_datetime" => date('Y-m-d H:i:s'),
                    "channel" => $channel
                ]);
        }

        return "[" . $date . " " . date("H:i:s") . "] Processed " . count($items) . " records.";
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
