<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Reason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;

class AttendanceLogController extends Controller
{
    public function index(AttendanceLog $model, Request $request)
    {

        return $model->with("device")->where("company_id", $request->company_id)

            ->when($request->from_date, function ($query) use ($request) {
                return $query->whereDate('LogTime', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($query) use ($request) {
                return $query->whereDate('LogTime', '<=', $request->to_date);
            })

            ->when($request->UserID, function ($query) use ($request) {
                return $query->where('UserID', $request->UserID);
            })

            ->when($request->DeviceID, function ($query) use ($request) {
                return $query->where('DeviceID', $request->DeviceID);
            })
            ->when($request->filled('search_system_user_id'), function ($q) use ($request) {
                $q->where('UserID', 'LIKE', "$request->search_system_user_id%");
            })
            ->when($request->filled('search_time'), function ($q) use ($request) {
                $q->where('LogTime', 'LIKE', "$request->search_time%");
            })
            ->when($request->filled('search_device_name'), function ($q) use ($request) {
                $key = strtolower($request->search_device_name);
                $q->whereHas('device', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$key%"));
            })
            ->when($request->filled('search_device_id'), function ($q) use ($request) {
                $q->where('DeviceID', 'LIKE', "$request->search_device_id%");
            })
            ->orderByDesc("LogTime")->paginate($request->per_page);
    }
    public function getAttendanceLogs(AttendanceLog $model, Request $request)
    {
        return $model->where("company_id", $request->company_id)->paginate($request->per_page);
    }

    public function store()
    {
        $file = base_path() . "/logs/logs.csv";

        if (!file_exists($file)) {

            Logger::channel("custom")->info('No new data found');

            return [
                'status' => false,
                'message' => 'No new data found',
            ];
        }

        $data = [];

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                $data[] = array_combine(["UserID", "DeviceID", "LogTime", "SerialNumber"], $row);
            }
            fclose($handle);
        }
        try {
            $created = AttendanceLog::insert($data);
            $created ? unlink($file) : 0;
            $count = count($data);
            Logger::channel("custom")->info($count . ' new logs has been inserted. Old file has been deleted.');
            return $created ?? 0;
        } catch (\Throwable $th) {

            Logger::channel("custom")->error('Error occured while inserting logs.');
            Logger::channel("custom")->error('Error Details: ' . $th);

            $data = [
                'title' => 'Quick action required',
                'body' => $th,
            ];

            // Mail::to(env("ADMIN_MAIL_RECEIVERS"))->send(new NotifyIfLogsDoesNotGenerate($data));
            return;
        }
    }

    public function getLateComing($row, $time_table, $date)
    {
        $first_log = $row->first_log()->whereDate("LogTime", $date)->first();

        if (($first_log->show_log_time) <= strtotime($time_table->beginning_out)) {
            $late_time = $time_table->late_time;
            $duty_time = $time_table->on_duty_time;

            $late_condition = strtotime("$duty_time + $late_time minute");

            $in = ($first_log->show_log_time);

            if ($in <= $late_condition) {
                return "---";
            }

            $diff = abs((strtotime($duty_time) - $in));

            $h = floor($diff / 3600);
            $m = floor($diff % 3600) / 60;
            return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
        }

        return "---";
    }

    public function getEarlyGoing($row, $time_table, $date)
    {
        $off_duty_time = $time_table->off_duty_time;
        $early_time = $time_table->early_time;

        $last_log = $row->last_log()->whereDate("LogTime", $date)->first();

        $out = ($last_log->show_log_time);

        $early_condition = strtotime("$off_duty_time - $early_time minute");
        $beginning_out = strtotime($time_table->beginning_out);

        if ($out <= $beginning_out || $out > $early_condition) {
            return "---";
        }

        $diff = abs((strtotime($off_duty_time) - $out));

        $h = floor($diff / 3600);
        $h = $h < 0 ? "0" : $h;
        $m = floor($diff % 3600) / 60;
        $m = $m < 0 ? "0" : $m;

        return (($h < 10 ? "0" . $h : $h) . ":" . ($m < 10 ? "0" . $m : $m));
    }

    public function singleView(AttendanceLog $model, Request $request)
    {
        return $model->where('UserID', $request->UserID)
            ->where('company_id', $request->company_id)
            ->whereDate('LogTime', $request->LogTime)
            ->select("LogTime")
            ->orderBy('LogTime')
            ->paginate($request->per_page ?? 100);
    }

    public function GenerateManualLog(Request $request)
    {
        try {
            $log = AttendanceLog::create($request->only(['UserID', 'LogTime', 'DeviceID', 'company_id']));

            $created = Reason::create([
                'reason' => $request->reason,
                'user_id' => $request->user_id,
                'reasonable_id' => $log->id,
                'reasonable_type' => "App\Models\AttendanceLog",
            ]);

            if ($created) {
                // $Attendance = new AttendanceController;
                // $Attendance->SyncAttendance();
                return [
                    'status' => true,
                    'message' => 'Log Successfully Updated',
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function GenerateLog(Request $request)
    {
        try {
            $log = AttendanceLog::create($request->all());

            if ($log) {
                // $Attendance = new AttendanceController;
                // $Attendance->SyncAttendance();
                return [
                    'status' => true,
                    'message' => 'Log Successfully Updated',
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function SyncCompanyIdsWithDevices()
    {

        // return 282+499+245+257+335+209;
        // get device ids with company ids = 0
        $model = AttendanceLog::query();
        $model->distinct('DeviceID');
        $model->where("company_id", 0);
        $model->take(10);
        $free_device_ids = $model->pluck("DeviceID");

        // get company ids against found device ids
        $rows = Device::whereIn("device_id", $free_device_ids)->get(["company_id", "device_id as DeviceID"])->toArray();

        if (count($rows) == 0 || count($free_device_ids) == 0) {
            Logger::channel("custom")->info('No new record found while updating company ids for device');
            return 'No new record found while updating company ids for device';
        }

        foreach ($rows as $arr) {
            try {
                AttendanceLog::where("company_id", 0)->where("DeviceID", $arr["DeviceID"])->update($arr);
            } catch (\Throwable $th) {
                Logger::channel("custom")->error('Error occured while updating company ids.');
                Logger::channel("custom")->error('Error Details: ' . $th);
                $th;
            }
        }
        Logger::channel("custom")->info("Company IDS has been updated. Details: " . json_encode($rows));

        return "Company IDS has been updated. Details: " . json_encode($rows);
    }

    public function getShift($log)
    {
        return $log;
    }
    public function AttendanceLogsDaily(Request $request, $id)
    {
        $model = Employee::query();

        $model->with(["first_log.device", "last_log.device"]);

        return $model->paginate($request->per_page);
    }
    public function test($row)
    {
        # code...
    }
    public function AttendanceLogsMonthly(Request $request, $id)
    {
        $model = AttendanceLog::query();
        $model->whereMonth("LogTime", date("m"))->orderBy("LogTime");
        $array = $model->get()->groupBy(["date", "UserID"])->toArray();

        $arr = [];

        foreach ($array as $value) {
            foreach ($value as $av) {
                $arr[]["logs"] = [
                    "first" => $av[0],
                    "last" => $av[count($av) - 1],
                ];
            }
        }

        return $arr;
    }

    public function AttendanceLogsSearch(AttendanceLog $model, Request $request, $id, $key)
    {
        if ($request->type) {
            $model = $model->getFilteredByTimeStamp($request->type);
        }

        if ($key) {
            $model = $this->searchWithRelation($model, $key);
        }
        return $this->getLogs($model, $request, $id);
    }

    public function searchWithRelation($model, $key)
    {
        $model->WhereHas("employee", function ($q) use ($key) {
            $q->where('employee_id', 'LIKE', "%$key%");
            $q->orWhere('first_name', 'LIKE', "%$key%");
            $q->orWhere('last_name', 'LIKE', "%$key%");
        });

        return $model;
    }

    public function getFilteredByTimeStamp($model)
    {
        $model->whereHas("log_monthly", function ($q) {
            $q->whereMonth("LogTime", date("m"));
        });

        return $model->get()->toArray();
    }

    public function AttendanceLogPaginate(AttendanceLog $model, Request $request)
    {

        // return  $model = $model->count();
        $array = $model->limit($request->input("per_page"))->get()->toArray();
        $total = count($array);
        $current_page = $request->input("page") ?? 1;

        $starting_point = ($current_page * $request->input("per_page")) - $request->input("per_page");

        $array = array_slice($array, $starting_point, $request->input("per_page"), true);

        $array = new Paginator($array, $total, $request->input("per_page"), $current_page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return $array;
    }

    public function AttendanceLogsDetails(Request $request)
    {
        //         EID: "00033" // company_id: "1" // date: "16-Aug-22"
        return $request->all();

        $model = AttendanceLog::query();
        $model->whereDate("LogTime", date("m"));
        $model->whereDate("UserID", date("m"));
        return $model->get()->orderBy("LogTime");
    }

    public function findClosest($arr, $n, $target)
    {
        // Corner cases
        if ($target <= $arr[0]->time_in_numbers) {
            return $arr[0];
        }

        if ($target >= $arr[$n - 1]->time_in_numbers) {
            return $arr[$n - 1];
        }

        // Doing binary search
        $i = 0;
        $j = $n;
        $mid = 0;
        while ($i < $j) {
            $mid = ($i + $j) / 2;

            if ($arr[$mid]->time_in_numbers == $target) {
                return $arr[$mid];
            }

            /* If target is less than array element,
            then search in left */
            if ($target < $arr[$mid]->time_in_numbers) {

                // If target is greater than previous
                // to mid, return closest of two
                if ($mid > 0 && $target > $arr[$mid - 1]->time_in_numbers) {
                    return $this->getClosest($arr[$mid - 1], $arr[$mid], $target);
                }

                /* Repeat for left half */
                $j = $mid;
            }

            // If target is greater than mid
            else {
                if ($mid < $n - 1 && $target < $arr[$mid + 1]->time_in_numbers) {
                    return $this->getClosest($arr[$mid], $arr[$mid + 1], $target);
                }

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

    public function Search(Request $request, $company_id)
    {

        $model = AttendanceLog::query();

        $model->where("company_id", $request->company_id);

        // $model->whereDate('LogTime', '>=', $request->from_date);
        // $model->whereDate('LogTime', '<=', $request->to_date);

        $model->when($request->from_date, function ($query) use ($request) {
            return $query->whereDate('LogTime', '>=', $request->from_date);
        });

        $model->when($request->to_date, function ($query) use ($request) {
            return $query->whereDate('LogTime', '<=', $request->to_date);
        });

        $model->when($request->UserID, function ($query) use ($request) {
            return $query->where('UserID', $request->UserID);
        });

        $model->when($request->DeviceID, function ($query) use ($request) {
            return $query->where('DeviceID', $request->DeviceID);
        });

        $model->when($request->filled('search_system_user_id'), function ($q) use ($request) {
            $q->where('UserID', 'LIKE', "$request->search_system_user_id%");
        });
        $model->when($request->filled('search_time'), function ($q) use ($request) {
            $q->where('LogTime', 'LIKE', "$request->search_time%");
        });
        $model->when($request->filled('search_device_name'), function ($q) use ($request) {
            $key = strtolower($request->search_device_name);
            $q->whereHas('device', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$key%"));
        });
        $model->when($request->filled('search_device_id'), function ($q) use ($request) {
            $q->where('DeviceID', 'LIKE', "$request->search_device_id%");
        });

        return $model
            ->with("device")
            ->orderBy('LogTime', 'desc')
            ->paginate($request->per_page ?? 100);
    }
}
