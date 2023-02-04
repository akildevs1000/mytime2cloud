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
use App\Models\Shift;

class AutoShiftController extends Controller
{

    public $update_date;

    public function processByManual(Request $request)
    {
        $shift_type_id = 3;

        $companyIds = $request->company_ids ?? [];

        $UserIDs = $request->UserIDs ?? [];

        $currentDate = $request->date ?? date('Y-m-d');

        $checked = $request->checked;

        $arr = [];
        $companies = $this->getModelDataByCompanyId($currentDate, $companyIds, $UserIDs, $shift_type_id);

        foreach ($companies as $company_id => $data) {
            // return ScheduleEmployee::whereCompanyId($company_id)->count();
            $arr[] = $this->processData($company_id, $data, $currentDate, $shift_type_id, $checked);
        }
        return $arr;
        return "Logs Count " . array_sum($arr);
    }

    public function getModelDataByCompanyId($currentDate, $companyIds, $UserIDs, $shift_type_id)
    {
        $model = AttendanceLog::query();

        $model->where(function ($q) use ($currentDate, $companyIds, $UserIDs, $shift_type_id) {

            $q->where("checked", false);

            $q->where("company_id", '>', 0);

            $q->whereHas("schedule", function ($q) use ($shift_type_id) {
                $q->where('shift_type_id', $shift_type_id);
            });

            $q->when(count($companyIds) > 0, function ($q) use ($companyIds) {
                $q->whereIn("company_id", $companyIds);
            });

            $q->when(count($UserIDs) > 0, function ($q) use ($UserIDs) {
                $q->whereIn("UserID", $UserIDs);
            });

            $q->whereBetween("LogTime", [$currentDate, date('Y-m-d', strtotime($currentDate . '+ 1 day'))]);
        });

        $model->orderBy("LogTime");

        return $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy(["company_id", "UserID"])->toArray();
    }

    public function getSchedule($currentDate, $companyId, $UserID, $shift_type_id)
    {
        $schedule = ScheduleEmployee::withOut(["logs", "first_log", "last_log"])
            ->where('company_id', $companyId)
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
            "shift_id" => $schedule["shift_id"],
            "range" => [$start_range, $end_range],
            "isOverTime" => $schedule["isOverTime"],
            "working_hours" => $schedule["shift"]["working_hours"],
            "overtime_interval" => $schedule["shift"]["overtime_interval"],
        ];
    }

    public function getLogsWithInRange($companyId, $UserID, $range, $shift_type_id)
    {
        $model = AttendanceLog::query();
        $model->whereHas("schedule", function ($q) use ($shift_type_id) {
            $q->where('shift_type_id', $shift_type_id);
        });
        $model->where("company_id", $companyId);
        $model->where("UserID", $UserID);
        $model->whereBetween("LogTime", $range);

        $model->orderBy("LogTime");

        return $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);
    }

    public function findAttendanceByUserId($item)
    {
        $prevDate =  date('Y-m-d', strtotime($item['date'] . ' - 1 day'));

        $model = Attendance::query();

        $model->where(function ($q) use ($item) {
            $q->where("employee_id", $item['employee_id']);
            $q->where("company_id", $item['company_id']);
            $q->where("date", $item['date']);
        });

        $model->orWhere(function ($q) use ($item, $prevDate) {
            $q->where("employee_id", $item['employee_id']);
            $q->where("company_id", $item['company_id']);
            $q->where("date", $prevDate);
        });

        if (!$model->first()) {
            return [
                "found" => false,
                "attendance" => $model
            ];
        }

        return [
            "found" => true,
            "attendance" => $model->with(["schedule", "shift"])->first()
        ];
    }

    public function getShifts($companyId)
    {
        return Shift::orderBy("on_duty_time")->whereHas("autoshift", function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get()->toArray();
    }

    public function processData($companyId, $data, $date, $shift_type_id, $checked = true)
    {

        $counter = 0;

        $temp = [];

        $items = [];

        $UserIDs = [];

        $shifts = $this->getShifts($companyId);

        $count = count($shifts);

        foreach ($data as $UserID => $logs) {

            if (count($logs) == 0) {
                continue;
            }

            $UserIDs[] = $UserID;

            $temp["company_id"] = $companyId;
            $temp["date"] = $date;
            $temp["employee_id"] = $UserID;

            $found = $this->findAttendanceByUserId($temp);


            if ($found["found"]) {
                $model = $found["attendance"];
                $last = array_reverse($logs)[0];

                $temp["shift_id"] = $model->shift_id;
                $temp["date"] = $model->date;
                $temp["out"] = $last["time"];
                $temp["device_id_out"] = $last["DeviceID"];
                $temp["total_hrs"] = $this->getTotalHrsMins($model->in, $last["time"]);

                $schedule = $model->schedule ?? false;

                $isOverTime = $schedule && $schedule->isOverTime ?? false;

                if ($isOverTime) {
                    $temp["ot"] = $this->calculatedOT($temp["total_hrs"], $schedule->working_hours, $schedule->overtime_interval);
                }

                $ifOutOfRange = $this->checkIfCurrentLogInPreviousShiftRange($model, $last, $date);

                if (!$ifOutOfRange) {
                    $model->update($temp);
                    $items[] = $temp;
                } else {
                    $nearestShift = $this->findClosest($shifts, $count, $logs[0]["show_log_time"], $date);
                    $temp["shift_type_id"] = $shift_type_id;
                    $temp["status"] = "P";
                    $temp["date"] = $date;
                    $temp["shift_id"] = $nearestShift["id"];
                    $temp["in"] = $logs[0]["time"];
                    $temp["device_id_in"] = $logs[0]["DeviceID"];

                    $items[] = $temp;

                    $model = $found["attendance"];
                    $model->create($temp);
                }
            } else {

                $nearestShift = $this->findClosest($shifts, $count, $logs[0]["show_log_time"], $date);
                $temp["shift_type_id"] = $shift_type_id;
                $temp["status"] = "P";
                $temp["shift_id"] = $nearestShift["id"];
                $temp["in"] = $logs[0]["time"];
                $temp["device_id_in"] = $logs[0]["DeviceID"];

                $items[] = $temp;

                $model = $found["attendance"];
                $model->create($temp);
            }
        }

        AttendanceLog::whereIn("UserID",  $UserIDs)->whereDate("LogTime", $date)->update(["checked" => $checked]);

        return $items;

        return $counter;
    }

    public function checkIfCurrentLogInPreviousShiftRange($model, $last)
    {

        if (!$model || !$model->shift) return false;

        $shift = $model->shift;

        $eIn = strtotime($shift->ending_in);

        if ($last["show_log_time"] < $eIn) return false;

        return $model->date == date("Y-m-d") ? false : true;
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

    public function syncLogsScript()
    {
        $shift_type_id = 3;

        $result = 0;

        $companyIds = Company::pluck("id") ?? [];

        $UserIDs = [];

        $currentTimestamp = date('Y-m-d H:i:s');

        $condtionTimestamp = date("Y-m-d 07:00");

        $currentDate = $currentTimestamp < $condtionTimestamp ? date('Y-m-d', strtotime('yesterday')) : date('Y-m-d');

        $companies = $this->getModelDataByCompanyId($currentDate, $companyIds, $UserIDs, $shift_type_id);

        foreach ($companies as $company_id => $data) {
            $result += $this->processData($company_id, $data, $currentDate, $shift_type_id);
        }

        return "Logs Count " . $result;
    }

    public function getItemByIndex($arr, $index, $date)
    {
        $dateTime = $date . ' ' . $arr[$index]["on_duty_time"];

        return strtotime($dateTime);
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


    public function getTotalHrsMins($first, $last)
    {
        $diff = abs(strtotime($last) - strtotime($first));

        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }
}
