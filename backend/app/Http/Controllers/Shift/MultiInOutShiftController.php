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

class MultiInOutShiftController extends Controller
{

    public $update_date;

    public function processByManual(Request $request)
    {
        $companyIds = $request->company_ids ?? [];
        $UserIDs = $request->UserIDs ?? [];

        $currentDate = $request->date ?? date('Y-m-d');

        $arr = [];

        $companies = $this->getModelDataByCompanyId($currentDate, $companyIds, $UserIDs);

        foreach ($companies as $company_id => $data) {
            $arr[] = $this->processData($company_id, $data, $currentDate);
        }
        // return $arr;
        return "Logs Count " . array_sum($arr);
    }

    public function getModelDataByCompanyId($currentDate, $companyIds, $UserIDs)
    {
        $model = AttendanceLog::query();
        $model->where("checked", false);
        $model->where("company_id", '>', 0);

        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', 2);
        });

        $model->when(count($companyIds) > 0, function ($q) use ($companyIds) {
            $q->whereIn("company_id", $companyIds);
        });

        $model->when(count($UserIDs) > 0, function ($q) use ($UserIDs) {
            $q->whereIn("UserID", $UserIDs);
        });

        $model->whereDate("LogTime", $currentDate);

        $model->orderBy("LogTime");

        return $model->get(["UserID", "company_id"])->groupBy(["company_id", "UserID"])->toArray();
    }

    public function getSchedule($currentDate, $companyId, $UserID)
    {
        $schedule = ScheduleEmployee::withOut(["logs", "first_log", "last_log"])
            ->where('company_id', $companyId)
            ->where("employee_id", $UserID)
            ->where("shift_type_id", 2)
            ->first();

        if (!$schedule || !$schedule->shift) {
            return false;
        }

        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        $start_range = $currentDate . " " . $schedule->shift->on_duty_time;

        $end_range = $nextDate . " " . $schedule->shift->off_duty_time;

        return [
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
            $q->where('shift_type_id', 2);
        });
        $model->where("company_id", $companyId);
        $model->where("UserID", $UserID);
        $model->whereBetween("LogTime", $range);

        $model->orderBy("LogTime");

        return $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);
    }

    public function processData($companyId, $data, $date)
    {
        $counter = 0;

        $temp = [];
        $logs = [];
        $total_hours = [];
        $items = [];
        $UserIDs = [];

        foreach ($data as $UserID => $data) {

            $schedule = $this->getSchedule($date, $companyId, $UserID);

            if (!$schedule) {
                continue;
            }

            $UserIDs[] = $UserID;

            $data = $this->getLogsWithInRange($companyId, $UserID, $schedule["range"]);

            $temp[$date][$UserID]["status"] = 'P';
            $temp[$date][$UserID]["shift_type_id"] = 2;
            $temp[$date][$UserID]["date"] = $date;
            $temp[$date][$UserID]["company_id"] = $companyId;
            $temp[$date][$UserID]["shift_id"] = $schedule['shift_id'];
            $temp[$date][$UserID]["employee_id"] = $UserID;

            $counter += count($data);

            for ($i = 0; $i < count($data); $i++) {
                $currentLog = $data[$i];
                $nextLog = isset($data[$i + 1]) ? $data[$i + 1] : false;

                $logs[$UserID][$date][] =  [
                    "in" => $currentLog['time'],
                    "out" =>  $nextLog && $nextLog['time'] ? $nextLog['time'] : "---",
                    "diff" => $nextLog ? $this->minutesToHoursNEW($currentLog['time'], $nextLog['time']) : "---",
                    // $currentLog['LogTime'], $nextLog['time'] ?? "---"
                ];

                $temp[$date][$UserID]["logs"] = $logs[$UserID][$date];


                if ((isset($currentLog['time']) && $currentLog['time'] != '---') and (isset($nextLog['time']) && $nextLog['time'] != '---')) {

                    $parsed_out = strtotime($nextLog['time'] ?? 0);
                    $parsed_in = strtotime($currentLog['time'] ?? 0);

                    if ($parsed_in > $parsed_out) {
                        $parsed_out += 86400;
                    }

                    $diff = $parsed_out - $parsed_in;

                    $mints =  floor($diff / 60);

                    $minutes = $mints > 0 ? $mints : 0;

                    $total_hours[$date][$UserID][] = $minutes;
                }

                if ($nextLog) {
                    $temp[$date][$UserID]["total_hrs"] = $this->minutesToHours(array_sum($total_hours[$date][$UserID]));
                }

                if ($schedule['isOverTime'] && array_key_exists("total_hrs", $temp[$date][$UserID])) {
                    $ot = $this->calculatedOT($temp[$date][$UserID]["total_hrs"], $schedule['working_hours'], $schedule['overtime_interval']);
                    $temp[$date][$UserID]["ot"] = $ot;
                }

                $this->storeOrUpdate($temp[$date][$UserID]);
                $items[] = $temp[$date][$UserID];

                $i++;
            }
        }
        AttendanceLog::whereIn("UserID",  $UserIDs)->whereDate("LogTime", $date)->update(["checked" => true]);
        return $counter;
    }
    public function storeOrUpdate($items)
    {
        $attendance = Attendance::whereDate("date", $items['date'])->where("employee_id", $items['employee_id'])->where("company_id", $items['company_id']);
        $found = $attendance->first();
        return $found ? $attendance->update($items) : Attendance::create($items);
    }

    public function minutesToHoursNEW($in, $out)
    {
        $parsed_out = strtotime($out);
        $parsed_in = strtotime($in);

        if ($parsed_in > $parsed_out) {
            $parsed_out += 86400;
        }

        $diff = $parsed_out - $parsed_in;

        $mints =  floor($diff / 60);

        $minutes = $mints > 0 ? $mints : 0;

        $newHours = intdiv($minutes, 60);
        $newMints = $minutes % 60;
        $final_mints =  $newMints < 10 ? '0' . $newMints :  $newMints;
        $final_hours =  $newHours < 10 ? '0' . $newHours :  $newHours;
        $hours = $final_hours . ':' . ($final_mints);
        return $hours;
    }


    public function minutesToHours($minutes)
    {
        $newHours = intdiv($minutes, 60);
        $newMints = $minutes % 60;
        $final_mints =  $newMints < 10 ? '0' . $newMints :  $newMints;
        $final_hours =  $newHours < 10 ? '0' . $newHours :  $newHours;
        $hours = $final_hours . ':' . ($final_mints);
        return $hours;
    }

    public function calculatedOT($total_hours, $working_hours, $interval_time)
    {

        $interval_time_num = date("i", strtotime($interval_time));
        $total_hours_num = strtotime($total_hours);

        $date = new \DateTime($working_hours);
        $date->add(new \DateInterval("PT{$interval_time_num}M"));
        $working_hours_with_interval = $date->format('H:i');


        $working_hours_num = strtotime($working_hours_with_interval);

        if ($working_hours_num > $total_hours_num) {
            return "---";
        }

        $diff = abs(((strtotime($working_hours)) - (strtotime($total_hours))));
        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function processPreviousDateByManual()
    {
        $companyIds = Company::pluck("id") ?? [];

        $UserIDs = [];

        $currentDate = date('Y-m-d');

        $arr = [];

        $companies = $this->getModelDataByCompanyId($currentDate, $companyIds, $UserIDs);

        foreach ($companies as $company_id => $data) {
            $arr[] = $this->processData($company_id, $data, $currentDate);
        }

        return "Logs Count " . array_sum($arr);
    }
}
