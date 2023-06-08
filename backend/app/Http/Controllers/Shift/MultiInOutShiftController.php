<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\ScheduleEmployee;
use App\Http\Controllers\Controller;
use App\Models\Company;

class MultiInOutShiftController extends Controller
{
    private const SHIFT_TYPE = 2;

    public function processByManual(Request $request)
    {
        $result = 0;

        $companyIds = $request->input('company_ids', []);
        $userIds = $request->input('userIds', []);
        $currentDate = $request->input('date', date('Y-m-d'));

        $companies = Company::with(["shift" => function ($q) {
            $q->where("shift_type_id", self::SHIFT_TYPE);
            $q->select(["on_duty_time", "off_duty_time", "company_id"]);
        }])->whereIn("id", $companyIds)->get("id");

        foreach ($companies as $company) {
            $result += $this->getModelData($currentDate, $company, $userIds);
        }

        return "Logs Count " . $result;
    }

    public function getModelData($currentDate, $company, $userIds)
    {
        $id = $company->id;
        if ($company->shift) {
            $in = $company->shift->on_duty_time;
            $out = $company->shift->off_duty_time;

            $currentDate = $in < $out ? date('Y-m-d', strtotime('yesterday')) : date('Y-m-d');

            $model = AttendanceLog::query();

            $model->where(function ($q) use ($currentDate, $id, $userIds) {
                $q->where("checked", false);
                $q->where("company_id", '>', 0);
                $q->whereHas("schedule", function ($q) use ($currentDate) {
                    $q->where('shift_type_id', self::SHIFT_TYPE);
                    $q->whereDate('from_date', "<=", $currentDate);
                    $q->whereDate('to_date', ">=", $currentDate);
                });
                $q->where("company_id", $id);
                $q->whereIn("UserID", $userIds);

                $q->whereDate("LogTime", $currentDate);
            });

            $nextDate = date('Y-m-d', strtotime($currentDate . '+ 1 day'));

            $model->orWhere(function ($q) use ($nextDate, $id, $userIds) {
                $q->where("checked", false);
                $q->where("company_id", '>', 0);
                $q->whereHas("schedule", function ($q) use ($nextDate) {
                    $q->where('shift_type_id', self::SHIFT_TYPE);
                    $q->whereDate('from_date', "<=", $nextDate);
                    $q->whereDate('to_date', ">=", $nextDate);
                });
                $q->where("company_id", $id);
                $q->whereIn("UserID", $userIds);

                $q->whereDate("LogTime", $nextDate);
            });

            $model->orderBy("LogTime");

            $data = $model->get(["UserID"])->groupBy(["UserID"])->toArray();

            return $this->processData($currentDate, $company->id, $data);
        }
    }

    public function getSchedule($currentDate, $companyId, $UserID)
    {
        $schedule = ScheduleEmployee::where('company_id', $companyId)
            ->where("employee_id", $UserID)
            ->where("shift_type_id", self::SHIFT_TYPE)
            ->first();

        if (!$schedule || !$schedule->shift) {
            return false;
        }

        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        $start_range = $currentDate . " " . $schedule->shift->on_duty_time;

        $end_range = $nextDate . " " . $schedule->shift->off_duty_time;

        return [
            "roster_id" => $schedule["roster_id"],
            "shift_id" => $schedule["shift_id"],
            "range" => [$start_range, $end_range],
            "isOverTime" => $schedule["isOverTime"],
            "working_hours" => $schedule["shift"]["working_hours"],
            "overtime_interval" => $schedule["shift"]["overtime_interval"],
        ];
    }

    public function getLogsWithInRange($companyId, $UserID, $range)
    {
        $model = AttendanceLog::query();
        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', self::SHIFT_TYPE);
        });
        $model->where("company_id", $companyId);
        $model->where("UserID", $UserID);
        $model->whereBetween("LogTime", $range);

        $model->orderBy("LogTime");

        return $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);
    }

    public function processData($date, $companyId, $data)
    {
        $temp = [];
        $items = [];
        $UserIDs = [];

        foreach ($data as $UserID => $data) {
            $schedule = $this->getSchedule($date, $companyId, $UserID);

            if (!$schedule) {
                continue;
            }

            $UserIDs[] = $UserID;

            $data = $this->getLogsWithInRange($companyId, $UserID, $schedule["range"]);

            $temp = [
                "status" => count($data) == 1 ?  Attendance::MISSING : Attendance::PRESENT,
                // "status" => count($data)  % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
                "shift_type_id" => self::SHIFT_TYPE,
                "date" => $date,
                "company_id" => $companyId,
                "shift_id" => $schedule['shift_id'],
                "roster_id" => $schedule['roster_id'],
                "employee_id" => $UserID,
                "logs" => [],
                "total_hrs" => 0,
            ];

            $totalMinutes = 0;

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

                    if ($parsed_in > $parsed_out) {
                        $parsed_out += 86400;
                    }

                    $diff = $parsed_out - $parsed_in;

                    $minutes = floor($diff / 60);

                    $totalMinutes += $minutes > 0 ? $minutes : 0;
                }

                $temp["total_hrs"] = $this->minutesToHours($totalMinutes);

                if ($schedule['isOverTime']) {
                    $temp["ot"] = $this->calculatedOT($temp["total_hrs"], $schedule['working_hours'], $schedule['overtime_interval']);
                }

                $this->storeOrUpdate($temp);
                $items[] = $temp;

                $i++;
            }
        }

        return AttendanceLog::whereIn("UserID",  $UserIDs)->whereDate("LogTime", $date)->where("company_id", $companyId)->update(["checked" => true]);

        return $items;
    }
    public function storeOrUpdate($items)
    {
        $attendance = Attendance::whereDate("date", $items['date'])->where("employee_id", $items['employee_id'])->where("company_id", $items['company_id']);
        $found = $attendance->first();
        return $found ? $attendance->update($items) : Attendance::create($items);
    }

    public function syncLogsScript()
    {
        $result = 0;

        $companyIds = Company::pluck("id") ?? [];
        $userIds = [];
        $currentDate = date('Y-m-d');

        $companies = Company::with(["shift" => function ($q) {
            $q->where("shift_type_id", self::SHIFT_TYPE);
            $q->select(["on_duty_time", "off_duty_time", "company_id"]);
        }])->whereIn("id", $companyIds)->get("id");

        foreach ($companies as $company) {
            $result += $this->getModelData($currentDate, $company, $userIds);
        }

        return "Logs Count " . $result;
    }
}
