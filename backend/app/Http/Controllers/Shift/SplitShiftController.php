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
            // Use the Night version specifically
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        // This is the query you shared. If it returns an empty collection, 
        // nothing below will execute.
        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        $items = [];
        $message = "";

        foreach ($employees as $row) {
            // Safety check for schedule
            if (!$row->schedule || !$row->schedule->shift) {
                $message .= "ID: {$row->system_user_id} skipped - No Schedule/Shift found; ";
                continue;
            }

            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift;

            // Fetch logs - Ensure this method returns ALL logs for the user
            $all_logs = (new AttendanceLog)->getLogsWithInRangeNew($params);
            $user_logs = $all_logs[$row->system_user_id] ?? [];

            // 1. UNIQUE BY TIME & SORT CHRONOLOGICALLY
            $data = collect($user_logs)
                ->unique('LogTime')
                ->sortBy('LogTime')
                ->values()
                ->toArray();

            $logCount = count($data);
            $logsJson = [];
            $totalMinutes = 0;

            // 2. PAIR-WISE PROCESSING (Ignore LogType)
            for ($i = 0; $i < $logCount; $i += 2) {
                $logA = $data[$i];
                $logB = $data[$i + 1] ?? null;

                $timeA = isset($logA['LogTime']) ? date('H:i', strtotime($logA['LogTime'])) : "---";
                $timeB = ($logB && isset($logB['LogTime'])) ? date('H:i', strtotime($logB['LogTime'])) : "---";

                $diff = 0;
                if ($logB) {
                    $t1 = strtotime($logA['LogTime']);
                    $t2 = strtotime($logB['LogTime']);
                    if ($t1 > $t2) $t2 += 86400; // Midnight handle
                    $diff = ($t2 - $t1) / 60;
                    $totalMinutes += $diff;
                }

                $logsJson[] = [
                    "in"            => $timeA,
                    "out"           => $timeB,
                    "device_in"     => $logA['DeviceID'] ?? "---",
                    "device_out"    => $logB ? ($logB['DeviceID'] ?? "---") : "---",
                    "total_minutes" => $diff,
                ];
            }

            // 3. THE "MISSING" RULE: Any odd count (1, 3, 5) = Missing
            // We also check if logsJson is empty
            $status = ($logCount % 2 !== 0 || $logCount === 0)
                ? Attendance::MISSING
                : Attendance::PRESENT;

            $totalHrs = $this->minutesToHours($totalMinutes);

            $items[] = [
                "employee_id"   => $row->system_user_id,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $params["shift"]["id"] ?? 0,
                "shift_type_id" => $params["shift"]["shift_type_id"] ?? 0,
                "total_hrs"     => $totalHrs,
                "status"        => $status,
                "logs"          => json_encode($logsJson),
                "in"            => $logsJson[0]['in'] ?? "---",
                "out"           => (count($logsJson) > 0) ? end($logsJson)['out'] : "---",
                "ot"            => $params["isOverTime"] ? $this->calculatedOT($totalHrs, $params["shift"]->working_hours, $params["shift"]->overtime_interval) : "00:00"
            ];
        }

        // 4. SYNC TO DATABASE
        if (count($items) > 0) {
            $affectedIds = array_column($items, "employee_id");

            Attendance::whereIn("employee_id", $affectedIds)
                ->where("date", $date)
                ->where("company_id", $id)
                ->delete();

            foreach (array_chunk($items, 100) as $chunk) {
                Attendance::insert($chunk);
            }

            AttendanceLog::where("company_id", $id)
                ->whereIn("UserID", $affectedIds)
                ->whereDate("LogTime", $date)
                ->update([
                    "checked"          => true,
                    "checked_datetime" => date('Y-m-d H:i:s'),
                    "channel"          => $channel
                ]);
        }

        return "Processed " . count($items) . " records for " . $date . ". Errors: " . $message;
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
