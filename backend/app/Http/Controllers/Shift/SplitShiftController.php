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
            // Night shift logic usually requires fetching logs from the next day as well
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        $items       = [];
        $message     = "";
        $logsUpdated = 0;

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift ?? false;

            if (!$params["shift"]) {
                $message .= "{$row->system_user_id} : No shift configured on date: $date; ";
                continue;
            }

            $allLogs = (new AttendanceLog)->getLogsWithInRangeNew($params);
            $userData = $allLogs[$row->system_user_id] ?? [];

            // Clean duplicates and format collection
            $data = collect($userData)
                ->unique(function ($item) {
                    return $item['LogTime'] . $item['log_type'];
                })
                ->values();

            $logsJson     = [];
            $totalMinutes = 0;
            $i = 0;
            $count = count($data);

            while ($i < $count) {
                $currentLog = $data[$i];
                $logType = strtolower($currentLog['log_type'] ?? '');

                // 1. Identify an "In" point. 
                // We accept 'in' specifically, or 'auto' if it's the start of a sequence.
                if (!in_array($logType, ['in', 'auto', 'option', 'mobile'])) {
                    $i++;
                    continue;
                }

                $inTime = $currentLog['time'] ?? '---';
                $deviceIn = $this->getDeviceName($currentLog, ["In", "Auto", "Option", "Mobile"]);

                $outTime = "---";
                $deviceOut = "---";
                $minutes = 0;
                $foundOut = false;

                // 2. Look ahead for the corresponding "Out"
                for ($j = $i + 1; $j < $count; $j++) {
                    $nextLog = $data[$j];
                    $nextType = strtolower($nextLog['log_type'] ?? '');

                    // If we hit another "In" before an "Out", the current "In" is a missing-out case
                    if ($nextType === 'in') {
                        break;
                    }

                    if (in_array($nextType, ['out', 'auto', 'option', 'mobile'])) {
                        $outTime = $nextLog['time'] ?? '---';
                        $deviceOut = $this->getDeviceName($nextLog, ["Out", "Auto", "Option", "Mobile"]);

                        // Calculation logic
                        $parsedIn  = strtotime($inTime);
                        $parsedOut = strtotime($outTime);

                        if ($parsedIn > $parsedOut) {
                            $parsedOut += 86400; // Handle midnight crossover
                        }

                        $minutes = ($parsedOut - $parsedIn) / 60;
                        $totalMinutes += $minutes;

                        $foundOut = true;
                        $i = $j; // Move main pointer to this 'Out' log
                        break;
                    }
                }

                $logsJson[] = [
                    "in"            => $inTime,
                    "out"           => $outTime,
                    "device_in"     => $deviceIn,
                    "device_out"    => $deviceOut,
                    "total_minutes" => $minutes,
                ];

                $i++; // Move to next potential 'In'
            }

            // Determine Status
            $dayOfWeekThreeLetter = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';

            // Pass the first log to the week-off processor
            $status = Attendance::processWeekOffFunc(
                $currentDayKey,
                $params["shift"]['weekoff_rules'] ?? "A",
                $id,
                $date,
                $row->system_user_id,
                $data->first()
            ) ?? "A";

            // If no logs, status is usually Absent; if odd logs (missing out), mark Missing
            if (count($logsJson) === 0) {
                $finalStatus = $status; // Likely 'A' or 'W'
            } else {
                $hasMissingOut = collect($logsJson)->contains('out', '---');
                $finalStatus = $hasMissingOut ? Attendance::MISSING : Attendance::PRESENT;
            }

            $item = [
                "employee_id"   => $row->system_user_id,
                "date"          => $date,
                "company_id"    => $id,
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                "status"        => $finalStatus,
                "in"            => $logsJson[0]['in'] ?? "---",
                "out"           => end($logsJson)['out'] ?? "---",
                "total_hrs"     => $this->minutesToHours($totalMinutes),
                "ot"            => "---",
                "logs"          => json_encode($logsJson),
            ];

            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT(
                    $item["total_hrs"],
                    $params["shift"]->working_hours,
                    $params["shift"]->overtime_interval
                );
            }

            $items[] = $item;
        }

        // --- Database Operations ---
        $items = collect($items)->unique('employee_id')->values()->all();

        try {
            if (count($items) > 0) {
                $employeeIds = array_column($items, "employee_id");

                Attendance::whereIn("employee_id", $employeeIds)
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->delete();

                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::insert($chunk);
                }

                $message = "[{$date} " . date("H:i:s") . "] Multi Shift. Affected Ids: " . json_encode($UserIds) . " " . $message;

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

        $this->logOutPut($this->logFilePath, "[$date] $logsUpdated logs updated. $message");
        return "[$date " . date("H:i:s") . "] $message";
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
