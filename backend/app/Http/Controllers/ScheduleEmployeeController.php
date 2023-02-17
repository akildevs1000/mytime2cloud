<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\ScheduleEmployee;
use App\Http\Requests\ScheduleEmployee\StoreRequest;
use App\Http\Requests\ScheduleEmployee\UpdateRequest;
use App\Models\Company;

class ScheduleEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ScheduleEmployee $model)
    {
        return $model
            ->where('company_id', $request->company_id)
            ->with("shift_type", "shift", "employee")
            ->paginate($request->per_page);
    }
    // public function employees_by_departments_old(Employee $employee, Request $request, $id)
    // {
    //     return $employee->whereHas('schedule')
    //         ->withOut(["user", "department", "sub_department", "sub_department", "designation", "role", "schedule"])
    //         ->when($id != -1, function ($q) use ($id) {
    //             $q->where("department_id", $id);
    //         })
    //         ->get(["first_name", "system_user_id", "employee_id"]);
    // }

    public function employees_by_departments(Request $request, $id)
    {
        return  Employee::select("first_name", "system_user_id", "employee_id", "department_id")
            ->withOut(["user", "sub_department", "sub_department", "designation", "role", "schedule"])
            ->when($id != -1, function ($q) use ($id) {
                $q->where("department_id", $id);
            })
            ->where('company_id', $request->company_id)
            ->get();
    }

    public function store(StoreRequest $request, ScheduleEmployee $model)
    {
        $data = $request->validated();

        $arr = [];

        foreach ($data["employee_ids"] as $item) {
            $value = [
                "shift_id" => $data["shift_id"] ?? 0,
                "isOverTime" => $data["isOverTime"],
                "employee_id" => $item,
                "shift_type_id" => $data["shift_type_id"],
                "from_date" => $data["from_date"],
                "to_date" => $data["to_date"],
                "company_id" => $data["company_id"],
            ];
            $found = ScheduleEmployee::where("employee_id", $item)->where("from_date", $data["from_date"])->where("company_id", $data["company_id"])->first();

            if (!$found) {
                $arr[] = $value;
            }
        }

        try {
            $record = $model->insert($arr);

            if ($record) {
                return $this->response('Schedule Employee successfully added.', $record, true);
            } else {
                return $this->response('Schedule Employee cannot add.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(ScheduleEmployee $ScheduleEmployee)
    {
        return $ScheduleEmployee;
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $record = ScheduleEmployee::where('employee_id', $id)->update($request->validated());
            if ($record) {
                return response()->json(['status' => true, 'message' => 'Schedule Employee successfully updated']);
            } else {
                return response()->json(['status' => false, 'message' => 'Schedule Employee cannot update']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        $record = ScheduleEmployee::where("employee_id", $id)->delete();

        try {
            if ($record) {
                return $this->response('Employee Schedule deleted.', null, true);
            } else {
                return $this->response('Employee Schedule cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteSelected(Request $request)
    {
        $record = ScheduleEmployee::whereIn('id', $request->ids)->delete();
        if ($record) {
            return response()->json(['status' => true, 'message' => 'ScheduleEmployee Successfully Deleted']);
        } else {
            return response()->json(['status' => false, 'message' => 'ScheduleEmployee cannot Deleted']);
        }
    }

    public function assignSchedule()
    {
        $companyIds = Company::pluck("id");

        if (count($companyIds) == 0) {
            return "No Record found.";
        }

        $currentDate = date('Y-m-d');

        $currentDay = date("D", strtotime($currentDate));

        $arrays = [];

        $str = "";

        $date = date("Y-m-d H:i:s");
        $script_name = "AssignScheduleToEmployee";

        $meta = "[$date] Cron: $script_name.";

        foreach ($companyIds as $company_id) {

            $no_of_employees = 0;

            $model = ScheduleEmployee::query();

            $model->where("company_id", '>', 0);
            // $model->where("is_week", 0);

            $model->where("company_id", $company_id);

            $model->where(function ($q) use ($currentDate) {
                $q->where('from_date', '<=', $currentDate)
                    ->where('to_date', '>=', $currentDate);
            });

            $model->with(["roster"]);

            $rows = $model->get();

            if ($rows->isEmpty()) {
                $str .= "$meta $no_of_employees employee(s) found for Company ID $company_id.\n";
                continue;
            };

            foreach ($rows as $row) {

                $roster = $row["roster"];

                $index = array_search($currentDay, $roster["days"]);

                $model = ScheduleEmployee::query();
                // $model->where("is_week", 0);
                $model->where("company_id", $company_id);

                $model->where(function ($q) use ($currentDate) {
                    $q->where('from_date', '<=', $currentDate)
                        ->where('to_date', '>=', $currentDate);
                });

                $model->where("employee_id", $row["employee_id"]);
                $model->where("roster_id", $roster["id"]);


                $arr = [
                    "shift_id" => $roster["shift_ids"][$index],
                    "shift_type_id" => $roster["shift_type_ids"][$index],
                    "is_week" => 1
                ];

                // $model->update($arr);
                $arr["employee_id"] = $row["employee_id"];
                $arrays[] = $arr;
                $no_of_employees++;
            }

            $str .= "$meta Total $no_of_employees employee(s) for Company ID $company_id has been scheduled.\n";
        }
        return $str;
    }

    public function assignScheduleByManual(Request $request)
    {
        $companyIds = $request->company_ids ?? Company::pluck("id");

        $currentDate = $request->date ?? date('Y-m-d');

        $currentDay = date("D", strtotime($currentDate));

        $arrays = [];

        $str = "";

        $date = date("Y-m-d H:i:s");
        $script_name = "AssignScheduleToEmployee";

        $meta = "[$date] Cron: $script_name.";

        foreach ($companyIds as $company_id) {

            $no_of_employees = 0;

            $model = ScheduleEmployee::query();

            $model->where("company_id", '>', 0);

            $model->where("company_id", $company_id);

            $model->where(function ($q) use ($currentDate) {
                $q->where('from_date', '<=', $currentDate)
                    ->where('to_date', '>=', $currentDate);
            });

            $model->with(["roster"]);

            $rows = $model->get();

            if ($rows->isEmpty()) {
                $str .= "$meta $no_of_employees employee(s) found for Company ID $company_id.\n";
                $str .= "<br>";

                continue;
            };

            foreach ($rows as $row) {

                $roster = $row["roster"];

                $index = array_search($currentDay, $roster["days"]);

                $model = ScheduleEmployee::query();
                $model->where("company_id", $company_id);

                $model->where(function ($q) use ($currentDate) {
                    $q->where('from_date', '<=', $currentDate)
                        ->where('to_date', '>=', $currentDate);
                });

                $model->where("employee_id", $row["employee_id"]);
                $model->where("roster_id", $roster["id"]);


                $arr = [
                    "shift_id" => $roster["shift_ids"][$index],
                    "shift_type_id" => $roster["shift_type_ids"][$index],
                    "is_week" => 1
                ];

                $model->update($arr);
                $arr["employee_id"] = $row["employee_id"];
                $arrays[] = $arr;
                $no_of_employees++;
            }
            $str .= "Total $no_of_employees employee(s) for Company ID $company_id has been scheduled.\n";
            $str .= "<br>";
        }
        return $str;
    }
}
