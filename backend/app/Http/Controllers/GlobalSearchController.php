<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Timezone;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class GlobalSearchController extends Controller
{


    public function globalSearch(Request $request)
    {

        return $request->search_value;
        $model = Employee::query();

        $model->with([
            "user" => function ($q) {
                return $q->with(["branchLogin", "role"]);
            },
        ])
            ->with([
                "reportTo",   "schedule_all", "branch", "department", "department.branch", "sub_department", "designation", "payroll", "timezone", "passport",
                "emirate", "qualification", "bank", "leave_group",  "Visa", "reporting_manager",
            ])
            ->with(["schedule" => function ($q) {
                $q->with("roster");
            }])
            ->where('company_id', $request->company_id)


            ->when($request->filled('search_value'), function ($q) use ($request) {
                $q->Where('system_user_id', 'ILIKE', "%$request->search_value%");
                $q->orWhere('employee_id', 'ILIKE', "%$request->search_value%");
                $q->orWhere('first_name', 'ILIKE', "%$request->search_value%");
                $q->orWhere('last_name', 'ILIKE', "%$request->search_value%");
                $q->orWhere('full_name', 'ILIKE', "%$request->search_value%");
                $q->orWhere('phone_number', 'ILIKE', "%$request->search_value%");
                $q->orWhere('local_email', 'ILIKE', "%$request->search_value%");

                $q->orWhereHas('branch', fn (Builder $query) => $query->where('branch_name', 'ILIKE', "$request->search_value%"));
                $q->orWhereHas('department', fn (Builder $query) => $query->where('name', 'ILIKE', "$request->search_value%"));
            });


        // ->when($request->filled('sortBy'), function ($q) use ($request) {
        //     $sortDesc = $request->input('sortDesc');
        //     if (strpos($request->sortBy, '.')) {
        //         if ($request->sortBy == 'department.name.id' || $request->sortBy == 'department_name_id') {
        //             $q->orderBy(Department::select("name")->whereColumn("departments.id", "employees.department_id"), $sortDesc == 'true' ? 'desc' : 'asc');
        //         } else
        //         if ($request->sortBy == 'user.email') {
        //             $q->orderBy(User::select("email")->whereColumn("users.id", "employees.user_id"), $sortDesc == 'true' ? 'desc' : 'asc');
        //         } else
        //         if ($request->sortBy == 'schedule.shift_name') {
        //             // $q->orderBy(Schedule::select("shift")->whereColumn("schedule_employees.employee_id", "employees.id"), $sortDesc == 'true' ? 'desc' : 'asc');

        //         } else
        //         if ($request->sortBy == 'timezone.name') {
        //             $q->orderBy(Timezone::select("timezone_name")->whereColumn("timezones.id", "employees.timezone_id"), $sortDesc == 'true' ? 'desc' : 'asc');
        //         }
        //     } else if ($request->sortBy == 'department_name_id') {
        //         $q->orderBy(Department::select("name")->whereColumn("departments.id", "employees.department_id"), $sortDesc == 'true' ? 'desc' : 'asc');
        //     } else {
        //         $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
        //         }
        //     }
        // });

        if (!$request->sortBy) {
            $model->orderBy('first_name', 'asc');
        }

        return $model;
    }
}
