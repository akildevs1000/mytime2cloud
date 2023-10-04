<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use Illuminate\Support\Facades\Log as Logger;

class FlexibleAndSingleController extends Controller
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

            if (in_array($row->schedule->shift_type_id ?? 0, [1, 6])) {

                $logs = $logs[$row->system_user_id] ?? [];

                if (!$logs) {
                    continue;
                }

                $firstLog = $logs->first();
                $lastLog = $logs->last();

                $item = [
                    "total_hrs" => "---",
                    "in" => $logs->where("log_type", "auto")->first()["time"] ?? "---",
                    "out" =>  "---",
                    "ot" => "---",
                    "device_id_in" => $logs->where("log_type", "auto")->first()["DeviceID"] ?? "---",
                    "device_id_out" => "---",
                    "date" => $params["date"],
                    "company_id" => $params["company_id"],
                    "employee_id" => $row->system_user_id,
                    "shift_id" => $row->schedule->shift_id  ?? 0,
                    "shift_type_id" => $row->schedule->shift_type_id  ?? 0,
                    "status" => "M",
                ];

                if ($firstLog["log_type"] == "in") {
                    $item["in"] = $logs->where("log_type", "in")->first()["time"] ?? "---";
                    $item["device_id_in"] = $logs->where("log_type", "in")->first()["DeviceID"] ?? "---";
                }

                $shift = $row->schedule->shift ?? false;

                if ($shift && $item["shift_type_id"] == 6) {
                    $item["late_coming"] =  $this->calculatedLateComing($item["in"], $shift->on_duty_time, $shift->late_time);

                    if ($item["late_coming"] != "---") {
                        $item["status"] = "LC";
                    }
                }

                if ($shift && $lastLog && count($logs) > 1) {
                    $item["status"] = "P";
                    $item["device_id_out"] = $logs->where("log_type", "auto")->last()["DeviceID"] ?? "---";
                    $item["out"] = $logs->where("log_type", "auto")->last()["time"] ?? "---";


                    if (in_array($lastLog["log_type"], ["in", "out"])) {
                        $item["device_id_out"] = $logs->where("log_type", "out")->last()["DeviceID"] ?? "---";
                        $item["out"] = $logs->where("log_type", "out")->last()["time"] ?? "---";
                    }
                    $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);

                    if ($row->schedule->isOverTime ?? false) {
                        $item["ot"] = $this->calculatedOT($item["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                    }

                    if ($item["shift_type_id"] == 6) {
                        $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $shift->off_duty_time, $shift->early_time);

                        if ($item["early_going"] != "---") {
                            $item["status"] = "EG";
                        }
                    }
                }

                $items[] = $item;
            }
        }

        if (!count($items)) {
            $message = $this->getMeta("All Shift", "No Data Found.");
            return $message;
        }

        try {
            $model = Attendance::query();
            $model->where("company_id", $id);
            $model->whereIn("employee_id", array_column($items, "employee_id"));
            $model->where("date", $date);
            $model->delete();
            $model->insert($items);
            AttendanceLog::whereIn("UserID", array_column($items, "employee_id"))->where("company_id", $id)->update(["checked" => true]);
            $message =  "Log(s) has been render. Affected Ids: " . json_encode(array_column($items, "employee_id"));
            return $this->getMeta("All Shift", $message);
        } catch (\Throwable $e) {
            return $this->getMeta("All Shift", $e->getMessage());
        }
    }
}
