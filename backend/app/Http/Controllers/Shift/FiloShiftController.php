<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\ScheduleEmployee;

class FiloShiftController extends Controller
{
    const SHIFTYPE = 1;

    public function render()
    {
        $params = [
            "date" => new \DateTime($this->getCurrentDate()),
            "company_ids" => [],
            "employee_ids" => [],
            "employeesByType" => (new ScheduleEmployee)->getEmployeesByType(self::SHIFTYPE),
        ];

        $payload = $this->prepareAttendanceRecords($params);

        $arr[] =  (new Attendance)->startDBOperation($params["date"], "Filo", $payload);

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
            "employeesByType" => (new ScheduleEmployee)->getEmployeesByType(self::SHIFTYPE),
        ];


        while ($startDate <= $currentDate && $startDate <= $endDate) {

            $params["date"] = $startDate;

            $payload = $this->prepareAttendanceRecords($params);

            $arr[] =  (new Attendance)->startDBOperation($params["date"], "Filo", $payload);

            $startDate->modify('+1 day');
        }

        return $arr;
    }


    public function prepareAttendanceRecords($params)
    {

        $companyIdWithUserIds = (new AttendanceLog)->getEmployeeIdsForNewLogs($params);

        $logs = (new AttendanceLog)->getLogsByUser($params);

        $items = [];

        foreach ($companyIdWithUserIds as $companyIdWithUserId) {

            $filteredLogs = $logs[$companyIdWithUserId->company_id][$companyIdWithUserId->UserID] ?? false;


            if (!$filteredLogs || $filteredLogs->isEmpty()) {
                continue;
            }

            $firstLog = $filteredLogs->first();
            $lastLog = $filteredLogs->last();

            $arr = [];

            $schedule = $params["employeesByType"][$companyIdWithUserId->company_id][$companyIdWithUserId->UserID][0] ?? false;

            if (!$schedule) {
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
                "shift_type_id" => self::SHIFTYPE,
                "device_id_in" => $firstLog["DeviceID"],
                "device_id_out" => $firstLog["DeviceID"],
                "in" => $firstLog["time"],
                "status" => "M",
            ];

            if (count($filteredLogs) > 1) {
                $arr["status"] = "P";
                $arr["device_id_out"] = $lastLog["DeviceID"];
                $arr["out"] = $lastLog["time"];
                $arr["total_hrs"] = $this->getTotalHrsMins($firstLog["time"], $lastLog["time"]);

                if ($schedule["isOverTime"]) {
                    $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                }
            }


            $items[$companyIdWithUserId->company_id] = $arr;
        }

        return array_values($items);
    }
}
