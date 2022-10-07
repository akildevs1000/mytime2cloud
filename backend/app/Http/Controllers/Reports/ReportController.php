<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class ReportController extends Controller
{

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

        $model->with([
            "employee:id,first_name",
            "shift:id,name,beginning_date,cycle_number,cycle_unit,overtime,days,working_hours",
            "shift_type:id,name,slug",
            "time_table",
            "device_in:id,name,short_name,device_id,location",
            "device_out:id,name,short_name,device_id,location",
        ]);

        return $model->orderByDesc("date")->paginate($request->per_page);
    }
}
