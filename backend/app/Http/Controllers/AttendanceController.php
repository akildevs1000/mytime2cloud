<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ScheduleEmployee;
use App\Models\ShiftType;
use Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function ProcessAttendance()
    {
        return $this->processNightShift();
    }


    public function processNightShift()
    {
        $i = 0;
        $isPair = false;
        $items = [];


        $model = AttendanceLog::query();
        $model->where("checked", false);
        $model->take(1000);
        $model->with(["schedule"]);

        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', 4);
        });

        $model->orderByDesc("LogTime");

        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();

        if (count($data) == 0) {
            return "No Log found";
        }

        foreach ($data as $UserID => $row) {

            foreach ($row as $log) {
                $arr = [];

                $time     = $log["show_log_time"];
                $schedule = $log["schedule"];
                $shift    = $schedule["shift"];

                $date = $log['edit_date'];

                $beginning_in = $date . " " . $shift["beginning_in"];
                $beginning_out = $date . " " . $shift["beginning_out"];

                $beginning_in_parsed = strtotime($beginning_in);
                $beginning_out_parsed = strtotime($beginning_out);

                $next_day_cap = $beginning_out_parsed + 86400; // adding 24 hours

                // 1 jan 20:00 to 2 jan 05:00
                if ($time >= $beginning_in_parsed && $time < $next_day_cap) {

                    $arr["device_id_in"] = $log["DeviceID"];
                    $arr["date"] = $date;


                    $arr["late_coming"] = $this->calculatedLateComing($log["time"], $shift["on_duty_time"], $shift["late_time"]);

                    // $arr["late_coming"] = json_encode([$log["time"], $shift["on_duty_time"], $shift["late_time"], $func]);

                    $attendance = $this->attendanceFound($date, $UserID)->first();

                    $arr["in"] = $attendance && $time < strtotime($attendance->in) && $attendance->in !== '---' ? $attendance->in : $log["time"];

                    if ($attendance && $attendance->out !== '---') {
                        $arr["out"] =  $attendance->out;
                    }
                }

                if ($time >= $beginning_out_parsed && $time < $beginning_in_parsed) {

                    $arr["device_id_out"] = $log["DeviceID"];
                    $arr["date"] = date("Y-m-d", strtotime($date) - 86400);

                    $arr["early_going"] = $this->calculatedEarlyGoiing($log["time"], $shift["off_duty_time"], $shift["early_time"]);

                    $attendance = $this->attendanceFound($arr["date"], $UserID)->first();

                    $arr["out"] = $attendance && $time < strtotime($attendance->out) && $attendance->out !== '---' ? $attendance->out : $log["time"];

                    if ($attendance && $attendance->in !== '---') {
                        $arr["in"] =  $attendance->in;
                    }
                }

                if (array_key_exists("in", $arr) && array_key_exists("out", $arr)) {

                    $isPair = true;
                }

                if ($isPair) {
                    $arr["status"] = "P";

                    $arr["total_hrs"] = $this->calculatedHours($arr["in"], $arr["out"]);

                    if (!$schedule["isOverTime"]) {
                        $arr["ot"] = "NA";
                    } else {
                        $arr["ot"] = $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                    }
                }

                $arr["company_id"] = $log["company_id"];
                $arr["employee_id"] = $UserID;
                $arr["shift_id"] = $schedule["shift_id"];
                $arr["shift_type_id"] = $schedule["shift_type_id"];

                $attendance = $this->attendanceFound($arr["date"], $UserID);

                $found = $attendance->first();

                $found ? $attendance->update($arr) : Attendance::create($arr);

                AttendanceLog::where("id", $log["id"])->update(["checked" => true]);

                $items[] = $arr;

                $isPair = false;
            }

            $i++;
        }
        return $items;
    }

    public function attendanceFound($date, $id)
    {
        return Attendance::whereDate("date", $date)->where("employee_id", $id);
    }

    public function calculatedHours($in, $out)
    {

        $diff = abs(((strtotime($in)) - (strtotime($out) + 86400)));
        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
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
            return "00:00";
        }

        $diff = abs(((strtotime($working_hours)) - (strtotime($total_hours))));
        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function calculatedEarlyGoiing($time, $off_duty_time, $grace)
    {

        $interval_time = date("i", strtotime($grace));

        $late_condition = strtotime("$off_duty_time + $interval_time minute");

        $out = strtotime($time);

        if ($out < $late_condition) {
            return "00:00";
        }

        $diff = abs((strtotime($off_duty_time) - $out));

        $h = floor($diff / 3600);
        $m = floor($diff % 3600) / 60;
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function calculatedLateComing($time, $on_duty_time, $grace)
    {

        $interval_time = date("i", strtotime($grace));

        $late_condition = strtotime("$on_duty_time + $interval_time minute");

        $in = strtotime($time);

        if ($in < $late_condition) {
            return "00:00";
        }

        $diff = abs((strtotime($on_duty_time) - $in));

        $h = floor($diff / 3600);
        $m = floor($diff % 3600) / 60;
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }


    public function SyncAttendance()
    {

        $this->processNightShift();

        $items = [];
        $model = AttendanceLog::query();
        $model->where("checked", false);
        $model->take(1000);
        if ($model->count() == 0) {
            return false;
        }
        return $logs = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);

        $i = 0;

        foreach ($logs as $log) {

            $date = date("Y-m-d", strtotime($log->LogTime));

            $AttendanceLog = new AttendanceLog;

            $orderByAsc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);
            $orderByDesc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);

            $first_log = $orderByAsc->orderBy("LogTime")->first() ?? false;
            $last_log =  $orderByDesc->orderByDesc('LogTime')->first() ?? false;

            $logs = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date)->count();

            $item = [];
            $item["company_id"] = $log->company_id;
            $item["employee_id"] = $log->UserID;
            $item["date"] = $date;

            if ($first_log) {
                $item["in"] = $first_log->time;
                $item["status"] = "---";
                $item["device_id_in"] = $first_log->DeviceID ?? "---";
            }
            if ($logs > 1 && $last_log) {
                $item["out"] = $last_log->time;
                $item["device_id_out"] = $last_log->DeviceID ?? "---";
                $item["status"] = "P";
                $diff = abs(($last_log->show_log_time - $first_log->show_log_time));
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $item["total_hrs"] = (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
            }

            $attendance = Attendance::whereDate("date", $date)->where("employee_id", $log->UserID);

            $attendance->first() ? $attendance->update($item) : Attendance::create($item);

            AttendanceLog::where("id", $log->id)->update(["checked" => true]);

            $i++;

            // $items[$date][$log->UserID] = $item;
        }

        return $i;
    }
    public function SyncAbsent($no_of_day = 1)
    {
        $day = date('Y-m-d', strtotime('-' . $no_of_day . ' days'));

        $employees = Employee::whereDoesntHave('attendances', function ($q) use ($day) {
            $q->whereDate('date', $day);
        })
            ->get(["employee_id", "company_id"]);

        if (count($employees) == 0) {
            return false;
        }

        $record = [];

        foreach ($employees as $employee) {
            $record[] = [
                "employee_id"   => $employee->employee_id,
                "date"          => $day,
                "status"        => "A",
                "company_id"    => $employee->company_id
            ];
        }

        Attendance::insert($record);

        return count($record);
    }

    public function SyncAbsentForMultipleDays()
    {
        $first = AttendanceLog::orderBy("id")->first();
        $today = date('Y-m-d');
        $startDate = $first->edit_date;
        $difference = strtotime($startDate) - strtotime($today);
        $days = abs($difference / (60 * 60) / 24);
        $arr = [];

        for ($i = $days; $i > 0; $i--) {
            $arr[] = $this->SyncAbsent($i);
        }

        return json_encode($arr);
    }

    public function ResetAttendance(Request $request)
    {
        $items = [];
        $model = AttendanceLog::query();
        $model->whereBetween("LogTime", [$request->from_date ?? date("Y-m-d"), $request->to_date ?? date("Y-m-d")]);
        $model->where("DeviceID", $request->DeviceID);

        if ($model->count() == 0) {
            return false;
        }
        $logs = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"]);


        $i = 0;

        foreach ($logs as $log) {

            $date = date("Y-m-d", strtotime($log->LogTime));

            $AttendanceLog = new AttendanceLog;

            $orderByAsc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);
            $orderByDesc = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date);

            $first_log = $orderByAsc->orderBy("LogTime")->first() ?? false;
            $last_log =  $orderByDesc->orderByDesc('LogTime')->first() ?? false;

            $logs = $AttendanceLog->where("UserID", $log->UserID)->whereDate("LogTime", $date)->count();

            $item = [];
            $item["company_id"] = $log->company_id;
            $item["employee_id"] = $log->UserID;
            $item["date"] = $date;

            if ($first_log) {
                $item["in"] = $first_log->time;
                $item["status"] = "---";
                $item["device_id_in"] = Device::where("device_id", $first_log->DeviceID)->pluck("id")[0] ?? "---";
            }
            if ($logs > 1 && $last_log) {
                $item["out"] = $last_log->time;
                $item["device_id_out"] = Device::where("device_id", $last_log->DeviceID)->pluck("id")[0] ?? "---";
                $item["status"] = "P";
                $diff = abs(($last_log->show_log_time - $first_log->show_log_time));
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $item["total_hrs"] = (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
            }


            $attendance = Attendance::whereDate("date", $date)->where("employee_id", $log->UserID);

            $attendance->first() ? $attendance->update($item) : Attendance::create($item);

            AttendanceLog::where("id", $log->id)->update(["checked" => true]);

            $i++;

            $items[$date][$log->UserID] = $item;
        }

        Storage::disk('local')->put($request->DeviceID . '-' . date("d-M-y") . '-reset_attendance.txt', json_encode($items));

        return $i;
    }
}
