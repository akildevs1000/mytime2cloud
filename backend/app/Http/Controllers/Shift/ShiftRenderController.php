<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ScheduleEmployee;

class ShiftRenderController extends Controller
{
    public function renderRequest(Request $request)
    {
        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"));
    }
    public function getCompanyIds(Request $request)
    {
        $date = date("Y-m-d");
        $companies = Company::where("shift_type", 1)->pluck("id");
        foreach ($companies as $company) {
            $this->render($company->id, $date);
        }
    }

    public function render($id, $date)
    {
        $params = ["company_id" => $id, "date" => $date, "shift_type_id" => 1];

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

            if ($row->schedule->shift_type_id == 1) {
                $items[] = $this->processFilo($params);
            }
            if ($row->schedule->shift_type_id == 6) {
                $items[] = $this->processFilo($params);
            }
        }

        if (count($items)) {
            $model = Attendance::query();
            $model->whereIn("company_id", array_column($items, "company_id"));
            $model->whereIn("employee_id", array_column($items, "employee_id"));
            $model->where("date", $date);
            $model->delete();
            $model->insert($items);
            AttendanceLog::whereIn("UserID", array_column($items, "employee_id"))->whereIn("company_id", array_column($items, "company_id"))->update(["checked" => true]);

            return "(Filo Shift) " . $date . ": Log(s) has been render. Affected Ids: " . json_encode(array_column($items, "employee_id")) . " Affected Company_id Ids: " . json_encode(array_column($items, "company_id"));
        }

        return json_encode(([]));
    }

    public function processFilo($params)
    {
        $firstLog = $params["logs"]->first();
        $lastLog = $params["logs"]->last();

        $item = $this->getDefaultCols($params);

        if ($firstLog && ($firstLog["log_type"] == "in" || $firstLog["log_type"] == "auto")) {
            $item["in"] = $firstLog["time"];
            $item["device_id_in"] = $firstLog["DeviceID"];
        }

        if ($lastLog && count($params["logs"]) > 1 && ($lastLog["log_type"] == "out" || $lastLog["log_type"] == "auto")) {
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

    public function processSingle($params)
    {
        $firstLog = $params["logs"]->first();
        $lastLog = $params["logs"]->last();

        $item = $this->getDefaultCols($params);

        if ($firstLog && ($firstLog["log_type"] == "in" || $firstLog["log_type"] == "auto")) {
            $items["late_coming"] =  $this->calculatedLateComing($firstLog["time"], $params["shift"]->on_duty_time, $params["shift"]->late_time);
            $item["in"] = $firstLog["time"];
            $item["device_id_in"] = $firstLog["DeviceID"];
        }

        if ($item["late_coming"] != "---") {
            $item["status"] = "LC";
        }

        if ($lastLog && count($params["logs"]) > 1 && ($lastLog["log_type"] == "out" || $lastLog["log_type"] == "auto")) {
            $item["status"] = "P";
            $item["device_id_out"] = $lastLog["DeviceID"];
            $item["out"] = $lastLog["time"];
            $item["total_hrs"] = $this->getTotalHrsMins($firstLog["time"], $lastLog["time"]);

            if ($params["isOverTime"]) {
                $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
            }

            $item["early_going"] = $this->calculatedEarlyGoing($lastLog["time"], $params["shift"]->off_duty_time, $params["shift"]->early_time);

            if ($item["early_going"] != "---") {
                $item["status"] = "EG";
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
