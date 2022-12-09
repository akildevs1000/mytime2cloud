<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class MultiInOutShiftController extends Controller
{

    public $update_date;

    public function processByManual()
    {

        $condition_date = (string) DB::table('misc')->pluck("date")[0];

        if ($condition_date > date('Y-m-d')) {
            return "You cannot process attendance against current date or future date";
        }

        $this->update_date = date("Y-m-d", strtotime($condition_date) + 86400);


        // AttendanceLog::whereDate("LogTime", $condition_date)->update([
        //     "checked" => false
        // ]);

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

                    $start = $on_duty_time_parsed + 86400;
                    $end = $next_day_cap + 86400;

                    if ($log["show_log_time"] > $start  && $log["show_log_time"] < $end) {

                        $arr["date"] = date("Y-m-d", $log["show_log_time"]);
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

                            // if ($dual) {
                            //     $out = $out + 86400;
                            // }

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

        DB::table('misc')->update(["date" => $this->update_date]);

        return "Date = $condition_date, Log processed count = $i, Out of range Logs = $out_of_range";
    }

    public function processShift()
    {
        // return  DB::table('misc')->update(["date" => '2022-12-07']);
        $currentDate = (string) DB::table('misc')->pluck("date")[0];
        $currentDate = "2022-12-04";

        if ($currentDate < date('Y-09-27')) {
            return "You cannot process attendance against current date or future date";
        }

        $this->update_date = date("Y-m-d", strtotime($currentDate));


        AttendanceLog::whereDate("LogTime", $currentDate)->update([
            "checked" => false
        ]);

        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        // return AttendanceLog::whereDate("LogTime", $nextDate)->update([
        //     "checked" => false
        // ]);


        $model = AttendanceLog::query();
        $model->where("checked", false);
        // $model->where("company_id", 1);

        // $model->whereDate("LogTime", $currentDate);
        // $model->orWhereDate("LogTime", $nextDate);


        $model->where(function ($q) use ($currentDate) {
            // $q->where("UserID", 515);
            $q->whereDate("LogTime", $currentDate);
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->orWhere(function ($q) use ($nextDate) {
            // $q->where("UserID", 515);
            $q->whereDate("LogTime", $nextDate);
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });


        $model->with(["schedule"]);

        $model->orderBy("LogTime");

        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->toArray();

        $count =  count($data);
        // return count($data);

        if ($count == 0) {
            return "No Log found";
        }

        $counter = 0;
        $out_of_range = 0;
        $items = [];
        $log_ids = [];
        $str = "";
        $even = false;

        $total_hours = [];

        $temp = [];
        $arr = [];
        for ($i = 0; $i < $count; $i += 2) {

            if ($data[$i]["schedule"]) {
                $date = $data[$i]["edit_date"];
                $time          = $data[$i]["show_log_time"];
                $schedule      = $data[$i]["schedule"];
                $shift         = $schedule["shift"];
                $on_duty_time  = $date . " " . $shift["on_duty_time"];
                $off_duty_time = $date . " " . $shift["off_duty_time"];

                $on_duty_time_parsed = strtotime($on_duty_time);
                $off_duty_time_parsed = strtotime($off_duty_time);

                $next_day_cap = $off_duty_time_parsed; // adding 24 hours


                if ($on_duty_time_parsed > $off_duty_time_parsed) {
                    $next_day_cap  = $next_day_cap + 86400;
                }

                if (($time >= $on_duty_time_parsed && $time < $next_day_cap)) {
                    $current  = $data[$i];
                    $next  = $data[$i + 1] ?? "---";


                    // $items[$date][$current["UserID"]]["edit_date"] =  $current["edit_date"];
                    // $items[$date][$current["UserID"]]["company_id"] =  $current["company_id"];
                    // $items[$date][$current["UserID"]]["UserID"] =  $current["UserID"];
                    // $items[$date][$current["UserID"]]["shift_type_id"] =  $current['schedule']['shift_type_id'];
                    // $items[$date][$current["UserID"]]["shift_id"] =  $current['schedule']['shift_id'];



                    $items["id"] =  $current["id"];
                    $items["date"] =  $current["edit_date"];
                    $items["company_id"] =  $current["company_id"];
                    $items["employee_id"] =  $current["UserID"];
                    $items["shift_type_id"] =  $current['schedule']['shift_type_id'];
                    $items["shift_id"] =  $current['schedule']['shift_id'];

                    if (isset($current['time']) and $current['time'] != '---' and isset($next['time']) and $next['time'] != '---') {

                        $diff = strtotime($next['time']) - strtotime($current['time']);
                        $mints =  floor($diff / 60);
                        // $items["diff"] = $this->minutesToHours($mints);

                        $total_hours[] = $mints;
                    }


                    // $items[$date][$current["UserID"]]["logs"][] =  [
                    //     "in" => $current['time'],
                    //     "out" => $next['time'],
                    //     "diff" => $items["diff"]
                    // ];



                    $items["logs"][] = [
                        "in" => $current['time'],
                        "out" => $next['time'],
                        "diff" => $this->minutesToHours($mints) ?? 0
                    ];

                    $items["total_hrs"] =  $this->minutesToHours(array_sum($total_hours));

                    $res = $this->storeOrUpdate($items);

                    if ($res) {
                        $log_ids[] = $items['id'];
                    }
                    $counter++;
                } else {
                    $i--;
                    $out_of_range++;
                }
            }
        }
        // return $log_ids;

        // AttendanceLog::whereIn("id", $log_ids)->update(["checked" => true]);
        $logsCount = count($log_ids);

        return "Log processed count = $logsCount, Out of range Logs = $out_of_range";
        // return $items;
    }



    public function storeOrUpdate($items)
    {
        $attendance = $this->attendanceFound($items['date'], $items['employee_id']);
        $found = $attendance->first();
        $res =   $found ? $attendance->update($items) : Attendance::create($items);

        return $res;
    }


    public function insertData($item)
    {
        $testarr = [];

        foreach ($item as $log) {
            $data = $this->calTimes($log['logs']);
            $attendance = $this->attendanceFound($log['date'], $log['UserID']);
            $found = $attendance->first();
            $status = "";

            // return count($data['logs']);
            // return $data['logs'];

            if ($data['logs'][0]['in'] != '---' and $data['logs'][0]['out'] != '---') {
                $status = "P";
            } else {
                $status = '---';
            }

            $data = [
                'company_id' => $log['company_id'],
                'date' => $log['date'],
                'employee_id' => $log['UserID'],
                'total_hrs' => $data['total_hours'],
                'logs' => $data['logs'],
                'shift_type_id' => $log['shift_type_id'],
                'shift_id' => $log['shift_id'],
                'status' => $status,
            ];
            // $res =   $found ? $attendance->update($data) : Attendance::create($data);

            $testarr[] = $data;
        }
        return $testarr;

        if (isset($res)) {
            DB::table('misc')->update(["date" => $this->update_date]);
        }
        return isset($res) ? 'done' : 'something wrong';
    }



    public function insertData1($items)
    {
        $testarr = [];

        foreach ($items as $item) {
            foreach ($item as $log) {
                $data = $this->calTimes($log['logs']);
                $attendance = $this->attendanceFound($log['date'], $log['UserID']);
                $found = $attendance->first();
                $status = "";

                // return count($data['logs']);
                // return $data['logs'];

                if ($data['logs'][0]['in'] != '---' and $data['logs'][0]['out'] != '---') {
                    $status = "P";
                } else {
                    $status = '---';
                }

                $data = [
                    'company_id' => $log['company_id'],
                    'date' => $log['date'],
                    'employee_id' => $log['UserID'],
                    'total_hrs' => $data['total_hours'],
                    'logs' => $data['logs'],
                    'shift_type_id' => $log['shift_type_id'],
                    'shift_id' => $log['shift_id'],
                    'status' => $status,
                ];
                // $res =   $found ? $attendance->update($data) : Attendance::create($data);

                $testarr[] = $data;
            }
        }
        return $testarr;

        if (isset($res)) {
            DB::table('misc')->update(["date" => $this->update_date]);
        }
        return isset($res) ? 'done' : 'something wrong';
    }

    public function calTimes($logs)
    {
        $arr_logs = [];
        $total_hours = [];
        foreach ($logs as $log) {

            if (isset($log['in']) and $log['in'] != '---' and isset($log['out']) and $log['out'] != '---') {
                $time1 = (strtotime($log['in']) ?? 0);
                $time2 = (strtotime($log['out']) ?? 0);
                $diff = $time2 - $time1;
                $minutes = floor($diff / 60);
                $arr_logs[] = [
                    'in' => $log['in'],
                    'out' => $log['out'],
                    'diff' => $this->minutesToHours($minutes),
                ];
                $total_hours[] = $minutes;
            } else if ($log['in'] == '---' or $log['out'] == '---') {
                $arr_logs[] = [
                    'in' => $log['in'],
                    'out' => $log['out'],
                ];
            }
        }

        return
            [
                'total_hours' => $this->minutesToHours(array_sum($total_hours)),
                'logs' => $arr_logs,
            ];
    }

    public function minutesToHours($minutes)
    {
        $hours = intdiv($minutes, 60) . ':' . ($minutes % 60);
        return $hours;
    }

    public function attendanceFound($date, $id)
    {
        return Attendance::whereDate("date", $date)
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
