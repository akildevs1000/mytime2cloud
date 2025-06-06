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

        $logsEmployees =  (new AttendanceLog)->getLogsForRender($params);

        //update atendance table with shift ID if shift with employee not found 
        if (count($logsEmployees) == 0) {
            $employees = (new Employee())->GetEmployeeWithShiftDetails($params);
            foreach ($employees as $key => $value) {
                if ($value->schedule->shift && $value->schedule->shift["id"] > 0) {
                    $data1 = [
                        "shift_id" => $value->schedule->shift["id"],
                        "shift_type_id" => $value->schedule->shift["shift_type_id"]
                    ];
                    $model1 = Attendance::query();
                    $model1->whereIn("employee_id", $UserIds);
                    $model1->where("date", $params["date"]);
                    $model1->where("company_id", $params["company_id"]);
                    $model1->update($data1);
                }
            }
        }

        $items = [];
        $message = "";
        foreach ($logsEmployees as $key => $logs) {

            $logs = $logs->toArray() ?? [];


            $firstLog = collect($logs)->first(function ($record) {
                return !in_array(strtolower($record['log_type']), ['out'], true);
            });

            $lastLog = collect($logs)->last(function ($record) {
                return !in_array(strtolower($record['log_type']), ['in'], true);
            });

            $schedules = ScheduleEmployee::where("company_id", $params["company_id"])->where("employee_id", $key)->get()->toArray();

            $schedule = $firstLog["schedule"] ?? false;
            $shift = $schedule["shift"] ?? false;

            if (!$schedule) {
                $message .= ".  No schedule is mapped with combination  System User Id: $key   and Date : " . $params["date"] . " ";
                continue;
            }

            $dayOfWeek = date('D', strtotime($firstLog["LogTime"])); // Convert to timestamp and get the day

            foreach ($schedules as $singleSchedule) {
                $day = $singleSchedule["shift"]["days"];

                if (isset($shift["days"]) && is_array($shift["days"]) && in_array($dayOfWeek, $day, true)) {
                    $schedule = $singleSchedule ?? false;
                    $shift =  $schedule["shift"] ?? false;
                    break;
                }
            }


            if (!$firstLog["schedule"]["shift_type_id"]) {
                $message .= "$key : None f=of the  Master shift configured on  date:" . $params["date"];
                continue;
            }


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
                "late_coming" => "---",
                "early_going" => "---",
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

                if ($schedule["isOverTime"] ?? false && isset($shift["working_hours"])) {
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
            $message = '[' . $date . " " . date("H:i:s") . '] Filo Shift: No data found' . $message;
            $this->devLog("render-manual-log", $message);
            return $message;
        }

        try {
            $model = Attendance::query();
            $model->where("company_id", $id);
            $model->whereIn("employee_id", array_column($items, "employee_id"));
            $model->where("date", $date);
            $model->delete();
            $model->insert($items);
            $message = "[" . $date . " " . date("H:i:s") .  "] Filo Shift.  Affected Ids: " . json_encode($UserIds) . " " . $message;
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") .  "] Filo Shift. " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return ($message);
    }
}
