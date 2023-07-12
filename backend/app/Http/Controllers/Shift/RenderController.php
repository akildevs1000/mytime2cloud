<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\ScheduleEmployee;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Reason;
use App\Models\Schedule;

class RenderController extends Controller
{

    public $manual_entry;

    public $reason;

    public $updated_by;

    public function renderMultiInOut(Request $request)
    {
        $shift_type_id = 2;

        $this->manual_entry = $request->manual_entry ?? false;

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
            "status" => count($data)  % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
            "shift_type_id" => $shift_type_id,
            "date" => $currentDate,
            "company_id" => $company_id,
            "employee_id" => $UserID,
            "shift_id" => $schedule['shift_id'],
            "roster_id" => $schedule['roster_id'],
            "is_manual_entry" => $this->manual_entry,
        ];

        $logs = $this->processLogs($data, $schedule);

        $result = $this->storeOrUpdate($AttendancePayload + $logs);

        if (!$result) {
            return $this->response("The Logs cannnot render against $UserID SYSTEM USER ID.", null, false);
        }
        return $this->response("The Logs has been render against $UserID SYSTEM USER ID.", null, true);
    }

    public function renderGeneral(Request $request)
    {
        $this->manual_entry = $request->manual_entry ?? false;

        $this->reason = $request->reason ?? null;

        $this->updated_by = $request->updated_by ?? 0;

        $date       = $request->date;

        $company_id = $request->company_id;

        $UserID     = $request->UserID;

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
            return $this->response("No Logs found", null, false);
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
        $arr["status"] = "P";


        if ($schedule["shift_type_id"] == 4 && $schedule["shift_type_id"] == 6) {

            $LateComing = $this->calculatedLateComing($arr["in"], $schedule["on_duty_time"], $schedule["late_time"]);

            if ($LateComing) {
                // $arr["status"] = "A";
                $arr["late_coming"] = $LateComing;
            }
        }

        if ($count > 1) {
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
            $arr["is_manual_entry"] = $this->manual_entry;
        }

        try {

            Attendance::where([
                'date' => $arr["date"],
                'employee_id' => $arr["employee_id"],
                'company_id' => $arr['company_id']
            ])->delete();

            Attendance::create($arr);
            return $this->response("The Log(s) has been render against {$request->UserID} SYSTEM USER ID.", null, true);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getLogs($currentDate, $company_id, $UserID, $shift_type_id)
    {
        $model = AttendanceLog::query();

        $model->whereHas("schedule", function ($q) use ($shift_type_id, $currentDate) {
            $q->where('shift_type_id', $shift_type_id);
            $q->whereDate('from_date', "<=", $currentDate);
            $q->whereDate('to_date', ">=", $currentDate);
        });

        $model->where("company_id", $company_id);
        $model->where("UserID", $UserID);
        $model->whereDate("LogTime", $currentDate);

        $model->distinct("LogTime");

        $model->orderBy("LogTime");

        return $model->get(["LogTime", "DeviceID", "UserID", "company_id"])->toArray();
    }

    public function getScheduleMultiInOut($currentDate, $companyId, $UserID, $shift_type_id)
    {
        $schedule = ScheduleEmployee::where('company_id', $companyId)
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
                "out" =>  $nextLog && $nextLog['time'] ? $nextLog['time'] : "---",
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
        try {
            $this->deleteOldRecord($items);

            $attendance = Attendance::firstOrNew([
                'date' => $items['date'],
                'employee_id' => $items['employee_id'],
                'company_id' => $items['company_id']
            ]);

            $attendance->fill($items)->save();

            if ($this->manual_entry) {

                Reason::create([
                    'reason' => $this->reason,
                    'user_id' => $this->updated_by,
                    'reasonable_id' => $attendance->id,
                    'reasonable_type' => "App\Models\Attendance",
                ]);
            }

            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function renderOff(Request $request, $company_id = 0)
    {
        $date = $request->date ?? date("Y-m-d");

        $UserIds = $request->UserIds ?? [];

        $msg =  $this->renderOffScript($company_id, $date, $UserIds);

        return $msg;
    }

    public function renderOffCron($company_id = 0)
    {
        $msg =  $this->renderOffScript($company_id,  date("Y-m-d"));

        return $this->getMeta("Sync Off", $msg . ".\n");
    }

    public function renderOffScript($company_id, $date, $UserIds = [])
    {
        try {
            $model = ScheduleEmployee::query();
            $model->where("shift_id", -1);
            $model->where("company_id", $company_id);
            $model->when(count($UserIds), function ($q) use ($UserIds) {
                return $q->whereIn("employee_id", $UserIds);
            });

            $employees = $model->get(["employee_id", "shift_type_id"]);

            $records = [];

            foreach ($employees as $employee) {
                $records[] = [
                    "company_id" => $company_id,
                    "date" => $date,
                    "status" => "O",
                    "employee_id" => $employee->employee_id,
                    "shift_id" => $employee->employee_id,
                    "shift_type_id" => $employee->shift_type_id,
                ];
            }

            $model = Attendance::query();
            // $model->where("shift_id", -1);
            $model->where("company_id", $company_id);
            $model->where("date", $date);
            $model->where("status", "O");

            $model->when(count($UserIds), function ($q) use ($UserIds) {
                return $q->whereIn("employee_id", $UserIds);
            });

            $model->delete();

            $model->insert($records);

            $UserIds = array_column($records, "employee_id");

            $NumberOfEmployee = count($records);

            return "$NumberOfEmployee Employee has been marked as OFF: " . json_encode($UserIds);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function renderAbsent(Request $request, $company_id = 0)
    {
        $msg = $this->renderAbsentScript($company_id, $request->date);

        return $msg;
    }

    public function renderAbsentCron($company_id = 0)
    {
        $msg =  $this->renderAbsentScript($company_id, date('Y-m-d', strtotime('-1 day')));

        return $this->getMeta("Sync Absent", $msg . ".\n");
    }

    public function renderAbsentScript($company_id, $date)
    {
        $model = ScheduleEmployee::query();

        $model->where("company_id", $company_id);

        $model->whereNot("shift_id", -1);

        $model->whereDoesntHave("attendance_logs", function ($q) use ($company_id, $date) {
            $q->whereDate('LogTime', $date);
            $q->where("company_id", $company_id);
        });

        $missingEmployees = $model->get(["employee_id", "shift_type_id"]);

        $records = [];

        foreach ($missingEmployees as $missingEmployee) {
            $records[] = [
                "company_id" => $company_id,
                "date" => $date,
                "status" => "A",
                "employee_id" => $missingEmployee->employee_id,
                "shift_id" => -2,
                "shift_type_id" => $missingEmployee->shift_type_id,
            ];
        }

        if (!count($records)) {
            return "No employee(s) found";
        }

        try {

            $UserIds = array_column($records, "employee_id");

            $model = Attendance::query();
            $model->where("company_id", $company_id);
            $model->where("date", $date);
            $model->whereIn("employee_id", $UserIds);
            $model->delete();
            $model->insert($records);

            $NumberOfEmployee = count($records);

            return "$NumberOfEmployee employee(s) absent. Employee IDs: " . json_encode($UserIds);
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
}
