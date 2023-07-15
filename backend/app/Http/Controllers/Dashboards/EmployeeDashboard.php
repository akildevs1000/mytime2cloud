<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeDashboard extends Controller
{

    public function statistics(Request $request)
    {
        
        $cId = $request->company_id;
        $eId = $request->employee_id;

        return [
            [
                "title" => "Total Presents",
                "value" => $this->getStatsByStatus($cId, $eId, "P"),
                "icon" => "fas fa-calendar-check",
                "color" => "l-bg-green-dark",
                "link" => $this->getLink($request, "P"),
            ],
            [
                "title" => "Total Absence",
                "value" => $this->getStatsByStatus($cId, $eId, "A"),
                "icon" => "fas fa-calendar-times",
                "color" => "l-bg-orange-dark",
                "link" => $this->getLink($request, "A"),
            ],
            [
                "title" => "Total Missing",
                "value" => $this->getStatsByStatus($cId, $eId, "M"),
                "icon" => "fas fa-clock",
                "color" => "l-bg-cyan-dark",
                "link" => $this->getLink($request, "M"),
            ],
            [
                "title" => "Total Off",
                "value" => $this->getStatsByStatus($cId, $eId, "O"),
                "icon" => "fas fa-clock",
                "color" => "l-bg-purple-dark",
                "link" => $this->getLink($request, "O"),
                "border_color" => "526C78",
            ]
        ];
    }

    public function getLink($request, $status)
    {
        $baseUrl = env("BASE_URL");

        $params = [
            'main_shift_type' => $request->shift_type,
            'company_id' => $request->company_id,
            'status' => $status,
            'department_id' => $request->department_id,
            'employee_id' => $request->employee_id,
            'report_type' => 'Monthly',
            'from_date' => date("Y-m-d"),
            'to_date' => date("Y-m-t")
        ];

        $queryString = http_build_query($params);

        $url = $baseUrl . "/api/multi_in_out_daily?" . $queryString;

        return $url;
    }

    public function getStatsByStatus($company_id, $employee_id, $status)
    {
        $daysInMonth = Carbon::now()->month(date('m'))->daysInMonth;

        $model = Attendance::query();

        $model->where("company_id", $company_id ?? 0);

        $model->where('employee_id', $employee_id);

        $model->whereMonth('date', date('m'));

        $model->where('status', $status);

        $count = $model->count();

        return "$count/$daysInMonth";
    }
}
