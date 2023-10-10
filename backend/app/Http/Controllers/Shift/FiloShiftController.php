<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;

class FiloShiftController extends Controller
{
    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        $endDateString = $request->dates[1];
        $company_id = $request->company_ids[0];
        $employee_ids = $request->employee_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $response = [];

        while ($startDate <= $currentDate && $startDate <= $endDate) {
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 1, $employee_ids, true);
            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"), $request->shift_type_id, $request->UserIds, true);
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false)
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

        // return json_encode($params);


        $logsEmployees =  (new AttendanceLog)->getLogsForRender($params);

        $items = [];

        foreach ($logsEmployees as $key => $logs) {

            $logs = $logs->toArray() ?? [];

            $firstLog = $logs[0];
            $lastLog = $logs[count($logs) - 1];

            $schedule = $firstLog["schedule"] ?? false;
            $shift = $schedule["shift"] ?? false;

            if (!$schedule) continue;

            $item = [
                "roster_id" => 0,
                "total_hrs" => "---",
                "in" => $firstLog["time"] ?? "---",
                "out" =>  "---",
                "ot" => "---",
                "device_id_in" =>  $firstLog["DeviceID"] ?? "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "company_id" => $params["company_id"],
                "employee_id" => $key,
                "shift_id" => $firstLog["schedule"]["shift_id"] ?? 0,
                "shift_type_id" => $firstLog["schedule"]["shift_type_id"] ?? 0,
                "status" => "M",
            ];

            if ($firstLog["log_type"] == "in") {
                $item["in"] = $firstLog["log_type"] == "in" ? $firstLog["time"] : "---";
                $item["device_id_in"] = $firstLog["log_type"] == "in" ? $firstLog["DeviceID"] : "---";
            }

            if ($shift && $item["shift_type_id"] == 6) {
                $item["late_coming"] =  $this->calculatedLateComing($item["in"], $shift["on_duty_time"], $shift["late_time"]);

                if ($item["late_coming"] != "---") {
                    $item["status"] = "LC";
                }
            }

            if ($shift && $lastLog && count($logs) > 1) {
                $item["status"] = "P";
                $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                $item["out"] = $lastLog["time"] ?? "---";

                if (in_array($lastLog["log_type"], ["in", "out"])) {
                    $item["device_id_out"] = $lastLog["log_type"] == "out" ? $lastLog["DeviceID"] : "---";
                    $item["out"] = $lastLog["log_type"] == "out" ? $lastLog["time"] : "---";
                }
                $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);

                if ($schedule["isOverTime"] ?? false) {
                    $item["ot"] = $this->calculatedOT($item["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                }

                if ($item["shift_type_id"] == 6) {
                    $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $shift["off_duty_time"], $shift["early_time"]);

                    if ($item["early_going"] != "---") {
                        $item["status"] = "EG";
                    }
                }
            }
            $items[] = $item;
        }

        if (!count($items)) {
            return '[' . $date . " " . date("H:i:s") . '] Filo Shift: No data found';
        }

        try {
            $UserIds = array_column($items, "employee_id");
            $model = Attendance::query();
            $model->where("company_id", $id);
            $model->whereIn("employee_id", $UserIds);
            $model->where("date", $date);
            $model->delete();
            $model->insert($items);

            if (!$custom_render) {
                AttendanceLog::where("company_id", $id)->whereIn("UserID", $UserIds)->update(["checked" => true]);
            }

            return "[" . $date . " " . date("H:i:s") .  "] Filo Shift. Log(s) have been rendered. Affected Ids: " . json_encode($UserIds);
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") .  "] Filo Shift. " . $e->getMessage();
            info($message);

            return "[" . $date . " " . date("H:i:s") .  "] Filo Shift. Server Error";
        }
    }
}
