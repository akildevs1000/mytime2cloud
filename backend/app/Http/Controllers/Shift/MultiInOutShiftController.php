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

    public function getDataToProcess($data)
    {
        $arr = [];
        foreach ($data as $UserID => $logs) {
            $arr[] = $logs;
        }

        return $arr;
    }

    // processByManual

    public function processByManual(Request $request)
    {
        $currentDate = $request->date ?? date('Y-m-d');
        // return  DB::table('misc')->update(["date" => '2022-12-07']);
        // $currentDate = (string) DB::table('misc')->pluck("date")[0];
        // $currentDate = date('Y-m-d');

        // return  Attendance::whereDate("date", $currentDate)->delete();

        $this->update_date = date("Y-m-d", strtotime($currentDate));


        // return   AttendanceLog::whereDate("LogTime", $currentDate)->update([
        //     "checked" => false
        // ]);

        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        // return AttendanceLog::whereDate("LogTime", $currentDate)->update([
        //     "checked" => false
        // ]);


        $model = AttendanceLog::query();
        $model->where("checked", false);
        // $model->where("company_id", 1);

        // $model->whereDate("LogTime", $currentDate);
        // $model->orWhereDate("LogTime", $nextDate);

        $model->where(function ($q) use ($currentDate) {
            // $q->whereIn("UserID", [
            //     209,
            // ]);
            $q->whereDate("LogTime", $currentDate);
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->orWhere(function ($q) use ($nextDate) {
            // $q->whereIn("UserID", [
            //     209,
            // ]);
            $q->whereDate("LogTime", $nextDate);
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->with(["schedule"]);

        // return   $model->count();
        $model->orderBy("LogTime");
        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();

        // return $this->getDataToProcess($data);
        // return count($data);

        if (count($data) == 0) {
            return "No Log found";
        }

        $counter = 0;
        $out_of_range = 0;
        $items = [];
        $log_ids = [];
        $logs = [];
        $str = "";
        $temp = [];
        $total_hours = [];


        foreach ($data as $UserID => $data) {
            $count =  count($data);
            for ($i = 0; $i < $count; $i++) {

                if ($data[$i]["schedule"]) {
                    $current  = $data[$i];
                    $next  = $data[$i + 1] ?? false;

                    $date = $current["edit_date"];
                    $time_in          = $current["show_log_time"];
                    $time_out          = $next["show_log_time"] ?? 0;
                    $schedule      = $current["schedule"];
                    $shift         = $schedule["shift"];
                    $on_duty_time  = $date . " " . $shift["on_duty_time"];
                    $off_duty_time = $date . " " . $shift["off_duty_time"];

                    $on_duty_time_parsed = strtotime($on_duty_time);
                    $off_duty_time_parsed = strtotime($off_duty_time);

                    $next_day_cap = $off_duty_time_parsed; // adding 24 hours

                    if ($on_duty_time_parsed > $off_duty_time_parsed) {
                        $next_day_cap  = $next_day_cap + 86400;
                    }

                    if (($time_in >= $on_duty_time_parsed && $time_in < $next_day_cap)) {

                        $items["id"] =  $current["id"];
                        $items["date"] =  $current["edit_date"];
                        $items["company_id"] =  $current["company_id"];
                        $items["employee_id"] =  $current["UserID"];
                        $items["shift_type_id"] =  $current['schedule']['shift_type_id'];
                        $items["shift_id"] =  $current['schedule']['shift_id'];

                        $gap = $shift["gap_in"];
                        $ct = $current['time'];
                        $cp = strtotime("$ct $gap minutes");
                        $np = strtotime($next['time'] ?? 0);


                        if ($cp > $np) {
                            $i++;
                            $next  = $data[$i + 1] ?? false;

                            // $testing[$UserID][$date][] = [$current["time"],$next["time"] ?? "----"];
                            // return [$current["time"],$next["time"]];
                        }

                        if (strtotime($ct) == $np) {
                            $i++;
                            $next  = $data[$i + 1] ?? false;
                        }

                        $mints = 0;
                        if (isset($current['time']) and $current['time'] != '---' and isset($next['time']) and $next['time'] != '---') {

                            $diff = strtotime($next['time']) - strtotime($current['time']);
                            $mints =  floor($diff / 60);
                            // $items["diff"] = $this->minutesToHours($mints);

                            if ($mints > 0) {
                                $final_mints = $mints;
                            } else {
                                $final_mints = 0;
                            }

                            $items["status"] =  'P';
                            $total_hours[$UserID][$date][] = $final_mints;
                        }



                        $logs[$UserID][$date][] =  [
                            "in" => $current['time'],
                            "out" =>  $next && $time_out < $next_day_cap ? $next['time'] : "---",
                            "diff" => $this->minutesToHours($final_mints) ?? 0

                            // "UserID" => $next['UserID'] ?? '---',
                            // "range" => [$on_duty_time, $next_day_cap_display]
                            // "diff" => $this->minutesToHours($mints) ?? 0
                        ];

                        $items["logs"] = $logs[$UserID][$date];
                        $res = $total_hours[$UserID][$date] ?? [];
                        $items["total_hrs"] = $this->minutesToHours(array_sum($res));



                        // $items[$date][$UserID]["id"] =  $current["id"];
                        // $items[$date][$UserID]["edit_date"] =  $current["edit_date"];
                        // $items[$date][$UserID]["company_id"] =  $current["company_id"];
                        // $items[$date][$UserID]["UserID"] =  $current["UserID"];
                        // $items[$date][$UserID]["shift_type_id"] =  $current['schedule']['shift_type_id'];
                        // $items[$date][$UserID]["shift_id"] =  $current['schedule']['shift_id'];
                        // $items["total_hrs"] =  $this->minutesToHours($total_hours[$UserID][$date]);

                        $res = $this->storeOrUpdate($items);

                        if ($res ?? true) {
                            $log_ids[] = $items['id'];
                        }
                        $counter++;
                        $i++;
                    } else {
                        $out_of_range++;
                    }
                }
            }
        }

        // AttendanceLog::whereIn("id", $log_ids)->update(["checked" => true]);
        $logsCount = count($log_ids);

        return "Log processed count = $logsCount, Out of range Logs = $out_of_range";
    }

    public function processShift()
    {
        // return  DB::table('misc')->update(["date" => '2022-12-07']);
        // $currentDate = (string) DB::table('misc')->pluck("date")[0];
        // $currentDate = date('Y-m-11');
        $currentDate = date('Y-m-d');


        if ($currentDate < date('Y-09-27')) {
            return "You cannot process attendance against current date or future date";
        }

        $this->update_date = date("Y-m-d", strtotime($currentDate));


        // return  AttendanceLog::whereDate("LogTime", $currentDate)->update([
        //     "checked" => false
        // ]);

        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        // return AttendanceLog::whereDate("LogTime", $currentDate)->update([
        //     "checked" => false
        // ]);


        $model = AttendanceLog::query();
        $model->where("checked", false);
        // $model->where("company_id", 1);

        // $model->whereDate("LogTime", $currentDate);
        // $model->orWhereDate("LogTime", $nextDate);

        $model->where(function ($q) use ($currentDate) {
            // $q->whereIn("UserID", [
            //     19,
            // ]);
            $q->whereDate("LogTime", $currentDate);
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->orWhere(function ($q) use ($nextDate) {
            // $q->whereIn("UserID", [
            //     19,
            // ]);
            $q->whereDate("LogTime", $nextDate);
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->with(["schedule"]);

        // return   $model->count();
        $model->orderBy("LogTime");
        $data = $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();

        // return $this->getDataToProcess($data);
        // return count($data);

        if (count($data) == 0) {
            return "No Log found";
        }

        $counter = 0;
        $out_of_range = 0;
        $items = [];
        $log_ids = [];
        $logs = [];
        $str = "";
        $temp = [];
        $total_hours = [];

        foreach ($data as $UserID => $data) {
            $count =  count($data);
            for ($i = 0; $i < $count; $i++) {

                if ($data[$i]["schedule"]) {
                    $current  = $data[$i];
                    $next  = $data[$i + 1] ?? false;

                    $date = $current["edit_date"];
                    $time_in          = $current["show_log_time"];
                    $time_out          = $next["show_log_time"] ?? 0;
                    $schedule      = $current["schedule"];
                    $shift         = $schedule["shift"];
                    $on_duty_time  = $date . " " . $shift["on_duty_time"];
                    $off_duty_time = $date . " " . $shift["off_duty_time"];

                    $on_duty_time_parsed = strtotime($on_duty_time);
                    $off_duty_time_parsed = strtotime($off_duty_time);

                    $next_day_cap = $off_duty_time_parsed; // adding 24 hours

                    if ($on_duty_time_parsed > $off_duty_time_parsed) {
                        $next_day_cap  = $next_day_cap + 86400;
                    }

                    if (($time_in >= $on_duty_time_parsed && $time_in < $next_day_cap)) {

                        $items["id"] =  $current["id"];
                        $items["date"] =  $current["edit_date"];
                        $items["company_id"] =  $current["company_id"];
                        $items["employee_id"] =  $current["UserID"];
                        $items["shift_type_id"] =  $current['schedule']['shift_type_id'];
                        $items["shift_id"] =  $current['schedule']['shift_id'];

                        $gap = $shift["gap_in"];
                        $ct = $current['time'];
                        $cp = strtotime("$ct $gap minutes");
                        $np = strtotime($next['time'] ?? 0);

                        if ($cp > $np) {
                            $i++;
                            $next  = $data[$i + 1] ?? false;

                            // $testing[$UserID][$date][] = [$current["time"],$next["time"] ?? "----"];
                            // return [$current["time"],$next["time"]];
                        }

                        if (strtotime($ct) == $np) {
                            $i++;
                            $next  = $data[$i + 1] ?? false;
                        }


                        $mints = 0;
                        if (isset($current['time']) and $current['time'] != '---' and isset($next['time']) and $next['time'] != '---') {

                            $diff = strtotime($next['time']) - strtotime($current['time']);
                            $mints =  floor($diff / 60);
                            // $items["diff"] = $this->minutesToHours($mints);

                            if ($mints > 0) {
                                $final_mints = $mints;
                            } else {
                                $final_mints = 0;
                            }
                            $items["status"] =  'P';
                            $total_hours[$UserID][$date][] = $final_mints;
                        }

                        $logs[$UserID][$date][] =  [
                            "in" => $current['time'],
                            "out" =>  $next && $time_out < $next_day_cap ? $next['time'] : "---",
                            "diff" => $this->minutesToHours($final_mints) ?? 0

                            // "UserID" => $next['UserID'] ?? '---',
                            // "range" => [$on_duty_time, $next_day_cap_display]
                            // "diff" => $this->minutesToHours($mints) ?? 0
                        ];

                        $items["logs"] = $logs[$UserID][$date];
                        $res = $total_hours[$UserID][$date] ?? [];
                        $items["total_hrs"] = $this->minutesToHours(array_sum($res));


                        // $items[$date][$UserID]["id"] =  $current["id"];
                        // $items[$date][$UserID]["edit_date"] =  $current["edit_date"];
                        // $items[$date][$UserID]["company_id"] =  $current["company_id"];
                        // $items[$date][$UserID]["UserID"] =  $current["UserID"];
                        // $items[$date][$UserID]["shift_type_id"] =  $current['schedule']['shift_type_id'];
                        // $items[$date][$UserID]["shift_id"] =  $current['schedule']['shift_id'];

                        // $items["total_hrs"] =  $this->minutesToHours($total_hours[$UserID][$date]);


                        $res = $this->storeOrUpdate($items);

                        if ($res ?? true) {
                            $log_ids[] = $items['id'];
                        }
                        $counter++;
                        $i++;
                    } else {
                        $out_of_range++;
                    }
                }
            }
        }


        // return [
        //     $items,
        // $temp
        // ];
        // AttendanceLog::whereIn("id", $log_ids)->update(["checked" => true]);
        $logsCount = count($log_ids);

        return "Log processed count = $logsCount, Out of range Logs = $out_of_range";
    }

    public function storeOrUpdate($items)
    {
        $attendance = $this->attendanceFound($items['date'], $items['employee_id']);
        $found = $attendance->first();
        return   $found ? $attendance->update($items) : Attendance::create($items);
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
        $newHours = intdiv($minutes, 60);
        $newMints = $minutes % 60;
        $final_mints =  $newMints < 10 ? '0' . $newMints :  $newMints;
        $final_hours =  $newHours < 10 ? '0' . $newHours :  $newHours;
        $hours = $final_hours . ':' . ($final_mints);
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