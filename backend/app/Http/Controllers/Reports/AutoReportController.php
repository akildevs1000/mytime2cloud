<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use App\Models\TimeTable;
use Illuminate\Http\Request;

class AutoReportController extends Controller
{
    public function SyncDefaultAttendance()
    {
        $model = ScheduleEmployee::query();

        $model->doesntHave("attendances");

        $model->whereHas("logs",function ($q) {
            $q->whereDate("LogTime","!=",date("Y-m-d"));
        });

        $model->withOut(["shift", "shift_type", "logs", "first_log", "last_log"]);
        $free_employees = $model->get(["employee_id", "company_id", "shift_type_id"])->toArray();

        foreach ($free_employees as &$val) {
            $val["date"] = date("Y-m-d");
            $val["status"] = "A";
        }

        return Attendance::insert($free_employees);
    }
    public function report(Request $request)
    {
        $model = Attendance::query();

        $model->when($request->employee_id, function ($q) use ($request) {
            $q->where('employee_id', $request->employee_id);
        });

        $model->when($request->department_id && $request->department_id != -1, function ($q) use ($request) {
            $ids = Employee::where("department_id", $request->department_id)->pluck("system_user_id");
            $q->whereIn('employee_id', $ids);
        });

        $model->when($request->from_date && $request->to_date, function ($q) use ($request) {
            $q->whereBetween("date", [$request->from_date, $request->to_date]);
        });

        $model->when($request->status == "P", function ($q) {
            $q->where('status', "P");
        });

        $model->when($request->status == "A", function ($q) {
            $q->where('status', "A");
        });

        $model->when($request->status == "M", function ($q) {
            $q->where('status', "---");
        });

        $model->when($request->late_early == "L", function ($q) {
            $q->where('late_coming', "!=", "---");
        });

        $model->when($request->late_early == "E", function ($q) {
            $q->where('early_going', "!=", "---");
        });

        $model->when($request->ot == 1, function ($q) {
            $q->where('ot', "!=", "---");
        });

        $model->with(["employee", "shift", "shift_type", "time_table", "device_out", "device_in"]);

        return $model->orderByDesc("date")->paginate($request->per_page);
    }

    public function store()
    {
        $model = ScheduleEmployee::query();
        $rows = $model->get();

        $arr = [];

        foreach ($rows as $row) {
            $item = [
                "company_id" =>  $row->company_id,
                "employee_id" =>  $row->employee_id,
                "shift_type_id" => $row->shift_type_id,
                "date" => date("Y-m-d")
            ];

            if (count($row->logs) > 0) {
                $item = $item + $this->process_columns($row);
            }

            $attendance = Attendance::whereDate("date", date("Y-m-d"))->where("employee_id", $row->employee_id);

            $attendance->first() ? $attendance->update($item) : Attendance::create($item);

            $arr[] = $item;
        }
        return $arr;
        return Attendance::count();
    }

    public function getStatus($row, $time_table)
    {
        if (count($row->logs) <= 1) {
            return  "---";
        }


        $absent_in = strtotime("$time_table->on_duty_time + $time_table->absent_min_in minute");
        $absent_out = strtotime("$time_table->off_duty_time - $time_table->absent_min_out minute");

        return strtotime($row->first_log->time) > $absent_in || strtotime($row->last_log->time) < $absent_out ? "A" : "P";
    }

    public function getCheckInDate($row)
    {
        return $row->first_log->date ?? "---";
    }

    public function getCheckOutDate($row)
    {
        if (count($row->logs) <= 1) {
            return "---";
        }

        return $row->first_log->date != $row->last_log->date ? $row->last_log->date : "---";
    }

    public function getCheckIn($row)
    {
        if (count($row->logs) <= 1) {
            return "---";
        }

        return $row->first_log->time;
    }

    public function getCheckOut($row)
    {
        if (count($row->logs) <= 1) {
            return  "---";
        }

        return $row->last_log->time;
    }

    public function getTotalHrsMins($row)
    {
        if (count($row->logs) <= 1) {
            return "---";
        }

        $diff = abs(($row->last_log->show_log_time - $row->first_log->show_log_time));

        $h = floor($diff / 3600);
        $m = floor($diff % 3600) / 60;
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function getOT($row, $shift)
    {
        if (!$row->isOverTime) {
            return "NA";
        }

        if (count($row->logs) <= 1) {
            return "---";
        }


        $diff = abs(($row->last_log->show_log_time - $row->first_log->show_log_time) - ($shift->overtime * 60));

        $diff =  $diff - $shift->working_hours * 3600;

        $h = floor($diff / 3600);
        $h = $h < 0 ? "0" : $h;
        $m = floor($diff % 3600) / 60;
        $m = $m < 0 ? "0" : $m;

        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function getLateComing($row, $time_table)
    {
        if (count($row->logs) <= 1) {
            return "---";
        }
        $late_time = $time_table->late_time;
        $duty_time = $time_table->on_duty_time;

        $late_condition = strtotime("$duty_time + $late_time minute");

        $in = strtotime($row->first_log->time);

        if ($in <= $late_condition) {
            return "---";
        }

        $diff = abs((strtotime($duty_time) - $in));

        $h = floor($diff / 3600);
        $m = floor($diff % 3600) / 60;
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function getEarlyGoing($row, $time_table)
    {
        $off_duty_time = $time_table->off_duty_time;
        $early_time = $time_table->early_time;

        if (count($row->logs) <= 1) {
            return "---";
        }


        $out = strtotime($row->last_log->time);

        $early_condition = strtotime("$off_duty_time - $early_time minute");

        if ($out <= $early_condition && count($row->logs) > 1) {
            $diff = abs((strtotime($off_duty_time) - $out));

            $h = floor($diff / 3600);
            $h = $h < 0 ? "0" : $h;
            $m = floor($diff % 3600) / 60;
            $m = $m < 0 ? "0" : $m;

            return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
        }
        return "---";
    }

    public function getDeviceIn($row)
    {
        if (count($row->logs) <= 1) {
            return "---";
        }

        return Device::where("device_id", $row->first_log->DeviceID)->pluck("id")[0] ?? "---";
    }

    public function getDeviceOut($row)
    {
        if (count($row->logs) <= 1) {
            return  "---";
        }

        return Device::where("device_id", $row->last_log->DeviceID)->pluck("id")[0];
    }

    public function findClosest($arr, $n, $target)
    {
        // Corner cases
        if ($target <= $arr[0]->time_in_numbers)
            return $arr[0];
        if ($target >= $arr[$n - 1]->time_in_numbers)
            return $arr[$n - 1];

        // Doing binary search
        $i = 0;
        $j = $n;
        $mid = 0;
        while ($i < $j) {
            $mid = ($i + $j) / 2;

            if ($arr[$mid]->time_in_numbers == $target)
                return $arr[$mid];

            /* If target is less than array element,
            then search in left */
            if ($target < $arr[$mid]->time_in_numbers) {

                // If target is greater than previous
                // to mid, return closest of two
                if ($mid > 0 && $target > $arr[$mid - 1]->time_in_numbers)
                    return $this->getClosest($arr[$mid - 1], $arr[$mid], $target);

                /* Repeat for left half */
                $j = $mid;
            }

            // If target is greater than mid
            else {
                if ($mid < $n - 1 && $target < $arr[$mid + 1]->time_in_numbers)
                    return $this->getClosest($arr[$mid], $arr[$mid + 1], $target);
                // update i
                $i = $mid + 1;
            }
        }

        // Only single element left after search
        return $arr[$mid];
    }
    public function getClosest($val1, $val2, $target)
    {
        return ($target - $val1->time_in_numbers > $val2->time_in_numbers - $target) ? $val2 : $val1;
    }

    public function getCalulatedHours($diff)
    {
        $h = floor($diff / 3600);
        $m = floor($diff % 3600) / 60;
        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function process_columns($row)
    {

        $item = [];

        if ($row->shift_type_id == 1) {
            if (count($row->logs) > 0) {
                $item["status"] = "P";
                // $item["ot"] = $this->getOT($row, $row->shift);
            }
        } else if ($row->shift_type_id == 2) {
            $time_tables = TimeTable::orderBy("on_duty_time")->with("shift")->get();

            $time_tables_count = count($time_tables);

            $row->related_time_table = $this->findClosest($time_tables, $time_tables_count, $row->first_log->show_log_time);

            $time_table = $row->related_time_table;

            $item["status"] = $this->getStatus($row, $time_table);
            $item["late_coming"] = $this->getLateComing($row, $time_table);
            $item["early_going"] = $this->getEarlyGoing($row, $time_table);
            $item["ot"] = $this->getOT($row, $time_table->shift);
            $item["time_table_id"] = $time_table->id ?? 0;
            $item["shift_id"] = $time_table->shift->id ?? 0;
        } else if ($row->shift_type_id == 3) {

            $time_table = $row->shift->time_table;
            $item["status"] = $this->getStatus($row, $time_table);
            $item["late_coming"] = $this->getLateComing($row, $time_table);
            $item["early_going"] = $this->getEarlyGoing($row, $time_table);
            $item["ot"] = $this->getOT($row, $row->shift);
            $item["time_table_id"] = $time_table->id ?? 0;
            $item["shift_id"] = $row->shift->id ?? 0;
        }

        $item["device_id_in"] = $this->getDeviceIn($row);
        $item["device_id_out"] = $this->getDeviceOut($row);
        $item["in"] = $this->getCheckIn($row);
        $item["out"] = $this->getCheckOut($row);
        $item["total_hrs"] = $this->getTotalHrsMins($row);

        // $item["date_in"] = $this->getCheckInDate($row);
        // $item["date_out"] = $this->getCheckOutDate($row);

        return $item;
    }
}
