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
        $date = $this->getCurrentDate();

        // Get all schedule employees for the current date and shift type id 6
        $scheduleEmployees = ScheduleEmployee::with("shift")
            ->whereHas("attendance_logs", function ($q) use ($date) {
                $q->whereDate("LogTime", $date);
            })
            ->where("shift_type_id", 6)
            ->get();

        // If no schedule employees are found, log and return a message
        if ($scheduleEmployees->isEmpty()) {
            info("No Data Found.");
            return "No Data Found";
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
                info("No Data Found for employee {$scheduleEmployee->employee_id}");
                continue;
            }

            $firstLog = $employeeAttendanceLogs->first();
            $lastLog = $employeeAttendanceLogs->last();

            $shift = $scheduleEmployee->shift;

            $arr = [

                "total_hrs" => "---",
                "early_going" => "---",
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
                "late_coming" => $this->calculatedLateComing($firstLog["time"], $shift->on_duty_time, $shift->late_time),
            ];

            if (count($employeeAttendanceLogs) > 1) {
                $arr["status"] = "P";
                $arr["device_id_out"] = $lastLog["DeviceID"];
                $arr["out"] = $lastLog["time"];
                $arr["total_hrs"] = $this->getTotalHrsMins($firstLog["time"], $lastLog["time"]);

                if ($scheduleEmployee->isOverTime) {
                    $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $shift->working_hours, $shift->overtime_interval);
                }
                $arr["early_going"] = $this->calculatedEarlyGoing($lastLog["time"], $shift->off_duty_time, $shift->early_time);
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
            info("SingleShift: Logs has been render. Data: " . json_encode($items));
            return $items;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function userFunc($company_id, $schedule)
    {

        $date = date("Y-m-d");



        $UserID = $schedule->employee_id;



        $model = AttendanceLog::query();

        $model->whereDate("LogTime", $date);
        $model->where("company_id", $company_id);
        $model->where("UserID", $UserID);
        $model->distinct("LogTime");

        $count = $model->count();

        $data = [$model->clone()->orderBy("LogTime")->first(), $model->orderBy("LogTime", "desc")->first()];

        if (!$count) {
            info("Employee with $UserID SYSTEM USER ID and company id = $company_id has no Log(s) on $date.");
            return;
        }

        $arr = [];
        $arr["company_id"] = $company_id;
        $arr["date"] = $date;
        $arr["employee_id"] = $UserID;
        $arr["shift_type_id"] = $schedule["shift_type_id"];
        $arr["shift_id"] = $schedule["shift_id"];
        $arr["roster_id"] = $schedule["roster_id"];
        $arr["device_id_in"] = $data[0]["DeviceID"];
        $arr["in"] = $data[0]["time"];
        $arr["status"] = "M";
        $arr["late_coming"] = $this->calculatedLateComing($arr["in"], $schedule["on_duty_time"], $schedule["late_time"]);


        if ($count > 1) {
            $arr["status"] = "P";
            $arr["device_id_out"] = $data[1]["DeviceID"];
            $arr["out"] = $data[1]["time"];
            $arr["total_hrs"] = $this->getTotalHrsMins($data[0]["time"], $data[1]["time"]);

            if ($schedule["isOverTime"]) {
                $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $schedule['working_hours'], $schedule['overtime_interval']);
            }
            $arr["early_going"] = $this->calculatedEarlyGoing($arr["out"], $schedule["off_duty_time"], $schedule["early_time"]);;
        }

        try {

            $model = Attendance::query();
            $model->where("date", $arr['date']);
            $model->where("employee_id", $arr['employee_id']);
            $model->where("company_id", $arr['company_id']);
            $model->delete();
            $model->create($arr);
            info("The Logs has been render against " . $arr['employee_id'] . " SYSTEM USER ID.");
            return;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public $shift_type_id = 6;

    public $result = "";

    public $arr = [];

    public function findAttendanceByUserId($item)
    {
        $model = Attendance::query();
        $model->where("employee_id", $item["employee_id"]);
        $model->where("company_id", $item["company_id"]);
        $model->whereDate("date", $item["date"]);

        return !$model->first() ? false : $model->with(["schedule", "shift"])->first();
    }

    public function processData($companyId, $data, $shift_type_id, $checked = true)
    {
        $items = [];
        $arr = [];
        $ids = [];
        $existing_ids = [];
        $arr["company_id"] = $companyId;
        $arr["date"] = $this->getCurrentDate();

        $str = "";

        foreach ($data as $UserID => $logs) {
            if (count($logs) == 0) {
                $str .= "No log(s) found for Company ID $companyId.\n";
                continue;
            };

            $schedule = $logs[0]["schedule"];
            $shift    = $schedule["shift"];

            $arr["employee_id"] = $UserID;

            $model = $this->findAttendanceByUserId($arr);

            if (!$model) {
                $arr["shift_type_id"] = $shift_type_id;
                $arr["status"] = "P";
                $arr["device_id_in"] = $logs[0]["DeviceID"];
                $arr["shift_id"] = $logs[0]["schedule"]["shift_id"];
                $arr["roster_id"] = $logs[0]["schedule"]["roster_id"];
                $arr["in"] = $logs[0]["time"];
                $arr["late_coming"] = $this->calculatedLateComing($logs[0]["time"], $shift["on_duty_time"], $shift["late_time"]);

                $items[] = $arr;
                $ids[] = $logs[0]["id"];

                Attendance::create($arr);
                AttendanceLog::where("id", $logs[0]["id"])->update(["checked" => true]);
            } else {

                $last = array_reverse($logs)[0];
                $arr["out"] = $last["time"];
                $arr["device_id_out"] = $last["DeviceID"];
                $arr["total_hrs"] = $this->getTotalHrsMins($model->in, $last["time"]);
                $schedule = $model->schedule ?? false;
                $isOverTime = $schedule && $schedule->isOverTime ?? false;
                $shift = $last['schedule']['shift'];
                $arr["early_going"] = $this->calculatedEarlyGoing($last["time"], $shift["off_duty_time"], $shift["early_time"]);

                if ($isOverTime) {
                    $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $shift['working_hours'], $shift['overtime_interval']);
                }

                $items[] = $arr;

                $model->update($arr);
                $existing_ids[] = $UserID;
            }
        }
        $new_logs = 0; //$this->storeAttendances($items, $ids);
        $existing_logs = $this->updateAttendances($companyId, $existing_ids);

        $result = $new_logs + $existing_logs;
        $str .= $this->getMeta("SyncSingleShift", "Total $result Log(s) Processed against company $companyId.\n");
        return $str;
    }

    public function storeAttendances($items, $ids)
    {
        Attendance::insert($items);

        return AttendanceLog::whereIn("id", $ids)->update(["checked" => true]);
    }

    public function updateAttendances($companyId, $existing_ids)
    {
        return AttendanceLog::where("UserID", $existing_ids)->where("company_id", $companyId)->update(["checked" => true]);
    }

    public function syncLogsScript()
    {
        $companyIds = Company::pluck("id");

        if (count($companyIds) == 0) {
            return $this->getMeta("SyncSingleShift", "No Company found.");
        }

        return $this->runFunc($this->getCurrentDate(), $companyIds, []);
    }


    public function ClearDB($currentDate, $companyIds, $UserIDs)
    {
        // update attendance_logs table
        DB::table('attendance_logs')
            ->whereDate('LogTime', '=', $currentDate)
            ->whereIn('company_id',  $companyIds)
            ->whereIn('UserID', $UserIDs)
            ->update(['checked' => false]);

        // delete from attendances table
        DB::table('attendances')
            ->whereDate('date', '=', $currentDate)
            ->whereIn('company_id',  $companyIds)
            ->whereIn('employee_id',  $UserIDs)
            ->delete();
    }

    public function processByManual(Request $request)
    {
        $currentDate = $request->input('date', $this->getCurrentDate());
        $companyIds = $request->input('company_ids', []);
        $UserIDs = $request->input('UserIDs', []);
        // $this->ClearDB($currentDate, $companyIds, $UserIDs);
        return $this->runFunc($currentDate, $companyIds, $UserIDs);
    }

    public function processByManualSingle(Request $request)
    {
        $currentDate = $request->input('date', $this->getCurrentDate());
        return $this->runFunc($currentDate, [$request->company_id], [$request->UserID]);
    }

    public function runFunc($currentDate, $companyIds, $UserIDs)
    {
        foreach ($companyIds as $company_id) {
            $data = $this->getModelDataByCompanyId($currentDate, $company_id, $UserIDs, $this->shift_type_id);
            if (count($data) == 0) {
                $this->result .= $this->getMeta("SyncSingleShift", "No Logs found against $company_id Company Id.\n");
                continue;
            }

            $row = $this->processData($company_id, $data, $this->shift_type_id);
            $this->result .= $row;
        }
        return $this->result;
    }
}
