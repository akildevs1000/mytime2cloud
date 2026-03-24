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



    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel)
    {
        $params = [
            "company_id"    => $id,
            "date"          => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds"       => $UserIds,
        ];

        // Fetch UserIds if not provided
        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        $items = [];
        $message = "";
        $logsUpdated = 0;

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift ?? false;

            if (!$params["shift"]) {
                $message .= "{$row->system_user_id} : No shift configured on date: $date; ";
                continue;
            }

            // Get logs for this specific employee
            $all_logs = (new AttendanceLog)->getLogsWithInRangeNew($params);
            $user_logs = $all_logs[$row->system_user_id] ?? [];

            // 1. REMOVE DUPLICATES (Same Time) and SORT chronologically
            $data = collect($user_logs)
                ->unique('LogTime')
                ->sortBy('LogTime')
                ->values()
                ->toArray();

            $logCount = count($data);
            $totalMinutes = 0;
            $logsJson = [];

            // 2. PAIR-WISE PROCESSING (Ignore labels, just pair Log 1 with 2, 3 with 4...)
            for ($i = 0; $i < $logCount; $i += 2) {
                $inLog  = $data[$i];
                $outLog = $data[$i + 1] ?? null; // Null if odd number (e.g., 3rd log)

                $inTime  = $inLog['time'] ?? '---';
                $outTime = $outLog ? ($outLog['time'] ?? '---') : '---';

                $minutes = 0;
                if ($outLog) {
                    $parsedIn  = strtotime($inLog['LogTime']);
                    $parsedOut = strtotime($outLog['LogTime']);

                    // Handle potential midnight crossing
                    if ($parsedIn > $parsedOut) {
                        $parsedOut += 86400;
                    }

                    $minutes = ($parsedOut - $parsedIn) / 60;
                    $totalMinutes += $minutes;
                }

                $logsJson[] = [
                    "in"            => $inTime,
                    "out"           => $outTime,
                    "device_in"     => $inLog['DeviceID'] ?? "---",
                    "device_out"    => $outLog ? ($outLog['DeviceID'] ?? "---") : "---",
                    "total_minutes" => $minutes,
                ];
            }

            // 3. STATUS LOGIC: Missing for 1, 3, 5, 7 logs
            $status = ($logCount % 2 !== 0 || $logCount === 0)
                ? Attendance::MISSING
                : Attendance::PRESENT;

            // 4. PREPARE ITEM FOR INSERT
            $totalHrs = $this->minutesToHours($totalMinutes);

            $item = [
                "employee_id"   => $row->system_user_id,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                "total_hrs"     => $totalHrs,
                "status"        => $status,
                "logs"          => json_encode($logsJson),
                "in"            => $logsJson[0]["in"] ?? "---",
                "out"           => (count($logsJson) > 0) ? end($logsJson)["out"] : "---",
            ];

            // Handle Overtime
            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT(
                    $totalHrs,
                    $params["shift"]->working_hours,
                    $params["shift"]->overtime_interval
                );
            }

            $items[] = $item;
        }

        // 5. DATABASE SYNC
        try {
            if (count($items) > 0) {
                $model = Attendance::query();

                // Clean up existing records for these users on this date
                $model->whereIn("employee_id", array_column($items, "employee_id"))
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->delete();

                // Insert new processed records in chunks
                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::insert($chunk);
                }

                // Mark logs as checked
                $logsUpdated = AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", array_column($items, "employee_id"))
                    ->where("LogTime", ">=", $date)
                    ->where("LogTime", "<=", date("Y-m-d", strtotime($date . "+1 day")))
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel,
                        "log_message"      => "Multi-render processed: " . count($items) . " records",
                    ]);
            }
        } catch (\Throwable $e) {
            $this->logOutPut($this->logFilePath, "Error in render: " . $e->getMessage());
        }

        return "[" . $date . " " . date("H:i:s") . "] " . count($items) . " employees rendered. Status: " . $message;
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
