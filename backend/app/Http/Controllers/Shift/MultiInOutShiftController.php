<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;

use App\Models\AttendanceLog;
use App\Models\Attendance;
use Illuminate\Http\Request;

class MultiInOutShiftController extends Controller
{

    public function processByManual(Request $request)
    {
        $diviced_by_1000 =  AttendanceLog::where("checked", false)->count() / 1000;

        $loops = ceil($diviced_by_1000);

        $i = 0;

        foreach (range(1, $loops) as $loop) {
            return  $this->processShift($request->start, $request->end);
        }

        return $i;
    }

    public function processShift()
    {
        $model = AttendanceLog::query();
        $model->where("checked", false);
        $model->take(1000);
        $model->with(["schedule"]);

        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', 2);
        });

        $model->orderBy("LogTime");

        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();

        if (count($data) == 0) {
            return "No Log found";
        }

        $i = 0;
        $items = [];
        $dual = false;

        $temp_arr = [];

        foreach ($data as $UserID => $row) {

            foreach ($row as $log) {

                $arr = [];

                $time     = $log["show_log_time"];
                $schedule = $log["schedule"];
                $shift    = $schedule["shift"];

                $date = $log['edit_date'];


                $on_duty_time = $date . " " . $shift["on_duty_time"];
                $off_duty_time = $date . " " . $shift["off_duty_time"];

                $on_duty_time_parsed = strtotime($on_duty_time);
                $off_duty_time_parsed = strtotime($off_duty_time);

                $next_day_cap = $off_duty_time_parsed; // adding 24 hours

                $attendance = $this->attendanceFound($date, $UserID);
                $found = $attendance->clone()->first();

                if ($on_duty_time_parsed > $off_duty_time_parsed) {
                    $next_day_cap  = $next_day_cap + 86400;
                    $dual = true;
                }

                if ($time >= $on_duty_time_parsed && $time < $next_day_cap) {

                    $arr["date"] = $log['edit_date'];

                    if (!$found) {
                        $arr["in"] = $log["time"];
                        $arr["status"] = "---";
                        $arr["device_id_in"] = $log["DeviceID"];
                    } else {

                        $arr["in"] = $time > strtotime($found->in) && $found->in !== '---' ? $log["time"] : $found->in;

                        $temp_arr[] = $log["time"];

                        $last = array_reverse($temp_arr)[0];

                        $arr["out"] = $last;

                        if (isset($arr["in"]) && isset($arr["out"])) {
                            $arr["status"] = $arr["in"] !== "---" && $arr["out"] !== "---" ? "P" : "A";

                            $out = strtotime($arr["out"]);

                            // if ($dual) {
                            //     $out = $out + 86400;
                            // }

                            $arr["total_hrs"] = $this->calculatedHours(strtotime($arr["in"]), $out);
                            $arr["ot"] = !$schedule["isOverTime"] ? "NA" : $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                        }
                    }
                    $arr["company_id"] = $log["company_id"];
                    $arr["employee_id"] = $UserID;
                    $arr["shift_id"] = $schedule["shift_id"];
                    $arr["shift_type_id"] = $schedule["shift_type_id"];
                } else {

                    $start = $on_duty_time_parsed - 86400;
                    $end = $next_day_cap - 86400;

                    if ($log["show_log_time"] > $start  && $log["show_log_time"] < $end) {

                        $arr["date"] = date("Y-m-d", $log["show_log_time"] - 86400);
                        $date = $arr["date"];

                        $attendance = $this->attendanceFound($date, $UserID);
                        $found = $attendance->clone()->first();

                        if ($found) {
                            $arr["in"] = $found->in;
                        }

                        $temp_arr[] = $log["time"];

                        $last = array_reverse($temp_arr)[0];

                        $arr["out"] = $last;


                        if (isset($arr["in"]) && isset($arr["out"])) {
                            $arr["status"] = $arr["in"] !== "---" && $arr["out"] !== "---" ? "P" : "A";

                            $out = strtotime($arr["out"]);

                            if ($dual) {
                                $out = $out + 86400;
                            }

                            $arr["total_hrs"] = $this->calculatedHours(strtotime($arr["in"]), $out);
                            $arr["ot"] = !$schedule["isOverTime"] ? "NA" : $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                        } else {
                            $arr["status"] =  "---";
                        }

                        $arr["device_id_out"] = $log["DeviceID"];
                        $arr["company_id"] = $log["company_id"];
                        $arr["employee_id"] = $UserID;
                        $arr["shift_id"] = $schedule["shift_id"];
                        $arr["shift_type_id"] = $schedule["shift_type_id"];
                    }
                }

                $attendance = $this->attendanceFound($date, $UserID);

                $found = $attendance->first();

                if (count($arr) > 0) {
                    $found ? $attendance->update($arr) : Attendance::create($arr);

                    AttendanceLog::where("id", $log["id"])->update(["checked" => true]);
                }

                $items[] = $arr;
            }

            $i++;
        }
        return "Log processed " . $i;
    }

    public function attendanceFound($date, $id)
    {
        return Attendance::whereDate("date", $date)->where("employee_id", $id);
    }

    public function calculatedHours($in, $out)
    {
        $diff = abs($in - $out);
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
}
