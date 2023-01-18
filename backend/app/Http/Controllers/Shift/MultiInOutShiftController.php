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

class MultiInOutShiftController extends Controller
{

    public $update_date;

    public function processByManual(Request $request)
    {
        $companyIds = $request->company_ids ?? [];

        if (count($companyIds) == 0) {
            return "Atleast 1 company Id is required";
        }

        $currentDate = $request->date ?? date('Y-m-d');

        $nextDate =  date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        $output = "";
        $arr = [];

        foreach ($companyIds as $companyId) {
            $data = $this->getModelDataByCompanyId($currentDate, $nextDate, $companyId, $request->UserID);
            $processed_logs = $this->processData($companyId, $data);
            $arr[] = $processed_logs;
            $output .= $processed_logs;
        }

        return $output;
        return $arr;
    }

    public function processPreviousDateByManual()
    {
        $companyIds = Company::pluck("id");

        if (count($companyIds) == 0) {
            return "No Company found";
        }

        $currentDate = date('Y-m-d', strtotime('yesterday'));

        $nextDate = date('Y-m-d', strtotime($currentDate . ' + 1 day'));

        $output = "";

        $date = date("Y-m-d H:i:s");
        $script_name = "SyncMultiInOut";

        $meta = "[$date] Cron: $script_name.";

        foreach ($companyIds as $companyId) {
            $data = $this->getModelDataByCompanyId($currentDate, $nextDate, $companyId);
            $output .= $meta . ' ' . $this->processData($companyId, $data);
        }

        return $output;
    }

    public function getModelDataByCompanyId($currentDate, $nextDate, $companyId, $UserID = 0)
    {
        $model = AttendanceLog::query();

        $model->where(function ($q) use ($currentDate, $companyId, $UserID) {
            $q->where("company_id", '>', 0);
            // $q->where("checked", false);
            $q->whereDate("LogTime", $currentDate);
            $q->where("company_id", $companyId);
            $q->when($UserID > 0, function ($qu) use ($UserID) {
                $qu->where("UserID", $UserID);
            });
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->orWhere(function ($q) use ($nextDate, $companyId, $UserID) {
            $q->where("company_id", '>', 0);
            // $q->where("checked", false);
            $q->whereDate("LogTime", $nextDate);
            $q->where("company_id", $companyId);
            $q->when($UserID > 0, function ($qu) use ($UserID) {
                $qu->where("UserID", $UserID);
            });
            $q->whereHas("schedule", function ($q) {
                $q->where('shift_type_id', 2);
            });
        });

        $model->with("schedule", function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });

        $model->orderBy("LogTime");

        return $model->get(["id", "UserID", "LogTime", "DeviceID", "company_id"])->groupBy("UserID")->toArray();
    }

    public function processData($companyId, $data)
    {
        $counter = 0;
        $processed_logs = 0;
        $out_of_range = 0;
        $items = [];
        $log_ids = [];
        $logs = [];
        $total_hours = [];

        $temp = [];

        foreach ($data as $UserID => $data) {

            $logCount = count($data);
            for ($i = 0; $i < $logCount; $i++) {
                if ($logCount > 0 && $data[$i]["schedule"]) {

                    $current  = $data[$i];
                    $next  = $data[$i + 1] ?? false;

                    $date          = $current["edit_date"];
                    $time_in       = $current["show_log_time"];
                    $time_out      = $next["show_log_time"] ?? 0;
                    $schedule      = $current["schedule"];
                    $shift         = $schedule["shift"];
                    $on_duty_time  = $date . " " . $shift["on_duty_time"];
                    $off_duty_time = $date . " " . $shift["off_duty_time"];

                    $on_duty_time_parsed  = strtotime($on_duty_time);
                    $off_duty_time_parsed = strtotime($off_duty_time);

                    $next_day_cap = $off_duty_time_parsed; // adding 24 hours

                    if ($on_duty_time_parsed > $off_duty_time_parsed) {
                        $next_day_cap  = $next_day_cap + 86400;
                    }

                    if (($time_in >= $on_duty_time_parsed && $time_in < $next_day_cap)) {
                        $gap = $shift["gap_in"];
                        $ct = $current['time'];
                        $cp = strtotime("$ct $gap minutes");
                        $np = strtotime($next['time'] ?? 0);

                        if ($cp > $np || strtotime($ct) == $np) {
                            $next  = $data[$i + 1] ?? false;
                        }

                        if ((isset($current['time']) && $current['time'] != '---') and (isset($next['time']) && $next['time'] != '---')) {
                            $parsed_out = strtotime($next['time'] ?? 0);
                            $parsed_in = strtotime($current['time'] ?? 0);

                            if ($parsed_in > $parsed_out) {
                                $parsed_out += 86400;
                            }

                            $diff = $parsed_out - $parsed_in;

                            $mints =  floor($diff / 60);

                            $minutes = $mints > 0 ? $mints : 0;

                            $total_hours[$date][$UserID][] = $minutes;
                        }

                        $logs[$UserID][$date][] =  [
                            "in" => $current['time'],
                            "out" =>  $next && $time_out < $next_day_cap ? $next['time'] : "---",
                            "diff" => $next && $time_out < $next_day_cap ? $this->minutesToHoursNEW($current['time'], $next['time']) : "---",
                        ];

                        $temp[$date][$UserID]["status"] = 'P';
                        $temp[$date][$UserID]["id"] =  $current["id"];
                        $temp[$date][$UserID]["logs"] =  $logs[$UserID][$date];
                        $temp[$date][$UserID]["date"] =  $date;
                        $temp[$date][$UserID]["company_id"] =  $current["company_id"];
                        $temp[$date][$UserID]["employee_id"] =  $current["UserID"];
                        $temp[$date][$UserID]["shift_type_id"] =  $current['schedule']['shift_type_id'];
                        $temp[$date][$UserID]["shift_id"] =  $current['schedule']['shift_id'];

                        if (($next && $time_out < $next_day_cap)) {
                            $temp[$date][$UserID]["total_hrs"] = $this->minutesToHours(array_sum($total_hours[$date][$UserID]));
                        }
                        $items[] = $this->storeOrUpdate($temp[$date][$UserID]);

                        $processed_logs++;
                        $log_ids[] = $temp[$date][$UserID]["id"];

                        $i++;
                    } else {
                        $out_of_range++;
                        $log_ids[] = $current["id"];
                    }
                }
                $counter++;
            }
        }
        // return $items;

        AttendanceLog::whereIn("id",  $log_ids)->update(["checked" => true]);

        return "company id = $companyId, Total Logs = $counter, Proceed Logs = $processed_logs, Ignored Logs = $out_of_range\n";
    }
    public function storeOrUpdate($items)
    {
        $attendance = $this->attendanceFound($items['date'], $items['employee_id']);
        $found = $attendance->first();
        return $found ? $attendance->update($items) : Attendance::create($items);
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
