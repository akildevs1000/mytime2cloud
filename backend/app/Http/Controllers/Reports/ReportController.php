<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request)
    {

        return $this->report($request)
        //   ->toSql();
            ->paginate($request->per_page);
    }

    public function multiInOut(Request $request)
    {
        $model = $this->processMultiInOut($request);
        return $this->paginate($model, $request->per_page);
    }

    public function processMultiInOut($request)
    {

        $model = $this->report($request)
            ->get();
        foreach ($model as $value) {
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
        return $model;
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        $perPage == 0 ? 50 : $perPage;

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function report($request)
    {
        $model = Attendance::query();

        $model->where('company_id', $request->company_id);
        $model->with('shift_type');
        $model->when($request->filled('employee_id'), function ($q) use ($request) {
            $q->where('employee_id', $request->employee_id);
        });

        $model->when($request->main_shift_type && $request->main_shift_type == 2, function ($q) {
            $q->where('shift_type_id', 2);
        });

        $model->when($request->main_shift_type && $request->main_shift_type != 2, function ($q) {
            $q->whereNot('shift_type_id', 2);
        });

        $model->when($request->department_id && $request->department_id != -1, function ($q) use ($request) {
            $q->whereIn('employee_id', Employee::where("department_id", $request->department_id)->where('company_id', $request->company_id)->pluck("system_user_id"));
        });

        $model->when($request->status == "P", function ($q) {
            $q->where('status', "P");
        });

        $model->when($request->status == "A", function ($q) {
            $q->where('status', "A");
        });

        $model->when($request->status == "M", function ($q) {
            $q->where('status', "M");
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

        $model->when($request->daily_date && $request->report_type == 'Daily', function ($q) use ($request) {
            $q->whereDate('date', $request->daily_date);
            //$q->orderBy("id", "desc");
        });

        $model->when($request->from_date && $request->to_date && $request->report_type != 'Daily', function ($q) use ($request) {
            $q->whereBetween("date", [$request->from_date, $request->to_date]);
            // $q->orderBy("date", "asc");
        });

        // dd($request->all());

        // $model->with([
        //     "employee:id,system_user_id,display_name,employee_id,department_id,profile_picture",
        //     "device_in:id,name,short_name,device_id,location",
        //     "device_out:id,name,short_name,device_id,location",
        //     "shift",
        //     "shift_type:id,name",
        // ]);

        $model->with('employee', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });

        $model->with('device_in', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });

        $model->with('device_out', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });

        $model->with('shift', function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });

        $model->with('schedule');

        $model->when($request->filled('date'), function ($q) use ($request) {
            $q->whereDate('date', '=', $request->date);
        });
        $model->when($request->filled('employee_id'), function ($q) use ($request) {
            $q->where('employee_id', 'LIKE', "$request->employee_id%");
        });

        $model->when($request->filled('employee_first_name') && $request->employee_first_name != '', function ($q) use ($request) {
            // $key = strtolower($request->employee_first_name);
            $q->whereHas('employee', fn(Builder $q) => $q->where('first_name', 'ILIKE', "$request->employee_first_name%"));
        });
        $model->when($request->filled('employee_department_name'), function ($q) use ($request) {
            // $key = strtolower($request->employee_department_name);
            $q->whereHas('employee.department', fn(Builder $query) => $query->where('company_id', $request->company_id)->where('name', 'ILIKE', "$request->employee_department_name%"));
        });
        if ($request->shift) {
            //$key = strtolower($request->shift_type_name);
            $model->where(function ($q) use ($request) {
                return $q->whereIn("shift_type_id", ShiftType::where('name', 'ILIKE', "$request->shift%")->pluck("id"));
            });
        }
        $model->when($request->filled('in'), function ($q) use ($request) {
            // $key = strtolower($request->in);
            $q->where('in', 'LIKE', "$request->in%");
        });
        $model->when($request->filled('out'), function ($q) use ($request) {
            // $key = strtolower($request->out);
            $q->where('out', 'LIKE', "$request->out%");
        });
        $model->when($request->filled('total_hrs'), function ($q) use ($request) {
            //$key = strtolower($request->total_hrs);
            $q->where('total_hrs', 'LIKE', "$request->total_hrs%");
        });
        $model->when($request->filled('ot'), function ($q) use ($request) {
            //$key = strtolower($request->ot);
            $q->where('ot', 'LIKE', "$request->ot%");
        });

        $model->when($request->filled('sortBy'), function ($q) use ($request) {
            $sortDesc = $request->input('sortDesc');

            $q->orderBy($request->sortBy, $sortDesc == 'true' ? 'desc' : 'asc');

        });
        $model->when(!$request->filled('sortBy'), function ($q) use ($request) {
            $q->orderBy('id', 'desc');

        });
        return $model;
    }
}
