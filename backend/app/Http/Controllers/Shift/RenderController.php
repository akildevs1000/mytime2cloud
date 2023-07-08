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
use App\Models\Schedule;

class RenderController extends Controller
{

    public $update_date;

    public function renderMultiInOut(Request $request)
    {

        $shift_type_id = 2;

        $company_id = $request->company_id;

        $UserID = $request->UserID;

        $currentDate = $request->date ?? date('Y-m-d');

        $schedule = $this->getSchedule($currentDate, $company_id, $UserID, $shift_type_id);

        if (!$schedule) {
            return $this->response("Employee with $UserID SYSTEM USER ID is not scheduled yet.", null, false);
        }

        $data = $this->getLogs($currentDate, $company_id, $UserID, $shift_type_id);

        $AttendancePayload = [
            "status" => count($data)  % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
            "shift_type_id" => $shift_type_id,
            "date" => $currentDate,
            "company_id" => $company_id,
            "employee_id" => $UserID,
            "shift_id" => $schedule['shift_id'],
            "roster_id" => $schedule['roster_id'],
        ];

        $logs = $this->processLogs($data, $schedule);

        $result = $this->storeOrUpdate($AttendancePayload + $logs);

        if (!$result) {
            return $this->response("The Logs cannnot render against $UserID SYSTEM USER ID.", null, false);
        }
        return $this->response("The Logs has been render against $UserID SYSTEM USER ID.", null, true);
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

        $model->select('LogTime', 'UserID', 'company_id', 'DeviceID')->distinct();

        $model->orderBy("LogTime");

        return $model->get()->toArray();
    }

    public function getSchedule($currentDate, $companyId, $UserID, $shift_type_id)
    {
        $schedule = ScheduleEmployee::where('company_id', $companyId)
            ->where("employee_id", $UserID)
            ->where("shift_type_id", $shift_type_id)
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

            return true;
        } catch (\Exception $e) {
            return false;
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
