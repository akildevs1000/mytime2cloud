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


    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel)
    {
        $params = [
            "company_id"    => $id,
            "date"          => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds"       => $UserIds,
        ];

        // Fetch Target Employee IDs
        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        // If no employee found via standard render, fetch via shift details
        if (count($employees) == 0) {
            $employees = (new Employee)->GetEmployeeWithShiftDetails($params);
        }

        $items = [];

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift ?? false;

            // --- INTEGRATED YOUR QUERY HERE ---
            $logs = AttendanceLog::with('device')
                ->where("company_id", $id)
                ->where("UserID", $row->system_user_id)
                ->whereDate('LogTime', $date)
                ->orderBy("LogTime", 'asc')
                ->get();

            // Convert to a flat array for pairing
            $data = $logs->values();

            // Handle Status (Weekoff/Holiday/Present)
            $dayOfWeekThreeLetter = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';
            $status = Attendance::processWeekOffFunc($currentDayKey, $params["shift"]['weekoff_rules'] ?? "A", $id, $date, $row->system_user_id, $data->first());


            if ($params["shift"]) {
                $params["shift"] = Attendance::processHalfDay($currentDayKey,  $params["shift"]['halfday_rules'] ?? null, $params["shift"]);
            }


            // If NO logs exist, mark status and skip
            if ($data->isEmpty()) {
                Attendance::where("employee_id", $row->system_user_id)
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->update(["status" => $status ?? "A"]);
                continue;
            }

            $logsJson = [];
            $totalMinutes = 0;

            // PAIRING LOGIC: Treat 1st as IN, 2nd as OUT regardless of "Auto" label
            for ($i = 0; $i < count($data); $i += 2) {
                $inLog  = $data[$i];
                $outLog = $data[$i + 1] ?? null;

                // Extract time from LogTime string (e.g., "2026-03-02 09:05:00" -> "09:05")
                $inTime  = $inLog->LogTime ? date("H:i", strtotime($inLog->LogTime)) : "---";
                $outTime = ($outLog && $outLog->LogTime) ? date("H:i", strtotime($outLog->LogTime)) : "---";

                $minutes = 0;
                if ($outLog && $inTime !== "---" && $outTime !== "---") {
                    $parsedIn  = strtotime($inLog->LogTime);
                    $parsedOut = strtotime($outLog->LogTime);

                    if ($parsedIn > $parsedOut) {
                        $parsedOut += 86400; // Handling midnight cross
                    }
                    $minutes = ($parsedOut - $parsedIn) / 60;
                    $totalMinutes += $minutes;
                }

                $logsJson[] = [
                    "in"            => $inTime,
                    "out"           => $outTime,
                    "device_in"     => $inLog->device->name ?? '---',
                    "device_out"    => $outLog->device->name ?? '---',
                    "total_minutes" => $minutes,
                ];
            }

            $totalHrs = $this->minutesToHours($totalMinutes);

            // Prepare the Attendance Record
            $item = [
                "employee_id"   => $row->system_user_id,
                "date"          => $date,
                "company_id"    => $id,
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                // First punch of the day
                "in"            => $logsJson[0]["in"] ?? "---",
                // Last punch of the day (if more than one exists)
                "out"           => (count($logsJson) > 0 && end($logsJson)["out"] !== "---") ? end($logsJson)["out"] : "---",
                "total_hrs"     => $totalHrs,
                "status"        => $status ?? (count($data) % 2 !== 0 ? Attendance::MISSING : Attendance::PRESENT),
                "logs"          => json_encode($logsJson),
            ];

            // Overtime Calculation
            if ($params["isOverTime"] && isset($params["shift"]->working_hours)) {
                $item["ot"] = $this->calculatedOT($totalHrs, $params["shift"]->working_hours, $params["shift"]->overtime_interval);
            }

            $items[] = $item;
        }

        // Database Sync
        if (!empty($items)) {
            try {
                $empIds = array_column($items, "employee_id");

                // Clean up existing records for these users on this date
                Attendance::whereIn("employee_id", $empIds)
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->delete();

                // Insert new records
                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::insert($chunk);
                }

                // Mark logs as processed in the raw logs table
                AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", $UserIds)
                    ->whereDate("LogTime", $date)
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel
                    ]);

                return "[" . $date . "] Processed " . count($items) . " records.";
            } catch (\Throwable $e) {
                return "Error: " . $e->getMessage();
            }
        }

        return "No records processed for " . $date;
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
