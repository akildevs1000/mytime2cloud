<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
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
        $attendanceModel = Attendance::query();
        $attendanceModel->whereCompanyId($id);
        $model = $attendanceModel->whereDate('date', date('Y-m-d'))->get();

        return [
            // [
            //     "title" => "TOTAL MODULES",
            //     "value" => count(AssignModule::whereCompanyId($id)->pluck("module_ids")[0]),
            //     "icon" => "mdi-apps",
            //     "color" => "l-bg-green-dark",
            // ],
            // [
            //     "title" => "TOTAL Department",
            //     "value" => Department::whereCompanyId($id)->count(),
            //     "icon" => "mdi-door",
            //     "color" => "l-bg-cyan-dark",
            // ],
            // [
            //     "title" => "TOTAL Employee",
            //     "value" => Employee::whereCompanyId($id)->count(),
            //     "icon" => "mdi-account",
            //     "color" => "l-bg-purple-dark",
            // ],
            // [
            //     "title" => "TOTAL Device",
            //     "value" => Device::whereCompanyId($id)->count(),
            //     "icon" => "mdi-laptop",
            //     "color" => "l-bg-orange-dark",
            // ],
            [
                "title" => "Today Presents",
                "value" => 25,
                // "value" => $model->where('status', 'P')->count(),
                "icon" => "fas fa-calendar-check",
                "color" => "l-bg-orange-dark",
            ],
            [
                "title" => "Today Absent",
                "value" => 14,
                // "value" => $model->where('status', 'A')->count(),
                "icon" => "fas fa-calendar-times",
                "color" => "l-bg-purple-dark",
            ],
            [
                "title" => "Late Coming",
                "value" => 20,
                // "value" => $model->where('status', 'A')->count(),
                "icon" => "	fas fa-clock",
                "color" => "l-bg-cyan-dark",
            ],
            [
                "title" => "Online Devices",
                "value" => 10,
                // "value" => $model->where('status', 'A')->count(),
                "icon" => "fas fa-clock",
                "color" => "l-bg-green-dark",
            ],
        ];
    }

    public function employeeDashboard(Request $request)
    {

        $id = $request->company_id ?? 0;
        $employee_id = $request->employee_id ?? 0;
        $daysInMonth = Carbon::now()->month(date('m'))->daysInMonth;
        $query = Attendance::whereCompanyId($id)->where('employee_id', $employee_id);

        return [
            [
                "title" => "Total Presents",
                "value" => $query->where('status', 'p')->count(),
                "icon" => "mdi-check",
                "color" => "l-bg-green-dark",
                "border_color" => "4B7BEC",
            ],
            [
                "title" => "Total Presents Current Month",
                "value" => $query->whereMonth('date', date('m'))->where('status', 'p')->count() . '/' . $daysInMonth,
                "icon" => "mdi-history",
                "color" => "orange darken-3",
                "border_color" => "EF6C00",
            ],
            [
                "title" => "Absence",
                "value" => $query->where('status', 'a')->count(),
                "icon" => "mdi-cancel",
                "color" => "error",
                "border_color" => "DD2C00",
            ],
            [
                "title" => "Total Absence Current Month",
                "value" => $query->whereMonth('date', date('m'))->where('status', 'a')->count() . '/' . $daysInMonth,
                "icon" => "mdi-history",
                "color" => "orange darken-3",
                "border_color" => "EF6C00",
            ],
            [
                "title" => "Number of Apply Leaves",
                "value" => Leave::where('company_id', $request->company_id)->where('employee_id', $request->employee_id)->count(),
                "icon" => "mdi-home",
                "color" => "blue-grey darken-1",
                "border_color" => "526C78",
            ],
            [
                "title" => "Late Coming",
                "value" => Device::whereCompanyId($id)->count(),
                "icon" => "mdi-clock",
                "color" => "error",
                "border_color" => "DD2C00",
            ],
        ];
    }

}