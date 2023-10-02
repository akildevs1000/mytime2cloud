<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleEmployee;

class ShiftRenderController extends Controller
{
    public function renderRequest(Request $request)
    {
        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"));
    }
    public function render($id, $date)
    {
        $params = ["company_id" => $id, "date" => $date];
        $employees =  (new Employee)->attendanceEmployee($params);
        $logs =  (new AttendanceLog)->getLogs($params);

        $items = [];


        foreach ($employees as $row) {

            $params["logs"] = $logs[$row->system_user_id] ?? [];

            if (!$params["logs"]) {
                continue;
            }

            $params["system_user_id"] = $row->system_user_id;
            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"] = $row->schedule->shift ?? false;

            if ($params["shift"]["shift_type_id"] == 6) {
                $items[] = $this->processSingle($params);
            }
            if ($params["shift"]["shift_type_id"] == 1) {
                $items[] = $this->processFilo($params);
            }
        }
        return $items;

        // $model = Attendance::query();
        // $model->where("company_id", $params["company_id"]);
        // $model->whereIn("employee_id", array_column($items, "employee_id"));
        // $model->where("date", $date);
        // $model->delete();
        // return $model->insert($items);
    }

    public function processFilo($params)
    {
        $firstLog = $params["logs"]->first();
        $lastLog = $params["logs"]->last();

        $item = $this->getDefaultCols($params);

        if ($firstLog && $firstLog["log_type"] == "in") {
            $item["in"] = $firstLog["time"];
            $item["device_id_in"] = $firstLog["DeviceID"];
        }

        if ($lastLog && $lastLog["log_type"] == "out" && count($params["logs"]) > 1) {
            $item["status"] = "P";
            $item["device_id_out"] = $lastLog["DeviceID"];
            $item["out"] = $lastLog["time"];
            $item["total_hrs"] = $this->getTotalHrsMins($firstLog["time"], $lastLog["time"]);

            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
            }
        }

        return $item;
    }

    public function getDefaultCols($params)
    {
        return [
            "total_hrs" => "---",
            "in" => "---",
            "out" => "---",
            "ot" => "---",
            "device_id_in" => "---",
            "device_id_out" => "---",
            "date" => $params["date"],
            "company_id" => $params["company_id"],
            "employee_id" => $params["system_user_id"],
            "shift_id" => $params["shift"]["id"] ?? 0,
            "shift_type_id" => $params["shift"]["shift_type_id"]  ?? 0,
            "status" => "M",
        ];
    }
}
