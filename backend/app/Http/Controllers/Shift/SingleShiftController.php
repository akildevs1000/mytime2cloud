<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;

class SingleShiftController extends Controller
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

        while ($startDate <= $currentDate && $startDate <= $endDate) {
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 6, $employee_ids, true);
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
            // return json_encode($params["UserIds"]);
        }



        $logsEmployees =  (new AttendanceLog)->getLogsForRender($params);

        $items = [];

        foreach ($logsEmployees as $key => $logs) {

            $logs = $logs->toArray() ?? [];

            // $firstLog = collect($logs)->filter(fn ($record) => $record['log_type'] !== "out")->first();
            // $lastLog = collect($logs)->filter(fn ($record) => $record['log_type'] !== "in")->last();

            // $firstLog = collect($logs)->filter(function ($record) {
            //     return isset($record["device"]["function"]) && ($record["device"]["function"] == "In" || $record["device"]["function"] == "auto");
            // })->first();

            // $lastLog = collect($logs)->filter(function ($record) {
            //     return isset($record["device"]["function"]) && ($record["device"]["function"] == "Out" || $record["device"]["function"] == "auto");
            // })->first();

            $firstLog = collect($logs)->filter(function ($record) {
                return isset($record["device"]["function"]) && ($record["device"]["function"] != "Out");
            })->first();

            $lastLog = collect($logs)->filter(function ($record) {
                return isset($record["device"]["function"]) && ($record["device"]["function"] != "In");
            })->last();



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

                if ($item["out"] !== "---") {
                    $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                }

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
            $message = '[' . $date . " " . date("H:i:s") . '] Single Shift: No data found';
            $this->devLog("render-manual-log", $message);
            return $message;
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

            $message = "[" . $date . " " . date("H:i:s") .  "] Single Shift.   Affected Ids: " . json_encode($UserIds);
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") .  "] Single Shift. " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }
}
