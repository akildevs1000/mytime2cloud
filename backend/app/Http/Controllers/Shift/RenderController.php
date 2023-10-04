<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\EmployeeLeaves;
use App\Models\Holidays;
use App\Models\Reason;
use App\Models\ScheduleEmployee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RenderController extends Controller
{

    public $manual_entry;

    public $reason;

    public $updated_by;

    public function renderLogs(Request $request)
    {
        // return (new ShiftRenderController)->renderData($request);

        $shift_type_id = $request->shift_type_id;
        
        if ($shift_type_id == 5) {
            return (new SplitShiftController)->renderData($request);
        } else if ($shift_type_id == 2) {
            return (new MultiInOutShiftController)->renderData($request);
        }
        return array_merge(
            (new FiloShiftController)->renderData($request),
            (new SingleShiftController)->renderData($request)
        );
    }

    public function renderMultiInOut(Request $request)
    {
        $shift_type_id = 2;

        $this->reason = $request->reason ?? null;

        $this->updated_by = $request->updated_by ?? 0;

        $company_id = $request->company_id;

        $UserID = $request->UserID;

        $currentDate = $request->date ?? date('Y-m-d');

        $schedule = $this->getScheduleMultiInOut($currentDate, $company_id, $UserID, $shift_type_id);

        if (!$schedule) {
            return $this->response("Employee with $UserID SYSTEM USER ID is not scheduled yet.", null, false);
        }

        $data = $this->getLogs($currentDate, $company_id, $UserID, $shift_type_id);

        if (!count($data)) {
            return $this->response("Employee with $UserID SYSTEM USER ID has no Log(s).", null, false);
        }

        $AttendancePayload = [
            "status" => count($data) % 2 !== 0 ? Attendance::MISSING : Attendance::PRESENT,
            "shift_type_id" => $shift_type_id,
            "date" => $currentDate,
            "company_id" => $company_id,
            "employee_id" => $UserID,
            "shift_id" => $schedule['shift_id'],
            "is_manual_entry" => false,
        ];

        $logs = $this->processLogs($data, $schedule);

        $items = $AttendancePayload + $logs;

        $attendance = Attendance::whereDate("date", $items['date'])->where("employee_id", $items['employee_id'])->where("company_id", $items['company_id']);
        $found = $attendance->first();
        return $found ? $attendance->update($items) : Attendance::create($items);
    }

    public function renderGeneral(Request $request)
    {
        $this->reason = $request->reason ?? null;

        $this->updated_by = $request->updated_by ?? 0;

        $date = $request->date;

        $company_id = $request->company_id;

        $UserID = $request->UserID;

        $schedule = $this->getScheduleGeneral($date, $company_id, $UserID);

        if (!$schedule) {
            return $this->response("Employee with $UserID SYSTEM USER ID is not scheduled yet.", null, false);
        }

        $model = AttendanceLog::query();

        $model->whereDate("LogTime", $date);
        $model->where("company_id", $company_id);
        $model->where("UserID", $UserID);

        $model->whereHas("schedule", function ($q) use ($company_id) {
            $q->whereNot('shift_type_id', 2);
            $q->where("company_id", $company_id);
        });

        $model->distinct("LogTime");

        $count = $model->count();

        $data = [$model->clone()->orderBy("LogTime")->first(), $model->orderBy("LogTime", "desc")->first()];

        if (!$count) {
            return $this->response("Employee with $UserID SYSTEM USER ID has no Log(s).", null, false);
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
        $arr["is_manual_entry"] = true;

        if ($schedule["shift_type_id"] == 4 && $schedule["shift_type_id"] == 6) {

            $LateComing = $this->calculatedLateComing($arr["in"], $schedule["on_duty_time"], $schedule["late_time"]);

            if ($LateComing) {
                // $arr["status"] = "A";
                $arr["late_coming"] = $LateComing;
            }
        }

        if ($count > 1) {
            $arr["status"] = "P";
            $arr["device_id_out"] = $data[1]["DeviceID"];
            $arr["out"] = $data[1]["time"];
            $arr["total_hrs"] = $this->getTotalHrsMins($data[0]["time"], $data[1]["time"]);

            if ($schedule["isOverTime"]) {
                $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $schedule['working_hours'], $schedule['overtime_interval']);
            }

            if ($schedule["shift_type_id"] == 4 && $schedule["shift_type_id"] == 6) {
                $EarlyGoing = $this->calculatedEarlyGoing($arr["in"], $schedule["off_duty_time"], $schedule["early_time"]);

                if ($EarlyGoing) {
                    // $arr["status"] = "A";
                    $arr["early_going"] = $EarlyGoing;
                }
            }
        }

        return $this->storeOrUpdate($arr);
    }

    public function renderAutoCron()
    {
        $message = "";

        $date = date("Y-m-d");

        $model = AttendanceLog::query();
        $model->whereDate("LogTime", date("Y-m-d"));
        $model->where("checked", false);
        $model->distinct(["LogTime"]);
        $model->where("company_id", ">", 0);

        $model->orderBy("LogTime");
        $data = $model->get()->groupBy(["UserID"]);

        if (!count($data)) {
            return "[" . date("Y-m-d H:i:s") . "] Cron:SyncAuto No data found.\n";
        }


        foreach ($data as $data) {

            $company_id = $data[0]->company_id;
            $UserID = $data[0]->UserID;

            $schedule = $this->getScheduleAuto($date, $company_id, $UserID);

            if (!$schedule) {
                $message .= "[" . date("Y-m-d H:i:s") . "] Cron:SyncAuto Employee with $UserID SYSTEM USER ID is not scheduled yet.\n";
                continue;
            }

            if (!$data) {
                $message .= "[" . date("Y-m-d H:i:s") . "] Cron:SyncAuto Employee with $UserID SYSTEM USER ID has no Log(s).\n";
                continue;
            }

            $count = count($data);

            $data = [$data[0], $data[$count - 1]];

            $shifts = $this->getShifts($company_id);

            $nearestShift = $this->findClosest($shifts, count($shifts), $data[0]["show_log_time"], $date);

            $arr = [];
            $arr["company_id"] = $company_id;
            $arr["date"] = $date;
            $arr["employee_id"] = $UserID;
            $arr["shift_type_id"] = $nearestShift["shift_type_id"];
            $arr["shift_id"] = $nearestShift["id"];
            $arr["device_id_in"] = $data[0]["DeviceID"];
            $arr["in"] = $data[0]["time"];
            $arr["status"] = "M";
            $arr["is_manual_entry"] = true;

            if ($schedule["shift_type_id"] == 4 && $schedule["shift_type_id"] == 6) {

                $LateComing = $this->calculatedLateComing($arr["in"], $nearestShift["on_duty_time"], $nearestShift["late_time"]);

                if ($LateComing) {
                    $arr["late_coming"] = $LateComing;
                }
            }

            if ($count > 1) {
                $arr["status"] = "P";
                $arr["device_id_out"] = $data[1]["DeviceID"];
                $arr["out"] = $data[1]["time"];
                $arr["total_hrs"] = $this->getTotalHrsMins($data[0]["time"], $data[1]["time"]);

                if ($schedule["isOverTime"]) {
                    $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $nearestShift['working_hours'], $nearestShift['overtime_interval']);
                }

                if ($nearestShift["shift_type_id"] == 4 && $nearestShift["shift_type_id"] == 6) {
                    $EarlyGoing = $this->calculatedEarlyGoing($arr["in"], $nearestShift["off_duty_time"], $nearestShift["early_time"]);

                    if ($EarlyGoing) {
                        // $arr["status"] = "A";
                        $arr["early_going"] = $EarlyGoing;
                    }
                }
            }

            AttendanceLog::where("UserID", $UserID)->where("company_id", $company_id)->update(["checked" => true]);

            $this->storeOrUpdate($arr);

            $message .= "[" . date("Y-m-d H:i:s") . "] Cron:SyncAuto The Log(s) has been rendered against " . $UserID . " SYSTEM USER ID.\n";
        }


        return $message;
    }
    public function renderAutoMultiple($date, $UserID, $company_id,)
    {
        $schedule = $this->getScheduleAuto($date, $company_id, $UserID);

        if (!$schedule) {
            return "Employee with $UserID SYSTEM USER ID is not scheduled yet.";
        }

        $model = AttendanceLog::query();

        $model->whereDate("LogTime", $date);
        $model->where("company_id", $company_id);
        $model->where("UserID", $UserID);
        $model->distinct("LogTime");
        $count = $model->count();
        $data = [$model->clone()->orderBy("LogTime")->first(), $model->orderBy("LogTime", "desc")->first()];

        if (!$count) {
            return "Employee with $UserID SYSTEM USER ID has no Log(s) on $date.";
        }

        $shifts = $this->getShifts($company_id);

        $nearestShift = $this->findClosest($shifts, count($shifts), $data[0]["show_log_time"], $date);

        $arr = [];
        $arr["company_id"] = $company_id;
        $arr["date"] = $date;
        $arr["employee_id"] = $UserID;
        $arr["shift_type_id"] = $nearestShift["shift_type_id"];
        $arr["shift_id"] = $nearestShift["id"];
        $arr["device_id_in"] = $data[0]["DeviceID"];
        $arr["in"] = $data[0]["time"];
        $arr["status"] = "M";
        $arr["is_manual_entry"] = true;

        if ($schedule["shift_type_id"] == 4 && $schedule["shift_type_id"] == 6) {

            $LateComing = $this->calculatedLateComing($arr["in"], $nearestShift["on_duty_time"], $nearestShift["late_time"]);

            if ($LateComing) {
                $arr["late_coming"] = $LateComing;
            }
        }

        if ($count > 1) {
            $arr["status"] = "P";
            $arr["device_id_out"] = $data[1]["DeviceID"];
            $arr["out"] = $data[1]["time"];
            $arr["total_hrs"] = $this->getTotalHrsMins($data[0]["time"], $data[1]["time"]);

            if ($schedule["isOverTime"]) {
                $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $nearestShift['working_hours'], $nearestShift['overtime_interval']);
            }

            if ($nearestShift["shift_type_id"] == 4 && $nearestShift["shift_type_id"] == 6) {
                $EarlyGoing = $this->calculatedEarlyGoing($arr["in"], $nearestShift["off_duty_time"], $nearestShift["early_time"]);

                if ($EarlyGoing) {
                    // $arr["status"] = "A";
                    $arr["early_going"] = $EarlyGoing;
                }
            }
        }

        try {

            $attendance = Attendance::firstOrNew([
                'date' => $arr['date'],
                'employee_id' => $arr['employee_id'],
                'company_id' => $arr['company_id'],
            ]);

            $attendance->fill($arr)->save();


            if (!$attendance) {
                return "The Logs cannnot render against " . $arr['employee_id'] . " SYSTEM USER ID on $date.";
            }
            return "The Logs has been render against " . $arr['employee_id'] . " SYSTEM USER ID on $date.";
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function renderAuto(Request $request)
    {
        $this->reason = $request->reason ?? null;

        $this->updated_by = $request->updated_by ?? 0;

        $date = $request->date;

        $company_id = $request->company_id;

        $UserID = $request->UserID;

        $schedule = $this->getScheduleAuto($date, $company_id, $UserID);

        if (!$schedule) {
            return $this->response("Employee with $UserID SYSTEM USER ID is not scheduled yet.", null, false);
        }

        $model = AttendanceLog::query();

        $model->whereDate("LogTime", $date);
        $model->where("company_id", $company_id);
        $model->where("UserID", $UserID);
        $model->distinct("LogTime");
        $count = $model->count();
        $data = [$model->clone()->orderBy("LogTime")->first(), $model->orderBy("LogTime", "desc")->first()];

        if (!$data) {
            return ["error" => true, "message" => "Employee with $UserID SYSTEM USER ID has no Log(s)."];
        }

        $shifts = $this->getShifts($company_id);

        $nearestShift = $this->findClosest($shifts, count($shifts), $data[0]["show_log_time"], $date);

        $arr = [];
        $arr["company_id"] = $company_id;
        $arr["date"] = $date;
        $arr["employee_id"] = $UserID;
        $arr["shift_type_id"] = $nearestShift["shift_type_id"];
        $arr["shift_id"] = $nearestShift["id"];
        $arr["device_id_in"] = $data[0]["DeviceID"];
        $arr["in"] = $data[0]["time"];
        $arr["status"] = "M";
        $arr["is_manual_entry"] = true;

        if ($schedule["shift_type_id"] == 4 && $schedule["shift_type_id"] == 6) {

            $LateComing = $this->calculatedLateComing($arr["in"], $nearestShift["on_duty_time"], $nearestShift["late_time"]);

            if ($LateComing) {
                $arr["late_coming"] = $LateComing;
            }
        }

        if ($count > 1) {
            $arr["status"] = "P";
            $arr["device_id_out"] = $data[1]["DeviceID"];
            $arr["out"] = $data[1]["time"];
            $arr["total_hrs"] = $this->getTotalHrsMins($data[0]["time"], $data[1]["time"]);

            if ($schedule["isOverTime"]) {
                $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $nearestShift['working_hours'], $nearestShift['overtime_interval']);
            }

            if ($nearestShift["shift_type_id"] == 4 && $nearestShift["shift_type_id"] == 6) {
                $EarlyGoing = $this->calculatedEarlyGoing($arr["in"], $nearestShift["off_duty_time"], $nearestShift["early_time"]);

                if ($EarlyGoing) {
                    // $arr["status"] = "A";
                    $arr["early_going"] = $EarlyGoing;
                }
            }
        }
        return $this->storeOrUpdate($arr);
    }

    public function createReason($id)
    {
        if (empty($this->reason)) {
            return false;
        }

        try {
            Reason::create([
                'reason' => $this->reason,
                'user_id' => $this->updated_by,
                'reasonable_id' => $id,
                'reasonable_type' => "App\Models\Attendance",
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getLogs($currentDate, $company_id, $UserID)
    {
        $model = AttendanceLog::query();

        $model->with("device");
        $model->where("company_id", $company_id);
        $model->where("UserID", $UserID);
        $model->whereDate("LogTime", $currentDate);
        $model->distinct("LogTime");
        $model->orderBy("LogTime");

        return $model->get(["LogTime", "DeviceID", "UserID", "company_id", "gps_location"])->toArray();
    }

    public function getScheduleMultiInOut($currentDate, $companyId, $UserID, $shift_type_id)
    {
        return $schedule = ScheduleEmployee::where('company_id', $companyId)
            ->where("employee_id", $UserID)
            ->where("shift_type_id", $shift_type_id)
            ->first();

        return $this->getSchedule($currentDate, $schedule);
    }

    public function getScheduleGeneral($currentDate, $companyId, $UserID)
    {
        $schedule = ScheduleEmployee::where('company_id', $companyId)
            ->where("employee_id", $UserID)
            ->whereNot("shift_type_id", 2)
            ->first();

        return $this->getSchedule($currentDate, $schedule);
    }

    public function getScheduleAuto($currentDate, $companyId, $UserID)
    {
        $schedule = ScheduleEmployee::where('company_id', $companyId)
            ->where("employee_id", $UserID)
            ->where("shift_id", -2)
            ->first();

        return $this->getSchedule($currentDate, $schedule);
    }

    public function processLogs($data, $schedule)
    {
        $logs = [];
        $total_hrs = 0;
        $ot = 0;
        $totalMinutes = 0;

        for ($i = 0; $i < count($data); $i++) {

            $currentLog = $data[$i];
            $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;

            $logs[] = [
                "in" => $currentLog['time'],
                "out" => $nextLog && $nextLog['time'] ? $nextLog['time'] : "---",
                "gps_location_in" => $currentLog && $currentLog['gps_location'] ? $currentLog['gps_location'] : $currentLog["device"]["name"],
                "gps_location_out" => $nextLog && $nextLog['gps_location'] ? $nextLog['gps_location'] : $currentLog["device"]["name"],
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

            $total_hrs = $this->minutesToHours($totalMinutes);

            if ($schedule['isOverTime']) {
                $ot = $this->calculatedOT($total_hrs, $schedule['working_hours'], $schedule['overtime_interval']);
            }

            $i++;
        }

        return ["logs" => $logs, "total_hrs" => $total_hrs, "ot" => $ot];
    }

    public function storeOrUpdate($items)
    {
    }

    public function renderOff(Request $request)
    {
        $UserIds = $this->renderOffScript($request->company_id, $request->date, $request->UserID);

        $result = json_encode($UserIds);

        return $this->response("$result Employee has been marked as OFF", null, true);
    }

    public function renderLeaves($company_id = 0, Request $request)
    {
        $schedule = null;
        //if not schedule return nothing
        if ($request->ShiftTypeId == 2) {
            $schedule = $this->getScheduleMultiInOut($request->date, $company_id, $request->UserID, $request->ShiftTypeId);
        } else {
            $schedule = $this->getScheduleGeneral($request->date, $company_id, $request->UserID);
        }

        if (!$schedule) {
            return $this->response("Employee with $request->UserID SYSTEM USER ID is not scheduled yet.", null, false);
        }

        return $this->renderLeavesScript($company_id, $request->date, $request->UserID);
    }
    public function renderHolidays($company_id = 0, Request $request)
    {
        return $this->renderHolidaysScript($company_id, $request->date, $request->UserID);
    }
    public function renderOffCron($company_id = 0)
    {
        $UserIds = $this->renderOffScript($company_id, date("Y-m-d"));

        $result = json_encode($UserIds);

        return $this->getMeta("Sync Off", "$result Employee has been marked as OFF" . ".\n");
    }

    public function renderLeavesCron($company_id = 0)
    {
        $todayDate = date('Y-m-d', strtotime('-1 day'));

        $model = EmployeeLeaves::with(["employee", "leave_type"])
            ->where('company_id', $company_id)
            ->where('status', 1)
            ->where('start_date', '<=', $todayDate)
            ->where('end_date', '>=', $todayDate);

        $employees = $model->get();

        $userIDs = [];
        foreach ($employees as $key => $value) {

            if ($value->employee->system_user_id) { {

                    $userIDs[] = $this->renderLeavesScript($company_id, $todayDate, $value->employee->system_user_id, $value->leave_type->name);
                }
            }
        }
        $result = json_encode($userIDs);

        return $this->getMeta("Sync Leaves", "$result Employee has been marked as Leave" . ".\n");
    }
    public function renderHolidaysCron($company_id = 0)
    {
        $todayDate = date('Y-m-d', strtotime('-1 day'));

        $holidayCount = Holidays::where('company_id', $company_id)
            ->where('start_date', '>=', $todayDate)
            ->where('end_date', '<=', $todayDate)->get()->count();

        if ($holidayCount) {
            $employees = Employee::where('company_id', $company_id)
                // ->where('status', 1)
                ->get();
            $userIDs = [];
            foreach ($employees as $key => $value) {

                if ($value->system_user_id) {
                    $userIDs[] = $this->renderHolidaysScript($company_id, $todayDate, $value->system_user_id);
                }
            }
            $result = json_encode($userIDs);

            return $this->getMeta("Sync Holidays", "$todayDate :  $result Employee has been marked as Holiday" . ".\n");
        }
        return $this->getMeta("Sync Holidays", "$todayDate : No Holiday" . ".\n");
    }

    public function renderOffScript($company_id, $date, $user_id = 0)
    {
        try {
            $model = ScheduleEmployee::query();

            $model->where("company_id", $company_id);

            $model->when($user_id, function ($q) use ($user_id) {
                $q->where("employee_id", $user_id);
            });

            $model->where(function ($q) use ($date) {
                $q->where('from_date', '<=', $date)
                    ->where('to_date', '>=', $date);
            });

            $model->when(!$user_id, function ($q) {
                $q->where("shift_id", -1);
            });

            $employees = $model->distinct("employee_id")->get(["employee_id", "shift_type_id"]);

            $records = [];

            foreach ($employees as $employee) {
                $records[] = [
                    "company_id" => $company_id,
                    "date" => $date,
                    "status" => "O",
                    "employee_id" => $employee->employee_id,
                    "shift_id" => -1,
                    "shift_type_id" => $employee->shift_type_id,
                ];
            }

            $model = Attendance::query();
            // $model->where("shift_id", -1);
            $model->where("company_id", $company_id);
            $model->where("date", $date);
            $model->whereIn("status", ["P", "A", "M", "O"]);

            $model->when($user_id, function ($q) use ($user_id) {
                return $q->where("employee_id", $user_id);
            });

            $model->delete();

            $model->insert($records);

            $UserIds = array_column($records, "employee_id");

            return $UserIds;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function renderWeekendScript($company_id, $date, $user_id = 0)
    {
        $model = ScheduleEmployee::query();

        $model->where("company_id", $company_id);

        $model->whereHas("shift", fn ($q) => $q->where("weekend1", "Not Applicable"));

        $model->when($user_id, function ($q) use ($user_id) {
            return $q->where("employee_id", $user_id);
        });

        $model->whereDoesntHave("attendance_logs", function ($q) use ($company_id, $date) {
            $q->whereDate('LogTime', $date);
            $q->where("company_id", $company_id);
        });

        $model->where(function ($q) use ($date) {
            $q->where('from_date', '<=', $date)
                ->where('to_date', '>=', $date);
        });

        $missingEmployees = $model->distinct("employee_id")->get(["employee_id", "shift_type_id"]);

        $records = [];

        foreach ($missingEmployees as $missingEmployee) {
            $records[] = [
                "company_id" => $company_id,
                "date" => $date,
                "status" => "A",
                "employee_id" => $missingEmployee->employee_id,
                "shift_id" => $missingEmployee->shift_id,
                "shift_type_id" => $missingEmployee->shift_type_id,
            ];
        }

        $UserIds = array_column($records, "employee_id");

        try {
            if (count($records)) {
                $model = Attendance::query();
                $model->where("company_id", $company_id);
                $model->where("date", $date);
                $model->whereIn("employee_id", $UserIds);
                $model->delete();
                $model->insert($records);
            }

            return $UserIds;
        } catch (\Exception $e) {
            return $e;
        }
    }
    public function renderLeavesScript($company_id, $date, $user_id = 0, $leave_type_name = '')
    {
        try {
            $model = ScheduleEmployee::query();

            $model->where("company_id", $company_id);

            $model->when($user_id, function ($q) use ($user_id) {
                $q->where("employee_id", $user_id);
            });

            $model->when(!$user_id, function ($q) {
                $q->where("shift_id", -3);
            });

            $employees = $model->latest()->first(["employee_id", "shift_type_id"]);

            $records = [];

            // foreach ($employees as $employee)
            {
                if ($leave_type_name != '' && strpos(strtolower($leave_type_name), 'vacation') > -1) {

                    $records[] = [
                        "company_id" => $company_id,
                        "date" => $date,
                        "status" => "V",
                        "employee_id" => $employees->employee_id,
                        "shift_id" => -3,
                        "shift_type_id" => $employees->shift_type_id,
                    ];
                } else {

                    $records[] = [
                        "company_id" => $company_id,
                        "date" => $date,
                        "status" => "L",
                        "employee_id" => $employees->employee_id,
                        "shift_id" => -3,
                        "shift_type_id" => $employees->shift_type_id,
                    ];
                }
            }

            $model = Attendance::query();
            // $model->where("shift_id", -1);
            $model->where("company_id", $company_id);
            $model->where("date", $date);
            $model->whereIn("status", ["P", "A", "M", "O", "L", "H", "V"]);

            $model->when($user_id, function ($q) use ($user_id) {
                return $q->where("employee_id", $user_id);
            });

            $model->delete();

            $model->insert($records);

            $UserIds = array_column($records, "employee_id");

            return $UserIds;
        } catch (\Exception $e) {
            return $e;
        }
    }
    public function renderHolidaysScript($company_id, $date, $user_id = 0)
    {
        try {
            $model = ScheduleEmployee::query();

            $model->where("company_id", $company_id);

            $model->when($user_id, function ($q) use ($user_id) {
                $q->where("employee_id", $user_id);
            });

            $model->when(!$user_id, function ($q) {
                $q->where("shift_id", -4);
            });

            $employees = $model->latest()->first(["employee_id", "shift_type_id"]);

            $records = [];

            // foreach ($employees as $employee)
            {

                $records[] = [
                    "company_id" => $company_id,
                    "date" => $date,
                    "status" => "H",
                    "employee_id" => $employees->employee_id,
                    "shift_id" => -4,
                    "shift_type_id" => $employees->shift_type_id,
                ];
            } {

                $model = Attendance::query();
                // $model->where("shift_id", -1);
                $model->where("company_id", $company_id);
                $model->where("date", $date);
                $model->whereIn("status", ["P", "A", "M", "O", "L", "H", "V"]);

                $model->when($user_id, function ($q) use ($user_id) {
                    return $q->where("employee_id", $user_id);
                });

                $model->delete();

                $model->insert($records);

                $UserIds = $employees->employee_id; // array_column($records, "employee_id");
                if ($UserIds) {
                    return $UserIds;
                }
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function deleteOldRecord($items)
    {
        try {
            $model = Attendance::query();
            $model->whereDate("date", $items['date']);
            $model->where("employee_id", $items['employee_id']);
            $model->where("company_id", $items['company_id'])->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function renderEmployeeReport(Request $request)
    {
        // Define the validation rules
        $rules = [
            'dates' => 'required|array',
            'dates.*' => 'string',
            'userIds' => 'required|array|max:' . $request->max ?? 10, // Must be an array
            'userIds.*' => 'numeric', // Each value in the array must be numeric
            'date' => 'required|date', // Must be a valid date format
            'company_id' => 'required|numeric', // Must be numeric
        ];

        // Define custom error messages for the 'date' rule
        $customMessages = [
            'date.date' => 'The :attribute field must be a valid date format. E.g. ' . date("Y-m-d"),
        ];

        // Run the validation
        $validator = Validator::make($request->all(), $rules, $customMessages);

        // Check if validation fails    
        if ($validator->fails()) {
            // If validation fails, return the error response
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $start_date = $request->dates[0];
        $end_date = $request->dates[1];

        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        $dates = [];

        while ($start->lte($end)) {
            $dates[] = $start->toDateString();
            $start->addDay();
        }

        $userIds = $request->userIds ?? [1001, 1006, 1005, 670, 1002, 1003, 1004, 1007];
        $date = $request->date ?? date("Y-m-d");
        $company_id = $request->company_id ?? 8;
        $shift_type_id = $request->shift_type_id ?? 2;
        $statuses = $request->statuses ?? ['A', 'P', 'M', 'O', 'L', 'V', 'H'];



        $response = $this->runEmployeeFunc($userIds, $date, $shift_type_id, $statuses, $company_id, $dates);

        return $this->response("Employee Data has been generated", $response, true);
    }

    public function runEmployeeFunc($userIds, $date, $shift_type_id, $statuses, $company_id, $dates)
    {
        $arr = [];

        foreach ($dates as $monthDate) {
            foreach ($userIds as $userId) {
                $in = $this->generateRandomTime('09:30', '14:00');
                $out = $this->generateRandomTime('16:30', '21:30');
                $arr[]  = [
                    'date' => $monthDate,
                    'employee_id' => $userId,
                    'shift_id' => 0,
                    'shift_type_id' => $shift_type_id,
                    'status' => $statuses[array_rand($statuses)],
                    'in' => $in,
                    'out' => $out,
                    'total_hrs' => $this->calculateTotalHours($in, $out),
                    'device_id_in' => "OX-8862021010010",
                    'device_id_out' => "OX-8862021010010",
                    'date_in' => $monthDate,
                    'date_out' => $monthDate,
                    'company_id' => $company_id
                ];
            }
        }



        $model = Attendance::query();
        $model->whereIn("employee_id", $userIds)->whereDate("date", $date)->delete();
        $model->insert($arr);

        return $arr;
    }

    public function findClosest($arr, $n, $target, $date)
    {
        if (count($arr) == 0) return false;

        // Corner cases
        if ($target <= $this->getItemByIndex($arr, 0, $date)) {
            return $arr[0];
        }

        if ($target >= $this->getItemByIndex($arr, $n - 1, $date)) {
            return $arr[$n - 1];
        }

        // Doing binary search
        $i = 0;
        $j = $n;
        $mid = 0;
        while ($i < $j) {
            $mid = ($i + $j) / 2;

            if ($this->getItemByIndex($arr, $mid, $date) == $target) {
                return $arr[$mid];
            }

            /* If target is less than array element,
            then search in left */
            if ($target < $this->getItemByIndex($arr, $mid, $date)) {

                // If target is greater than previous
                // to mid, return closest of two
                if ($mid > 0 && $target > $this->getItemByIndex($arr, $mid - 1, $date)) {
                    return $this->getClosest($arr[$mid - 1], $arr[$mid], $target, $date);
                }

                /* Repeat for left half */
                $j = $mid;
            }

            // If target is greater than mid
            else {
                if ($mid < $n - 1 && $target < $this->getItemByIndex($arr, $mid + 1, $date)) {
                    return $this->getClosest($arr[$mid], $arr[$mid + 1], $target, $date);
                }

                // update i
                $i = $mid + 1;
            }
        }

        // Only single element left after search
        return $arr[$mid];
    }

    public function getClosest($val1, $val2, $target, $date)
    {
        $v1 = strtotime($date . ' ' . $val1["on_duty_time"]);
        $v2 = strtotime($date . ' ' . $val2["on_duty_time"]);

        return ($target - $v1 > $v2 - $target) ? $val2 : $val1;
    }
    public function getItemByIndex($arr, $index, $date)
    {
        $dateTime = $date . ' ' . $arr[$index]["on_duty_time"];

        return strtotime($dateTime);
    }
    public function getShifts($companyId)
    {
        return Shift::orderBy("on_duty_time")->whereHas("autoshift", function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get()->toArray();
    }
}
