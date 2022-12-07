<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;

use App\Models\AttendanceLog;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MultiInOutShiftController extends Controller
{

    public function processByManual()
    {
        $condition_date = DB::table('misc')->pluck("date")[0];

        if ($condition_date > date('Y-m-d')) {
            return "You cannot process attendance against current date or future date";
        }

        $update_date = date("Y-m-d", strtotime($condition_date) + 86400);


        AttendanceLog::whereDate("LogTime", $condition_date)->update([
            "checked" => false
        ]);

        $model = AttendanceLog::query();
        $model->where("checked", false);
        $model->whereDate("LogTime", $condition_date ?? date('Y-m-d'));

        $model->with(["schedule"]);

        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', 2);
        });

        $model->orderBy("LogTime");

        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();

        // return count($data);

        if (count($data) == 0) {
            DB::table('misc')->update(["date" => $update_date]);
            return "No Log found";
        }

        $i = 0;
        $items = [];
        $dual = false;
        $str = "";

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

                        if (count($row) > 1) {
                            $arr["out"] = end($row)["time"];
                        }


                        if (isset($arr["in"]) && isset($arr["out"])) {
                            $arr["status"] = $arr["in"] !== "---" && $arr["out"] !== "---" ? "P" : "A";

                            $out = strtotime($arr["out"]);

                            // if ($dual) {
                            //     $out = $out + 86400;
                            // }

                            $arr["total_hrs"] = $this->calculatedHours(strtotime($arr["in"]), $out);
                            $arr["ot"] = !$schedule["isOverTime"] ? "NA" : $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                            $arr["device_id_out"] = $log["DeviceID"];
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

                        if (count($row) > 1) {
                            $arr["out"] = end($row)["time"];
                        }


                        if (isset($arr["in"]) && isset($arr["out"])) {
                            $arr["status"] = $arr["in"] !== "---" && $arr["out"] !== "---" ? "P" : "A";

                            $out = strtotime($arr["out"]);

                            if ($dual) {
                                $out = $out + 86400;
                            }

                            $arr["total_hrs"] = $this->calculatedHours(strtotime($arr["in"]), $out);
                            $arr["ot"] = !$schedule["isOverTime"] ? "NA" : $this->calculatedOT($arr["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                            $arr["device_id_out"] = $log["DeviceID"];
                        } else {
                            $arr["status"] =  "---";
                        }

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

                    $updated = AttendanceLog::where("id", $log["id"])->update(["checked" => true]);

                    if ($updated) {
                        $i++;
                    }
                } else {
                    // $UserID = $log['UserID'];
                    // $LogTime = $log['LogTime'];
                    // $str .= "$UserID, $LogTime\n";
                    // $str .= "<br>";

                    $items[] = ["date" => $date, "UserID" => $log["UserID"], "LogTime" => $log["LogTime"]];
                }

                // $items[] = $arr;
                // $items[] = ["date" => $date, "UserID" => $log["UserID"], "LogTime" => $log["LogTime"]];
            }
        }

        // return $items;

        $out_of_range = count($items);

        DB::table('misc')->update(["date" => $update_date]);

        return "Date = $condition_date, Log processed count = $i, Out of range Logs = $out_of_range";
    }

    public function processShift()
    {
        $currentDate = date('Y-m-04');
        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        $model = AttendanceLog::query();
        $model->where("checked", false);

        // $model->whereDate("LogTime", $currentDate);
        // $model->orWhereDate("LogTime", $nextDate);


        $model->where(function ($q) use ($currentDate) {
            // $q->where("UserID", 645);
            $q->whereDate("LogTime", $currentDate);
        });

        $model->orWhere(function ($q) use ($nextDate) {
            // $q->where("UserID", 645);
            $q->whereDate("LogTime", $nextDate);
        });



        $model->with(["schedule"]);

        $model->whereHas("schedule", function ($q) {
            $q->where('shift_type_id', 2);
        });

        $model->orderBy("LogTime");

        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy(["UserID"])->toArray();

        // return count($data);

        if (count($data) == 0) {
            return "No Log found";
        }

        $i = 0;
        $items = [];
        $final_arr = [];
        $str = "";

        foreach ($data as $UserID => $row) {
            $arr = [];

            if (count($row) % 2 == 0) {
                $current  = current($row);

                $arr["company_id"] = $current["company_id"];
                $arr["employee_id"] = $UserID;


                if ($current["schedule"]) {

                    $schedule = $current["schedule"];
                    $shift    = $schedule["shift"];
                    $arr["shift_id"] = $schedule["shift_id"];
                    $arr["shift_type_id"] = $schedule["shift_type_id"];
                }
                $chunks = array_chunk($row, 2);

                foreach ($chunks as $chunk) {


                    if (count($chunk) % 2 == 0) {
                        $arr["date"] = $chunk[0]["edit_date"];

                        $time1 = ($chunk[0]["show_log_time"] ?? 0);
                        $time2 = ($chunk[1]["show_log_time"] ?? 0);
                        $total_hour = date("i", $time2 - $time1);

                        $arr["total_hours"][] = $total_hour;


                        $arr["logs"][] = ["in" => $chunk[0]["time"] ?? '---', "out" => $chunk[1]["time"] ?? '---', "total_hour_min" => $this->get_total_hours($total_hour)];
                    }
                }

                $arr["total_hours"] = $this->get_total_hours(array_sum($arr["total_hours"]));

                $attendance = $this->attendanceFound($arr["date"], $UserID);
                $found = $attendance->first();


                $found ? $attendance->update($arr) : Attendance::create($arr);

                // AttendanceLog::where("id", $log["id"])->update(["checked" => true]);

                $items[] = $arr;
            }
        }

        return $items;

        $out_of_range = count($items);

        return "Log processed count = $i, Out of range Logs = $out_of_range";
    }

    public function getCols($log, $on_duty_time, $next_condition)
    {
    }

    public function attendanceFound($date, $id)
    {
        $nextDate =  date('Y-m-d', strtotime($date . ' + 1 day'));
        return Attendance::whereDate("date", $date)
            ->orWhereDate("date", $nextDate)
            ->where("employee_id", $id);
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

    public function get_total_hours($diff)
    {
        $h = floor($diff / 60);
        $m = floor(($diff % 60));
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }
}