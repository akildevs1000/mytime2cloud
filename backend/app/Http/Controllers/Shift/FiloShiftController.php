<?php

namespace App\Http\Controllers\Shift;

use App\Models\Company;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ScheduleEmployee;

class FiloShiftController extends Controller
{
    public function render()
    {
        $date = $this->getCurrentDate();

        // Get all schedule employees for the current date and shift type id 6
        $scheduleEmployees = ScheduleEmployee::with("shift")
            ->whereHas("attendance_logs", function ($q) use ($date) {
                $q->whereDate("LogTime", $date);
                // $q->where("checked", false);
            })
            ->where("shift_type_id", 1)
            ->get();

        // If no schedule employees are found, log and return a message
        if ($scheduleEmployees->isEmpty()) {
            info("FiloShift: No Data Found.");
            return "FiloShift: No Data Found";
        }

        $company_ids = $scheduleEmployees->pluck('company_id')->toArray();
        $employee_ids = $scheduleEmployees->pluck('employee_id')->toArray();

        $attendanceLogs = AttendanceLog::whereDate("LogTime", $date)
            ->whereIn("company_id", $company_ids)
            ->whereIn("UserID", $employee_ids)
            ->distinct("LogTime", "UserID", "company_id")
            ->get()
            ->groupBy(['company_id', 'UserID']);

        $items = [];

        foreach ($scheduleEmployees as $scheduleEmployee) {
            $employeeAttendanceLogs = $attendanceLogs[$scheduleEmployee->company_id][$scheduleEmployee->employee_id];

            if (!$employeeAttendanceLogs || $employeeAttendanceLogs->isEmpty()) {
                info("FiloShift: No Data Found for employee {$scheduleEmployee->employee_id}");
                continue;
            }

            $firstLog = $employeeAttendanceLogs->first();
            $lastLog = $employeeAttendanceLogs->last();

            $shift = $scheduleEmployee->shift;

            $arr = [

                "total_hrs" => "---",
                "out" => "---",
                "ot" => "---",

                "company_id" => $scheduleEmployee->company_id,
                "date" => $date,
                "employee_id" => $scheduleEmployee->employee_id,
                "shift_type_id" => $scheduleEmployee->shift_type_id,
                "shift_id" => $scheduleEmployee->shift_id,
                "roster_id" => $scheduleEmployee->roster_id,
                "device_id_in" => $firstLog["DeviceID"],
                "device_id_out" => $firstLog["DeviceID"],
                "in" => $firstLog["time"],
                "status" => "M",
            ];

            if (count($employeeAttendanceLogs) > 1) {
                $arr["status"] = "P";
                $arr["device_id_out"] = $lastLog["DeviceID"];
                $arr["out"] = $lastLog["time"];
                $arr["total_hrs"] = $this->getTotalHrsMins($firstLog["time"], $lastLog["time"]);

                if ($scheduleEmployee->isOverTime) {
                    $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                }
            }
            $items[] = $arr;
        }
        // return $items;

        try {
            $model = Attendance::query();
            $model->where("date", $date);
            $model->whereIn("employee_id", $employee_ids);
            $model->whereIn("company_id", $company_ids);
            $model->delete();
            $model->insert($items);
            info("FiloShift: Log(s) has been render. Data: " . json_encode($items));
            AttendanceLog::where("UserID", $employee_ids)->whereIn("company_id", $company_ids)->update(["checked" => true]);
            return "FiloShift: Log(s) has been render. Data: " . json_encode($items);
        } catch (\Exception $e) {
            return $e;
        }
    }
}
