<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        //    return $request->all();
        $model = (new Attendance)->processAttendanceModel($request);



        if ($request->shift_type_id == 1) {
            return $this->general($model, $request->per_page ?? 1000);
        }

        return $this->multiInOut($model->get(), $request->per_page ?? 1000);
    }

    public function fetchDataOLD(Request $request)
    {
        //    return $request->all();
        $model = (new Attendance)->processAttendanceModel($request);

        if ($request->shift_type_id == 1) {
            return $this->general($model, $request->per_page ?? 1000);
        }

        return $this->multiInOut($model->get(), $request->per_page ?? 1000);
    }

    public function fetchDataNEW(Request $request)
    {
        $perPage = $request->per_page ?? 100;

        $model = (new Attendance)->processAttendanceModel($request);

        $data = $model->paginate($perPage);

        // only for multi in/out
        if ($request->shift_type_id == 2) {
            foreach ($data as $value) {

                $logs = $value->logs ?? [];

                $logs = array_pad($logs, 7, [
                    "in" => "---",
                    "out" => "---",
                    "device_in" => "---",
                    "device_out" => "---",
                ]);

                // Populate log details for each entry
                foreach ($logs as $index => $log) {
                    $value["in" . ($index + 1)] = $log["in"];
                    $value["out" . ($index + 1)] = $log["out"];
                    $value["device_in" . ($index + 1)] = $log["device_in"];
                    $value["device_out" . ($index + 1)] = $log["device_out"];
                }
            }
        }

        return $data;
    }
    public function performanceReport(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString()); // Default: first day of the month
        $toDate = $request->input('to_date', Carbon::now()->toDateString()); // Default: today

        $companyId = $request->input('company_id', 0);
        $branch_id = $request->input('branch_id', 0);

        $department_ids = $request->department_ids;

        if (gettype($department_ids) !== "array") {
            $department_ids = explode(",", $department_ids);
        }

        $employeeIds = [];

        if (!empty($request->employee_id)) {
            $employeeIds = is_array($request->employee_id) ? $request->employee_id : explode(",", $request->employee_id);
        }

        $model = Attendance::where('company_id', $companyId)
            ->when($branch_id, function ($q) use ($branch_id) {
                $q->whereHas('employee', fn(Builder $query) => $query->where('branch_id', $branch_id));
            })
            ->when(count($department_ids), function ($q) use ($department_ids) {
                $q->whereHas('employee', fn(Builder $query) => $query->whereIn('department_id',   $department_ids));
            })
            ->when(count($employeeIds), function ($q) use ($employeeIds) {
                $q->whereIn('employee_id', $employeeIds);
            })

            ->whereBetween('date', [$fromDate, $toDate])
            ->select(
                'employee_id',
                $this->getStatusCountWithSuffix('P'), // Present count
                $this->getStatusCountWithSuffix('A'), // Absent count
                $this->getStatusCountWithSuffix('L'), // Leave count
                $this->getStatusCountWithSuffix('M'), // Missing count
                $this->getStatusCountWithSuffix('LC'), // Late Coming count
                $this->getStatusCountWithSuffix('EG'), // Early Going count

                $this->getStatusCountValue('P'), // Present count
                $this->getStatusCountValue('A'), // Absent count
                $this->getStatusCountValue('L'), // Leave count
                $this->getStatusCountValue('M'), // Missing count
                $this->getStatusCountValue('LC'), // Late Coming count
                $this->getStatusCountValue('EG') // Early Going count
            )

            ->with(["employee" => function ($q) {
                $q->withOut("schedule", "user");
                $q->with("reporting_manager:id,reporting_manager_id,first_name");
                $q->select(
                    "first_name",
                    "last_name",
                    "profile_picture",
                    "phone_number",
                    "whatsapp_number",
                    "employee_id",
                    "joining_date",
                    "designation_id",
                    "department_id",
                    "user_id",
                    "sub_department_id",
                    "overtime",
                    "title",
                    "status",
                    "company_id",
                    "branch_id",
                    "system_user_id",
                    "display_name",
                    "full_name",
                    "home_country",
                    "reporting_manager_id",
                    "local_email",
                    "home_email",
                );
            }])
            ->groupBy('employee_id');

        // return $model->count();


        return $model->paginate($request->per_page ?? 10);
    }

    function getStatusCountWithSuffix($status)
    {
        return DB::raw("CONCAT(LPAD(COUNT(CASE WHEN status = '{$status}' THEN 1 END)::text, 2, '0'), 
                     CASE WHEN COUNT(CASE WHEN status = '{$status}' THEN 1 END) = 1 THEN ' day' 
                          WHEN COUNT(CASE WHEN status = '{$status}' THEN 1 END) > 1 THEN ' days' 
                          ELSE ' days' END) AS {$status}_count");
    }

    function getStatusCountValue($status)
    {
        return DB::raw("COUNT(CASE WHEN status = '$status' THEN 1 END) AS {$status}_count_value");
    }

    public function general($model, $per_page = 100)
    {
        return $model->paginate($per_page);
    }

    public function multiInOut($model, $per_page = 100)
    {
        foreach ($model as $value) {
            $logs = $value->logs ?? [];
            $count = count($logs);
            $requiredLogs = max($count, 7); // Ensure at least 8 logs

            for ($a = 0; $a < $requiredLogs; $a++) {
                $log = $logs[$a] ?? [];
                $value["in" . ($a + 1)] = $log["in"] ?? "---";
                $value["out" . ($a + 1)] = $log["out"] ?? "---";
                $value["device_" . "in" . ($a + 1)]   = $log["device_in"] ?? "---";
                $value["device_" . "out" . ($a + 1)]  = $log["device_out"] ?? "---";
            }
        }

        return $this->paginate($model, $per_page);
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        $perPage == 0 ? 50 : $perPage;

        $resultArray = [];

        foreach ($items->forPage($page, $perPage) as $object) {
            $resultArray[] =   $object;
        }

        return new LengthAwarePaginator($resultArray, $items->count(), $perPage, $page, $options);
        //return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function general_download_csv(Request $request)
    {
        $data = (new Attendance)->processAttendanceModel($request)->get();

        $fileName = 'report.csv';

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            $i = 0;

            fputcsv($file, ["#", "Date", "E.ID", "Name", "Dept", "Shift Type", "Shift", "Status", "In", "Out", "Total Hrs", "OT", "Late coming", "Early Going", "D.In", "D.Out"]);
            foreach ($data as $col) {
                fputcsv($file, [
                    ++$i,
                    $col['date'],
                    $col['employee_id'] ?? "---",
                    $col['employee']["display_name"] ?? "---",
                    $col['employee']["department"]["name"] ?? "---",
                    $col["shift_type"]["name"] ?? "---",
                    $col["shift"]["name"] ?? "---",
                    $col["status"] ?? "---",
                    $col["in"] ?? "---",
                    $col["out"] ?? "---",
                    $col["total_hrs"] ?? "---",
                    $col["ot"] ?? "---",
                    $col["late_coming"] ?? "---",
                    $col["early_going"] ?? "---",
                    $col["device_in"]["short_name"] ?? "---",
                    $col["device_out"]["short_name"] ?? "---"
                ], ",");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function multi_in_out_daily_download_csv(Request $request)
    {
        $data = (new Attendance)->processAttendanceModel($request)->get();

        foreach ($data as $value) {
            $count = count($value->logs ?? []);
            if ($count > 0) {
                if ($count < 8) {
                    $diff = 7 - $count;
                    $count = $count + $diff;
                }
                $i = 1;
                for ($a = 0; $a < $count; $a++) {

                    $holder = $a;
                    $holder_key = ++$holder;

                    $value["in" . $holder_key] = $value->logs[$a]["in"] ?? "---";
                    $value["out" . $holder_key] = $value->logs[$a]["out"] ?? "---";
                }
            }
        }

        $fileName = 'report.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            $i = 0;
            fputcsv($file, [
                "#",
                "Date",
                "E.ID",
                "Name",
                "In1",
                "Out1",
                "In2",
                "Out2",
                "In3",
                "Out3",
                "In4",
                "Out4",
                "In5",
                "Out5",
                "In6",
                "Out6",
                "In7",
                "Out7",
                "Total Hrs",
                "OT",
                "Status",

            ]);
            foreach ($data as $col) {
                fputcsv($file, [
                    ++$i,
                    $col['date'],
                    $col['employee_id'] ?? "---",
                    $col['employee']["display_name"] ?? "---",
                    $col["in1"] ?? "---",
                    $col["out1"] ?? "---",
                    $col["in2"] ?? "---",
                    $col["out2"] ?? "---",
                    $col["in3"] ?? "---",
                    $col["out3"] ?? "---",
                    $col["in4"] ?? "---",
                    $col["out4"] ?? "---",
                    $col["in5"] ?? "---",
                    $col["out5"] ?? "---",
                    $col["in6"] ?? "---",
                    $col["out6"] ?? "---",
                    $col["in7"] ?? "---",
                    $col["out7"] ?? "---",
                    $col["total_hrs"] ?? "---",
                    $col["ot"] ?? "---",
                    $col["status"] ?? "---",

                ], ",");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function lastSixMonthsPerformanceReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $employeeId = $request->input('employee_id', 0);

        $startMonth = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();  // Removes time
        $endMonth = Carbon::now()->endOfMonth()->toDateString();  // Removes time
        // $endMonth = Carbon::now()->toDateString();  // Removes time


        $startMonthOnly = Carbon::now()->subMonths(5)->startOfMonth();
        $endMonthOnly = Carbon::now()->endOfMonth();

        $months = [];
        for ($month = $startMonthOnly; $month <= $endMonthOnly; $startMonthOnly->addMonth()) {
            $months[] = [
                'year' => $month->year,
                'month' => $month->month,
            ];
        }

        // Now, use these dates in your query
        $query = DB::table('attendances')
            ->select(
                DB::raw('EXTRACT(YEAR FROM date) AS year'),
                DB::raw('EXTRACT(MONTH FROM date) AS month'),
                DB::raw('COUNT(CASE WHEN status = \'P\' THEN 1 ELSE NULL END) AS present_count'),
                DB::raw('COUNT(CASE WHEN status = \'A\' THEN 1 ELSE NULL END) AS absent_count'),
            )
            ->where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$startMonth, $endMonth])  // Date-only comparison
            ->groupBy(DB::raw('EXTRACT(YEAR FROM date)'), DB::raw('EXTRACT(MONTH FROM date)'))
            ->orderBy(DB::raw('EXTRACT(YEAR FROM date)'), 'desc')
            ->orderBy(DB::raw('EXTRACT(MONTH FROM date)'), 'desc')
            ->get();

        $queryResults = [];


        foreach ($months as $month) {
            $found = false;
            foreach ($query as $result) {

                $monthFormatted = str_pad($month['month'], 2, '0', STR_PAD_LEFT);

                $month_year = date("M Y", strtotime("{$month['year']}-{$monthFormatted}-01"));

                if ($result->year == $month['year'] && $result->month == $month['month']) {
                    $found = true;
                    $queryResults[] = (object) [
                        'year' => $month['year'],
                        'month' => $month['month'],
                        'present_count' => $result->present_count,
                        'absent_count' => $result->absent_count,
                        'month_year' => date("M y", strtotime($month_year))
                    ];
                    break;
                }
            }

            if (!$found) {
                // If the month was not found in the results, add it with counts as 0
                $queryResults[] = (object) [
                    'year' => $month['year'],
                    'month' => $month['month'],
                    'present_count' => 0,
                    'absent_count' => 31,
                    'month_year' => date("M y", strtotime($month_year))
                ];
            }
        }

        return response()->json($queryResults);
    }

    public function lastSixMonthsSalaryReport(Request $request)
    {
        $companyId = $request->input('company_id', 0);
        $employeeId = $request->input('employee_id', 0);

        $startMonth = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();  // Removes time
        $endMonth = Carbon::now()->endOfMonth()->toDateString();  // Removes time
        // $endMonth = Carbon::now()->toDateString();  // Removes time


        $startMonthOnly = Carbon::now()->subMonths(5)->startOfMonth();
        $endMonthOnly = Carbon::now()->endOfMonth();

        $months = [];
        for ($month = $startMonthOnly; $month <= $endMonthOnly; $startMonthOnly->addMonth()) {
            $months[] = [
                'year' => $month->year,
                'month' => $month->month,
            ];
        }

        // Now, use these dates in your query
        $query = DB::table('attendances')
            ->select(
                DB::raw('EXTRACT(YEAR FROM date) AS year'),
                DB::raw('EXTRACT(MONTH FROM date) AS month'),
                DB::raw('COUNT(CASE WHEN status = \'P\' THEN 1 ELSE NULL END) AS present_count'),
                DB::raw('COUNT(CASE WHEN status = \'A\' THEN 1 ELSE NULL END) AS absent_count'),
            )
            ->where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$startMonth, $endMonth])  // Date-only comparison
            ->groupBy(DB::raw('EXTRACT(YEAR FROM date)'), DB::raw('EXTRACT(MONTH FROM date)'))
            ->orderBy(DB::raw('EXTRACT(YEAR FROM date)'), 'desc')
            ->orderBy(DB::raw('EXTRACT(MONTH FROM date)'), 'desc')
            ->get();

        $queryResults = [];


        foreach ($months as $month) {
            $found = false;
            foreach ($query as $result) {

                $monthFormatted = str_pad($month['month'], 2, '0', STR_PAD_LEFT);

                $month_year = date("M Y", strtotime("{$month['year']}-{$monthFormatted}-01"));

                if ($result->year == $month['year'] && $result->month == $month['month']) {
                    $found = true;
                    $queryResults[] = (object) [
                        'year' => $month['year'],
                        'month' => $month['month'],
                        'present_count' => $result->present_count,
                        'absent_count' => $result->absent_count,
                        'month_year' => date("M y", strtotime($month_year))
                    ];
                    break;
                }
            }

            if (!$found) {
                // If the month was not found in the results, add it with counts as 0
                $queryResults[] = (object) [
                    'year' => $month['year'],
                    'month' => $month['month'],
                    'present_count' => 0,
                    'absent_count' => 31,
                    'month_year' => date("M y", strtotime($month_year))
                ];
            }
        }

        return response()->json($queryResults);
    }
}
