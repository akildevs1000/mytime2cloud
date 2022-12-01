<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;

use App\Models\AttendanceLog;
use App\Models\Attendance;

class SingleShiftController extends Controller
{
    public function processSingleShift()
    {
        $i = 0;
        $items = [];
        $model = AttendanceLog::query();
        $model->where("checked", false);
        // $model->where("UserID", 215);
        $model->where("UserID", 259);
        $model->whereBetween("LogTime", [date("Y-11-28"), date("Y-11-30")]);
        $model->take(1000);
        $model->with(["schedule"]);

        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', 6);
        });

        $model->orderBy("LogTime");

        return $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();

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
                $last_cap = $date . " " . "23:59:00";

                $beginning_in_parsed = strtotime($beginning_in);
                $beginning_out_parsed = strtotime($beginning_out);
                $last_cap_parsed = strtotime($last_cap);

                $attendance = $this->attendanceFound($date, $UserID);

                $found = $attendance->clone()->first();

                if ($time >= $beginning_in_parsed && $time < $beginning_out_parsed) {

                    $arr["in"] = $found && $time < strtotime($found->in) && $found->in !== '---' ? $found->in : $log["time"];

                    if ($found) {

                        if ($found->out !== '---') {
                            $arr["out"] =  $found->out;
                        }

                        if (isset($arr["in"]) && isset($arr["out"])) {
                            $arr["status"] = $arr["in"] !== "---" && $arr["out"] !== "---" ? "P" : "A";
                            $arr["total_hrs"] = $this->calculatedHours($arr["in"], $arr["out"]);
                            $arr["ot"] = !$schedule["isOverTime"] ? "NA" : $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                        }
                    } else {
                        $arr["status"] = "---";
                    }

                    $arr["late_coming"] = $this->calculatedLateComing($log["time"], $shift["on_duty_time"], $shift["late_time"]);

                    $arr["device_id_in"] = $log["DeviceID"];
                }

                if ($time >= $beginning_out_parsed && $time < $last_cap_parsed) {


                    $arr["out"] = $found && $time < strtotime($found->out) && $found->out !== '---' ? $found->out : $log["time"];

                    if ($found) {

                        if ($found->in !== '---') {
                            $arr["in"] =  $found->in;
                        }

                        if (isset($arr["in"]) && isset($arr["out"])) {
                            $arr["status"] = $arr["in"] !== "---" && $arr["out"] !== "---" ? "P" : "A";
                            $arr["total_hrs"] = $this->calculatedHours($arr["in"], $arr["out"]);
                            $arr["ot"] = !$schedule["isOverTime"] ? "NA" : $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                        }
                    } else {
                        $arr["status"] = "---";
                    }


                    $arr["early_going"] = $this->calculatedEarlyGoing($log["time"], $shift["off_duty_time"], $shift["early_time"]);

                    $arr["device_id_out"] = $log["DeviceID"];
                }

                $arr["date"] = $date;
                $arr["company_id"] = $log["company_id"];
                $arr["employee_id"] = $UserID;
                $arr["shift_id"] = $schedule["shift_id"];
                $arr["shift_type_id"] = $schedule["shift_type_id"];

                $found ? $attendance->update($arr) : Attendance::create($arr);

                AttendanceLog::where("id", $log["id"])->update(["checked" => true]);



                $items[] = $arr;
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

        $diff = abs(((strtotime($in)) - (strtotime($out))));
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

    public function calculatedEarlyGoing($time, $off_duty_time, $grace)
    {

        $interval_time = date("i", strtotime($grace));

        $late_condition = strtotime("$off_duty_time - $interval_time minute");

        $out = strtotime($time);

        if ($out > $late_condition) {
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
}
