<?php

namespace App\Http\Controllers\Shift;

use App\Models\Company;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use App\Models\ShiftType;

class SplitShiftController extends Controller
{
    const SHIFTYPE = 5;

    public function render()
    {
        $date = $this->getCurrentDate();

        $dateObj = new \DateTime($date);


        // Get all schedule employees for the current date and shift type id 6
        $scheduleEmployees = ScheduleEmployee::with("shift")
            ->whereHas("attendance_logs", function ($q) use ($date) {
                $q->whereDate("LogTime", $date);
                $q->where("checked", false);
                $q->where("log_type", "auto");
            })
            ->where("shift_type_id", 5)
            ->get();

        // If no schedule employees are found, log and return a message
        if ($scheduleEmployees->isEmpty()) {
            return $this->getMeta("SplitShift", "No Data Found");
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
                continue;
            }

            $shift = $scheduleEmployee->shift;

            $temp = [
                "logs" => [],

                "total_hrs" => 0,
                "out" => "---",
                "ot" => "---",

                "company_id" => $scheduleEmployee->company_id,
                "date" => $date,
                "employee_id" => $scheduleEmployee->employee_id,
                "shift_type_id" => 5,
                "shift_id" => $scheduleEmployee->shift_id,
                "roster_id" => $scheduleEmployee->roster_id,
                "status" => count($employeeAttendanceLogs)  % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
            ];

            $totalMinutes = 0;

            $data = $employeeAttendanceLogs;

            for ($i = 0; $i < count($data); $i++) {
                $currentLog = $data[$i];
                $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;

                $temp["logs"][] =  [
                    "in" => $currentLog['time'],
                    "out" =>  $nextLog && $nextLog['time'] ? $nextLog['time'] : "---",
                    "diff" => $nextLog ? $this->minutesToHoursNEW($currentLog['time'], $nextLog['time']) : "---",
                    // $currentLog['LogTime'], $nextLog['time'] ?? "---"
                ];

                if ((isset($currentLog['time']) && $currentLog['time'] != '---') and (isset($nextLog['time']) && $nextLog['time'] != '---')) {

                    $parsed_out = strtotime($nextLog['time'] ?? 0);
                    $parsed_in = strtotime($currentLog['time'] ?? 0);

                    $diff = $parsed_out - $parsed_in;

                    $minutes = floor($diff / 60);

                    $totalMinutes += $minutes > 0 ? $minutes : 0;
                }

                $temp["total_hrs"] = $this->minutesToHours($totalMinutes);


                if ($scheduleEmployee->isOverTime) {
                    $temp["ot"] = $this->calculatedOT($temp["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                }
                $this->storeOrUpdate($temp);
                $items[] = $temp;
                $i++;
            }
        }
        AttendanceLog::whereIn("UserID", $employee_ids)->whereDate("LogTime", $date)->whereIn("company_id", $company_ids)->update(["checked" => true]);
        $message = "{$dateObj->format('d-M-y')}: Log(s) has been render. Affected Ids: " . json_encode($employee_ids);
        info($message);
        return $message;

        try {
            $model = Attendance::query();
            $model->where("date", $date);
            $model->whereIn("employee_id", $employee_ids);
            $model->whereIn("company_id", $company_ids);
            $model->delete();
            $model->insert($items);
            info("SplitShift: Log(s) has been render. Data: " . json_encode($items));
            AttendanceLog::where("UserID", $employee_ids)->whereIn("company_id", $company_ids)->update(["checked" => true]);
            return "SplitShift: Log(s) has been render. Data: " . json_encode($items);
        } catch (\Exception $e) {
            return $e;
        }
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

            $arr[] = (new Attendance)->startDBOperation($params["date"], "Split", $payload);

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

            $schedule = $params["employeesByType"][$companyIdWithUserId->company_id][$companyIdWithUserId->UserID][0] ?? false;

            if (!$schedule) {
                continue;
            }


            $shift = $schedule["shift"];

            $temp = [

                "logs" => [],
                "total_hrs" => 0,
                "out" => "---",
                "ot" => "---",
                "company_id" => $companyIdWithUserId->company_id,
                "date" => $params["date"]->format('Y-m-d'),
                "employee_id" => $companyIdWithUserId->UserID,
                "shift_type_id" => self::SHIFTYPE,
                "shift_id" => $schedule["shift_id"],

                "status" => count($filteredLogs)  % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
            ];

            $totalMinutes = 0;

            $data = $filteredLogs;

            for ($i = 0; $i < count($data); $i++) {
                $currentLog = $data[$i];
                $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;

                $temp["logs"][] = [
                    "in" => $currentLog['time'],
                    "out" =>  $nextLog && $nextLog['time'] ? $nextLog['time'] : "---",
                    "diff" => $nextLog ? $this->minutesToHoursNEW($currentLog['time'], $nextLog['time']) : "---",
                    // $currentLog['LogTime'], $nextLog['time'] ?? "---"
                ];
                //$temp["logs"] = json_encode($temp["logs"]);
                if ((isset($currentLog['time']) && $currentLog['time'] != '---') and (isset($nextLog['time']) && $nextLog['time'] != '---')) {

                    $parsed_out = strtotime($nextLog['time'] ?? 0);
                    $parsed_in = strtotime($currentLog['time'] ?? 0);

                    $diff = $parsed_out - $parsed_in;

                    $minutes = floor($diff / 60);

                    $totalMinutes += $minutes > 0 ? $minutes : 0;
                }

                $temp["total_hrs"] = $this->minutesToHours($totalMinutes);


                if ($schedule["isOverTime"]) {
                    $temp["ot"] = $this->calculatedOT($temp["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                }

                // $this->storeOrUpdate($temp);

                $items[] = $temp;
                // $items[$companyIdWithUserId->company_id] = $arr;

                $i++;
            }
        }
        // return "(Split Shift) " . $params['date']->format('d-M-y') . ": Log(s) has been render. Affected Ids: " . json_encode($params["employee_ids"]);

        return array_values($items);
    }
    public function storeOrUpdate($items)
    {
        $attendance = Attendance::whereDate("date", $items['date'])->where("employee_id", $items['employee_id'])->where("company_id", $items['company_id']);
        $found = $attendance->first();
        return $found ? $attendance->update($items) : Attendance::create($items);
    }

    public function renderByLogType($id, $date)
    {
        $params = ["company_id" => $id, "date" => $date];

        $employees = (new Employee)->attendanceEmployee($params);

        if (!count($employees)) {
            return $this->getMeta("Split Shift", "No record found");
        };


        $logs = AttendanceLog::whereDate("LogTime", $params["date"])
            ->where("company_id", $params["company_id"])
            ->whereIn("log_type", ["in", "out"])
            ->whereHas("schedule", function ($q) {
                $q->where("shift_type_id", 5);
            })
            ->distinct("LogTime", "UserID", "company_id")
            ->get()
            ->groupBy(['UserID']);

        $items = [];


        if (!count($logs)) {
            return $this->getMeta("Split Shift", "No record found");
        };

        foreach ($employees as $row) {

            $params["isOverTime"] = $row->schedule->isOverTime;
            $params["shift"] = $row->schedule->shift ?? false;
            if ($row->schedule->shift_type_id == 5 ?? false) {

                $data = $logs[$row->system_user_id] ?? [];

                if (!count($data)) {
                    $items[] = $this->getMeta("All Shift", "No record found" . $row->system_user_id);
                };

                $item = [
                    "total_hrs" => 0,
                    "in" => "---",
                    "out" => "---",
                    "ot" => "---",
                    "device_id_in" => "---",
                    "device_id_out" => "---",
                    "date" => $params["date"],
                    "company_id" => $params["company_id"],
                    "employee_id" => $row->system_user_id,
                    "shift_id" => $params["shift"]["id"] ?? 0,
                    "shift_type_id" => $params["shift"]["shift_type_id"]  ?? 0,
                    "status" => "A",
                    "logs" =>  []
                ];

                $item["status"] = count($data) % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT;
                $totalMinutes = 0;

                for ($i = 0; $i < count($data); $i++) {
                    $currentLog = $data[$i];
                    $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;



                    $item["logs"][] =  [

                        "in" => $currentLog['time'],
                        "out" =>  $nextLog && $nextLog['time'] ? $nextLog['time'] : "---",
                        // "diff" => $nextLog ? $this->minutesToHoursNEW($currentLog['time'], $nextLog['time']) : "---",
                        "device_in" => $currentLog['device']['short_name'] ?? "---",
                        "device_out" => $nextLog['device']['short_name'] ?? "---",
                    ];

                    if ((isset($currentLog['time']) && $currentLog['time'] != '---') and (isset($nextLog['time']) && $nextLog['time'] != '---')) {

                        $parsed_out = strtotime($nextLog['time'] ?? 0);
                        $parsed_in = strtotime($currentLog['time'] ?? 0);

                        if ($parsed_in > $parsed_out) {
                            $parsed_out += 86400;
                        }

                        $diff = $parsed_out - $parsed_in;

                        $minutes = floor($diff / 60);

                        $totalMinutes += $minutes > 0 ? $minutes : 0;
                    }

                    $item["total_hrs"] = $this->minutesToHours($totalMinutes);

                    if ($params["isOverTime"]) {
                        $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
                    }

                    try {
                        $attendance = Attendance::whereDate("date", $item['date'])->where("employee_id", $item['employee_id'])->where("company_id", $item['company_id']);
                        $found = $attendance->first();
                        $found ? $attendance->update($item) : Attendance::create($item);
                        $items[$item['employee_id']] = $item['employee_id'];
                        $i++;
                    } catch (\Throwable $e) {
                        return $this->getMeta("All Shift", $e->getMessage());
                    }
                }
            }
        }
        $UserIds = array_keys($logs->toArray());
        AttendanceLog::whereIn("UserID", $UserIds)->where("company_id", $id)->update(["checked" => true]);
        $message =  "Log(s) has been render. Affected Ids: " . json_encode($UserIds);
        return $this->getMeta("Split Shift", $message);
    }
}
