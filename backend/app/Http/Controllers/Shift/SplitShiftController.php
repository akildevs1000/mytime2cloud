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

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        $items = [];
        $message = "";
        $logsUpdated = 0;

        foreach ($employees as $row) {
            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"]      = $row->schedule->shift ?? false;

            $logs = (new AttendanceLog)->getLogsWithInRangeNew($params);

            // 1. Get logs and REMOVE duplicates based on time only
            $data = $logs[$row->system_user_id] ?? [];
            $data = collect($data)->unique('LogTime')->values()->toArray();

            $logCount = count($data);

            // 2. Determine Status: Missing if log count is odd (1, 3, 5...)
            $status = ($logCount % 2 !== 0) ? Attendance::MISSING : Attendance::PRESENT;

            if (!$params["shift"]["id"]) {
                $message .= "{$row->system_user_id} : No shift configured on date: $date";
                continue;
            }

            $totalMinutes = 0;
            $logsJson = [];

            // 3. Pair-wise processing without log_type filtering
            for ($i = 0; $i < $logCount; $i += 2) {
                $inLog = $data[$i];
                $outLog = $data[$i + 1] ?? null; // May be null if odd number

                $inTime = $inLog['time'] ?? '---';
                $outTime = $outLog ? ($outLog['time'] ?? '---') : '---';

                $minutes = 0;
                if ($outLog) {
                    $parsedIn = strtotime($inTime);
                    $parsedOut = strtotime($outTime);

                    if ($parsedIn > $parsedOut) {
                        $parsedOut += 86400; // Handle midnight crossing
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

            // 4. Build the final item
            $item = [
                "employee_id"   => $row->system_user_id,
                "date"          => $params["date"],
                "company_id"    => $params["company_id"],
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                "total_hrs"     => $this->minutesToHours($totalMinutes),
                "status"        => $status,
                "logs"          => json_encode($logsJson, JSON_PRETTY_PRINT),
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

        // Database operations
        try {
            if (count($items) > 0) {
                $model = Attendance::query();
                $model->whereIn("employee_id", array_column($items, "employee_id"))
                    ->where("date", $date)
                    ->where("company_id", $id)
                    ->delete();

                foreach (array_chunk($items, 100) as $chunk) {
                    Attendance::insert($chunk);
                }

                $logsUpdated = AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", array_column($items, "employee_id"))
                    ->where("LogTime", ">=", $date)
                    ->where("LogTime", "<=", date("Y-m-d", strtotime($date . "+1 day")))
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel,
                    ]);
            }
        } catch (\Throwable $e) {
            $this->logOutPut($this->logFilePath, $e->getMessage());
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
