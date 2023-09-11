<?php

namespace App\Http\Controllers\Shift;

use App\Models\Company;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ScheduleEmployee;

class SingleShiftController extends Controller
{
    public function render()
    {
        $dateObj = new \DateTime($this->getCurrentDate());

        $params = [
            "date" => $dateObj,
            "company_ids" => [],
            "employee_ids" => [],
            "shift_type_id" => 6,
            "checked" => false
        ];


        $arr[] = $this->renderManual($params);
        return json_encode($arr);
    }

    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        $endDateString = $request->dates[1];
        $company_ids = $request->company_ids;
        $employee_ids = $request->employee_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $arr = [];

        $params = [
            "company_ids" => $company_ids,
            "employee_ids" => $employee_ids,
            "shift_type_id" => 6,
            "checked" => true
        ];

        while ($startDate <= $currentDate && $startDate <= $endDate) {

            $params["date"] = $startDate;
            $arr[] =  $this->renderManual($params);
            $startDate->modify('+1 day');
        }

        return $arr;
    }

    public function renderManual($params)
    {
        $payload = $this->prepareAttendanceRecords($params);

        if (!count($payload)) {
            info("(Filo Shift) {$params['date']->format('d-M-y')}: No Data Found");
            return "(Filo Shift) {$params['date']->format('d-M-y')}: No Data Found";
        }

        $employee_ids = array_column($payload, "employee_id");
        $company_ids = array_column($payload, "company_id");

        try {
            $model = Attendance::query();
            $model->where("date", $params["date"]->format('Y-m-d'));
            $model->whereIn("employee_id", $employee_ids);
            $model->whereIn("company_id", $company_ids);
            $model->delete();
            $model->insert($payload);
            AttendanceLog::whereIn("UserID", $employee_ids)->whereIn("company_id", $company_ids)->update(["checked" => true]);
            return "(Filo Shift) " . $params['date']->format('d-M-y') . ": Log(s) has been render. Affected Ids: " . json_encode($employee_ids) . ". Affected Company Ids: " . json_encode($company_ids);
        } catch (\Throwable $e) {
            return $e;
        }
    }

    public function prepareAttendanceRecords($params)
    {
        $employeesByType = (new ScheduleEmployee)->getEmployeesByType($params);

        $companyIdWithUserIds = (new AttendanceLog)->getEmployeeIdsForNewLogs($params);

        $logs = (new AttendanceLog)->getLogsByUser($params);

        $items = [];

        foreach ($companyIdWithUserIds as $companyIdWithUserId) {

            $filteredLogs = $logs[$companyIdWithUserId->company_id][$companyIdWithUserId->UserID];

            $firstLog = $filteredLogs->first();
            $lastLog = $filteredLogs->last();

            $arr = [];

            $schedule = $employeesByType[$companyIdWithUserId->company_id][$companyIdWithUserId->UserID][0];

            if (!$schedule) {
                continue;
            }

            if (!$filteredLogs || $filteredLogs->isEmpty()) {
                continue;
            }

            $shift = $schedule["shift"];

            $firstLog = $filteredLogs->first();
            $lastLog = $filteredLogs->last();

            $arr = [
                "total_hrs" => "---",
                "out" => "---",
                "ot" => "---",
                "date" => $params["date"]->format('Y-m-d'),
                "company_id" => $companyIdWithUserId->company_id,
                "employee_id" => $companyIdWithUserId->UserID,
                "shift_id" => $schedule["shift_id"],
                "shift_type_id" => $params["shift_type_id"],
                "device_id_in" => $firstLog["DeviceID"],
                "device_id_out" => $firstLog["DeviceID"],
                "in" => $firstLog["time"],
                "status" => "M",
                "late_coming" => $this->calculatedLateComing($firstLog["time"], $shift->on_duty_time, $shift->late_time),
            ];

            if (count($filteredLogs) > 1) {
                $arr["status"] = "P";
                $arr["device_id_out"] = $lastLog["DeviceID"];
                $arr["out"] = $lastLog["time"];
                $arr["total_hrs"] = $this->getTotalHrsMins($firstLog["time"], $lastLog["time"]);

                if ($schedule["isOverTime"]) {
                    $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                }

                $arr["early_going"] = $this->calculatedEarlyGoing($lastLog["time"], $shift->off_duty_time, $shift->early_time);
            }


            $items[$companyIdWithUserId->company_id] = $arr;
        }

        return array_values($items);
    }
}
