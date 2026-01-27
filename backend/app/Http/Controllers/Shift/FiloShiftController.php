<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\API\SharjahUniversityAPI;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use App\Models\Shift;

class FiloShiftController extends Controller
{
    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        //$endDateString = $request->dates[1];
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
            //$response[] = $this->render($company_id, $startDate->format("Y-m-d"), 1, $employee_ids, true);
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 1, $employee_ids, $request->filled("auto_render") ? false : true, $request->channel ?? "unknown");

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"), $request->shift_type_id, $request->UserIds, true, $request->channel ?? "unknown");
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel = "unknown")
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsToRender($params);
        }

        $logsEmployees = (new AttendanceLog)->getLogsForRender($params);

        // Update attendance table with shift ID if shift with employee not found 
        if (count($logsEmployees) == 0) {
            $employees = (new Employee())->GetEmployeeWithShiftDetails($params);
            foreach ($employees as $key => $value) {
                if (isset($value->schedule->shift) && $value->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id" => $value->schedule->shift["id"],
                        "shift_type_id" => $value->schedule->shift["shift_type_id"]
                    ];
                    Attendance::whereIn("employee_id", $UserIds)
                        ->where("date", $params["date"])
                        ->where("company_id", $params["company_id"])
                        ->update($data1);
                }
            }
        }

        $items = [];
        $message = "";

        foreach ($logsEmployees as $key => $logsGroup) {
            $logsArray = $logsGroup->toArray() ?? [];

            // 1. Determine the Shift Boundaries
            $firstValidRecord = collect($logsArray)->first();
            $schedule = $firstValidRecord["schedule"] ?? false;
            $shift = $schedule["shift"] ?? false;

            if (!$shift) {
                $message .= ". No shift mapped for User: $key on " . $params["date"];
                continue;
            }

            // Logic to define shift range (handle overnight shifts)
            $onDutyStr = $params["date"] . ' ' . $shift["on_duty_time"];
            $offDutyStr = $params["date"] . ' ' . $shift["off_duty_time"];

            // If off_duty is earlier than on_duty, it ends the next day
            if (strtotime($shift["off_duty_time"]) < strtotime($shift["on_duty_time"])) {
                $offDutyStr = date("Y-m-d H:i:s", strtotime($offDutyStr . " +1 day"));
            }

            // 2. Filter logs that only fall within this shift range
            $filteredLogs = collect($logsArray)->filter(function ($record) use ($onDutyStr, $offDutyStr) {
                return $record['LogTime'] >= $onDutyStr && $record['LogTime'] <= $offDutyStr;
            });

            if ($filteredLogs->isEmpty()) {
                continue;
            }

            $firstLog = $filteredLogs->first(function ($record) {
                return !in_array(strtolower($record['log_type']), ['out'], true);
            });

            $lastLog = $filteredLogs->last(function ($record) {
                return !in_array(strtolower($record['log_type']), ['in'], true);
            });

            // 3. Prepare the Item
            $item = [
                "roster_id" => 0,
                "total_hrs" => "---",
                "in" => $firstLog["time"] ?? "---",
                "out" => "---",
                "ot" => "---",
                "device_id_in" => $firstLog["DeviceID"] ?? "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "company_id" => $params["company_id"],
                "employee_id" => $key,
                "shift_id" => $shift["id"] ?? 0,
                "shift_type_id" => $shift["shift_type_id"] ?? 0,
                "status" => "M", // Default to Missing
                "late_coming" => "---",
                "early_going" => "---",
            ];

            // Handle Late Coming (Shift Type 6 example)
            if ($item["shift_type_id"] == 6 && $item["in"] !== "---") {
                $item["late_coming"] = $this->calculatedLateComing($item["in"], $shift["on_duty_time"], $shift["late_time"]);
                if ($item["late_coming"] != "---") {
                    $item["status"] = "LC";
                }
            }

            // Handle Check Out and Total Hours
            if ($lastLog && $filteredLogs->count() > 1) {
                $item["status"] = ($item["status"] == "LC") ? "LC" : "P";
                $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                $item["out"] = $lastLog["time"] ?? "---";

                if ($item["out"] !== "---") {
                    if (strtotime($shift["on_duty_time"]) > strtotime($shift["off_duty_time"])) {
                        // Use the full LogTime strings which already have the +1 day logic applied to $offDutyStr
                        // We use the raw LogTime from the records to ensure date crossing is captured
                        $item["total_hrs"] = $this->getTotalHrsMins($firstLog["LogTime"], $lastLog["LogTime"]);
                    } else {
                        // Standard same-day calculation
                        $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                    }
                }

                // OT Calculation
                if (($schedule["isOverTime"] ?? false) && isset($shift["working_hours"])) {
                    $item["ot"] = $this->calculatedOT($item["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                }

                // Early Going
                if ($item["shift_type_id"] == 6 && $item["out"] !== "---") {
                    $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $shift["off_duty_time"], $shift["early_time"]);
                    if ($item["early_going"] != "---") {
                        $item["status"] = "EG";
                    }
                }
            }

            $items[] = $item;
        }

        // 4. Save to Database
        if (!count($items)) {
            $message = '[' . $date . " " . date("H:i:s") . '] No valid logs within shift range. ' . $message;
            $this->devLog("render-manual-log", $message);
            return $message;
        }

        try {
            Attendance::where("company_id", $id)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->where("date", $date)
                ->delete();

            Attendance::insert($items);
            $message = "[" . $date . " " . date("H:i:s") .  "] Filo Shift.  Affected Ids: " . json_encode($UserIds) . " " . $message;
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") .  "] Filo Shift. " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }

    public function getTotalHrsMins($first, $last)
    {
        // If these strings include the date (e.g. "2026-01-27 15:01:00"), 
        // it will calculate overnight shifts perfectly.
        $start = new \DateTime($first);
        $end = new \DateTime($last);

        // If the end time is actually before the start time (same day error),
        // and you KNOW it's an overnight shift, you could manually add a day,
        // but it's better to pass the full date-time string from the start.
        $diff = $start->diff($end);

        // %H ensures 0-padding for hours, %I for minutes
        return $diff->format('%H:%I');
    }
}
