<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CountController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $id = $request->company_id ?? 0;
        $model = Attendance::query();
        $model->whereCompanyId($id);

        $date =  date("Y-m-d");
        $model = $model->whereDate('date', $date)->get();


        $modelEmployees = Employee::query();
        $modelEmployees->whereCompanyId($id);

        return [
            [
                "title" => "Today Summary",
                "value" => $model->count(),
                "icon" => "fas fa-clock",
                "color" => "l-bg-purple-dark",
                "link"  => env("BASE_URL") . "/api/daily?company_id=$id&status=SA&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "multi_in_out"  => env("BASE_URL") . "/api/multi_in_out_daily?company_id=$id&status=SA&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "total_employees_count" => $modelEmployees->count(),
            ],
            [
                "title" => "Today Presents",
                "value" => $model->where('status', 'P')->count(),
                "icon" => "fas fa-calendar-check",
                "color" => "l-bg-green-dark ",
                "link"  => env("BASE_URL") . "/api/daily?page=1&per_page=1000&company_id=$id&status=P&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "multi_in_out"  => env("BASE_URL") . "/api/multi_in_out_daily?page=1&per_page=1000&company_id=$id&status=P&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "total_employees_count" => $modelEmployees->count(),
            ],
            [
                "title" => "Today Absent",
                "value" => $model->where('status', 'A')->count(),
                "icon" => "fas fa-calendar-times",
                "color" => "l-bg-orange-dark",
                "link"  => env("BASE_URL") . "/api/daily?page=1&per_page=1000&company_id=$id&status=A&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "multi_in_out"  => env("BASE_URL") . "/api/multi_in_out_daily?page=1&per_page=1000&company_id=$id&status=A&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "total_employees_count" => $modelEmployees->count(),
            ],
            [
                "title" => "Today Missing",
                "value" => $model->where('status', '---')->count(),
                "icon" => "	fas fa-clock",
                "color" => "l-bg-cyan-dark",
                "link"  => env("BASE_URL") . "/api/daily?page=1&per_page=1000&company_id=$id&status=---&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "multi_in_out"  => env("BASE_URL") . "/api/multi_in_out_daily?page=1&per_page=1000&company_id=$id&status=---&daily_date=" . $date . "&department_id=-1&report_type=Daily",
                "total_employees_count" => $modelEmployees->count(),
            ],
        ];
    }

    public function employeeCounts(Request $request)
    {

        $cId = $request->company_id;
        $eId = $request->employee_id;

        return [
            [
                "title" => "Total Presents",
                "value" => $this->getStatsByStatus($cId, $eId, "p"),
                "icon" => "fas fa-calendar-check",
                "color" => "l-bg-green-dark",
                "link" => "http://localhost:8000/api/daily?page=1&per_page=1000&company_id=8&status=P&daily_date=2023-06-13&department_id=-1&report_type=Daily",
            ],
            [
                "title" => "Total Absence",
                "value" => $this->getStatsByStatus($cId, $eId, "a"),
                "icon" => "fas fa-calendar-times",
                "color" => "l-bg-orange-dark",
                "link" => "http://localhost:8000/api/daily?page=1&per_page=1000&company_id=8&status=A&daily_date=2023-06-13&department_id=-1&report_type=Daily",
            ],
            [
                "title" => "Total Missing",
                "value" => $this->getStatsByStatus($cId, $eId, "---"),
                "icon" => "fas fa-clock",
                "color" => "l-bg-cyan-dark",
                "link" => "http://localhost:8000/api/daily?page=1&per_page=1000&company_id=8&status=---&daily_date=2023-06-13&department_id=-1&report_type=Daily",
            ],
            [
                "title" => "Total Leaves",
                "value" => Leave::where('company_id', $cId)->where('employee_id', $request->employee_id)->count(),
                "icon" => "fas fa-clock",
                "color" => "l-bg-purple-dark",
                "border_color" => "526C78",
            ]
        ];
    }

    public function getStatsByStatus($company_id, $employee_id, $status)
    {
        return rand(0, 30);
        $daysInMonth = Carbon::now()->month(date('m'))->daysInMonth;
        $query = Attendance::whereCompanyId($company_id ?? 0)->where('employee_id', $employee_id);
        return $query->whereMonth('date', date('m'))->where('status', $status)->count() . '/' . $daysInMonth;
    }
}
