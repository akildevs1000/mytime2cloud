<?php

namespace App\Http\Controllers\Shift;

use App\Models\Company;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ScheduleEmployee;

class SplitShiftController extends Controller
{
    public function render()
    {
        $date = $this->getCurrentDate();

        $dateObj = new \DateTime($date);


        // Get all schedule employees for the current date and shift type id 6
        $scheduleEmployees = ScheduleEmployee::with("shift")
            ->whereHas("attendance_logs", function ($q) use ($date) {
                $q->whereDate("LogTime", $date);
                $q->where("checked", false);
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
            $employeeAttendanceLogs = $attendanceLogs[$scheduleEmployee->employee_id];

            if (!$employeeAttendanceLogs || $employeeAttendanceLogs->isEmpty()) {
                continue;
            }

            $shift = $scheduleEmployee->shift;

            $temp = [
                "logs" => [],

                "total_hrs" => 0,
                "out" => "---",
                "ot" => "---",

                "company_id" => $company_id,
                "date" => $date,
                "employee_id" => $scheduleEmployee->employee_id,
                "shift_type_id" => $scheduleEmployee->shift_type_id,
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
        return "{$dateObj->format('d-M-y')}: Log(s) has been render. Affected Ids: " . json_encode($employee_ids);

        return;

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

    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        $endDateString = $request->dates[1];
        $company_id = $request->company_id;
        $employee_ids = $request->employee_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $arr = [];

        while ($startDate <= $currentDate && $startDate <= $endDate) {
            $arr[] =  $this->renderManual($startDate, $employee_ids, $company_id);
            $startDate->modify('+1 day');
        }

        return $arr;
    }

    public function renderManual($dateObj, $employee_ids, $company_id)
    {
        $date = $dateObj->format('Y-m-d');
        // Get all schedule employees for the current date and shift type id 6
        $scheduleEmployees = ScheduleEmployee::with("shift")
            ->whereHas("attendance_logs", function ($q) use ($date, $employee_ids, $company_id) {
                $q->where("company_id", $company_id);
                $q->whereDate("LogTime", $date);
                // $q->where("checked", false);
                $q->whereIn("employee_id", $employee_ids);
            })
            ->where("shift_type_id", 5)
            ->get();

        // If no schedule employees are found, log and return a message
        if ($scheduleEmployees->isEmpty()) {
            return "{$dateObj->format('d-M-y')}: No Data Found";
        }

        $attendanceLogs = AttendanceLog::whereDate("LogTime", $date)
            ->where("company_id", $company_id)
            ->whereIn("UserID", $employee_ids)
            ->distinct("LogTime", "UserID", "company_id")
            ->get()
            ->groupBy(['UserID']);

        $items = [];

        foreach ($scheduleEmployees as $scheduleEmployee) {
            $employeeAttendanceLogs = $attendanceLogs[$scheduleEmployee->employee_id];

            if (!$employeeAttendanceLogs || $employeeAttendanceLogs->isEmpty()) {
                continue;
            }

            $shift = $scheduleEmployee->shift;

            $temp = [
                "logs" => [],

                "total_hrs" => 0,
                "out" => "---",
                "ot" => "---",

                "company_id" => $company_id,
                "date" => $date,
                "employee_id" => $scheduleEmployee->employee_id,
                "shift_type_id" => $scheduleEmployee->shift_type_id,
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
        AttendanceLog::whereIn("UserID", $employee_ids)->whereDate("LogTime", $date)->where("company_id", $company_id)->update(["checked" => true]);
        return "{$dateObj->format('d-M-y')}: Log(s) has been render. Affected Ids: " . json_encode($employee_ids);

        // try {
        //     $model = Attendance::query();
        //     $model->where("date", $date);
        //     $model->whereIn("employee_id", $employee_ids);
        //     $model->where("company_id", $company_id);
        //     $model->delete();
        //     $model->insert($items);
        //     return "{$dateObj->format('d-M-y')}: Log(s) has been render. Affected Ids: " . json_encode($employee_ids);
        //     return;
        // } catch (\Exception $e) {
        //     return $e;
        // }
    }
    public function storeOrUpdate($items)
    {
        $attendance = Attendance::whereDate("date", $items['date'])->where("employee_id", $items['employee_id'])->where("company_id", $items['company_id']);
        $found = $attendance->first();
        return $found ? $attendance->update($items) : Attendance::create($items);
    }
}
