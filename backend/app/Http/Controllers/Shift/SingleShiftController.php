<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Company;

class SingleShiftController extends Controller
{

    public $update_date;

    public function findAttendanceByUserId($item)
    {
        $model = Attendance::query();
        $model->where("employee_id", $item["employee_id"]);
        $model->where("company_id", $item["company_id"]);
        $model->whereDate("date", $item["date"]);

        return !$model->first() ? false : $model->with(["schedule", "shift"])->first();
    }

    public function processData($companyId, $data, $date, $shift_type_id, $checked = true)
    {
        $date = date("Y-m-d H:i:s");
        $script_name = "SyncSingleShift";

        $meta = "[$date] Cron: $script_name.";

        $items = [];
        $arr = [];
        $ids = [];
        $arr["company_id"] = $companyId;
        $arr["date"] = $date;

        $str = "";

        foreach ($data as $UserID => $logs) {

            if (count($logs) == 0) {
                $str .= "No log(s) found for Company ID $companyId.\n";
                continue;
            };

            $arr["employee_id"] = $UserID;

            $model = $this->findAttendanceByUserId($arr);

            if (!$model) {
                $arr["shift_type_id"] = $shift_type_id;
                $arr["status"] = "P";
                $arr["device_id_in"] = $logs[0]["DeviceID"];
                $arr["shift_id"] = $logs[0]["schedule"]["shift_id"];
                $arr["roster_id"] = $logs[0]["schedule"]["roster_id"];
                $arr["in"] = $logs[0]["time"];
                $items[] = $arr;
                Attendance::create($arr);
                $ids[] = $logs[0]["id"];
            } else {
                $last = array_reverse($logs)[0];
                $arr["out"] = $last["time"];
                $arr["device_id_out"] = $last["DeviceID"];
                $arr["total_hrs"] = $this->getTotalHrsMins($model->in, $last["time"]);
                $schedule = $model->schedule ?? false;
                $isOverTime = $schedule && $schedule->isOverTime ?? false;
                if ($isOverTime) {
                    $temp["ot"] = $this->calculatedOT($arr["total_hrs"], $schedule->working_hours, $schedule->overtime_interval);
                }
                $items[] = $arr;
                $model->update($arr);
                $ids[] = $last["id"];
            }
        }

        $result = AttendanceLog::whereIn("id", $ids)->update(["checked" => $checked]);
        $str .= "$meta Total $result Log(s) Processed against company $companyId.\n";
        return $str;
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

    public function getTotalHrsMins($first, $last)
    {
        $diff = abs(strtotime($last) - strtotime($first));

        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function syncLogsScript()
    {
        $date = date("Y-m-d H:i:s");
        $script_name = "SyncSingleShift";

        $meta = "[$date] Cron: $script_name.";

        $shift_type_id = 6;

        $result = "";

        $companyIds = Company::pluck("id");

        if (count($companyIds) == 0) {
            return "$meta No Company found.";
        }

        $UserIDs = [];

        $currentDate = date('Y-m-d');

        $companies = $this->getModelDataByCompanyId($currentDate, $companyIds, $UserIDs, $shift_type_id);

        if (count($companies) == 0) {
            return "$meta No Logs found.\n";
        }

        foreach ($companies as $company_id => $data) {
            $result .= $this->processData($company_id, $data, $currentDate, $shift_type_id);
        }

        return $result;
    }

    public function processByManual(Request $request)
    {
        $shift_type_id = 6;

        $arr = [];

        $currentDate = $request->input('date', date('Y-m-d'));
        $checked = $request->input('checked');
        $companyIds = $request->input('company_ids', []);
        $UserIDs = $request->input('UserIDs', []);

        $companies = $this->getModelDataByCompanyId($currentDate, $companyIds, $UserIDs, $shift_type_id);

        foreach ($companies as $company_id => $data) {
            $arr[] = $this->processData($company_id, $data, $currentDate, $shift_type_id, $checked);
        }
        // return $arr;
        return "Logs Count " . array_sum($arr);
    }
}
