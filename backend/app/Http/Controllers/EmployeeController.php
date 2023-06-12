<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\ContactRequest;
use App\Http\Requests\Employee\EmployeeContactRequest;
use App\Http\Requests\Employee\EmployeeImportRequest;
use App\Http\Requests\Employee\EmployeeOtherRequest;
use App\Http\Requests\Employee\EmployeeRequest;
use App\Http\Requests\Employee\EmployeeUpdateContact;
use App\Http\Requests\Employee\EmployeeUpdateRequest;
use App\Http\Requests\Employee\StoreRequest;
use App\Http\Requests\Employee\UpdateRequest;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public $company_id = null;

    public function validateEmployee(EmployeeRequest $request)
    {
        return ['status' => true];
    }
    public function validateContact(EmployeeContactRequest $request)
    {
        return ['status' => true];
    }
    public function validateOther(EmployeeOtherRequest $request)
    {
        return ['status' => true];
    }

    public function employeeStore(StoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $fileName);
            $data['profile_picture'] = $fileName;
        }

        DB::beginTransaction();

        if ($request->email) {

            $user = User::create([
                "name" => "null",
                "email" => $request->email,
                "password" => Hash::make("secret"),
                "company_id" => $data["company_id"],
            ]);

            if (!$user) {
                return $this->response('User cannot add.', null, false);
            }

            $data["user_id"] = $user->id;

            unset($data['email']);
        }

        try {

            $employee = Employee::create($data);
            if (!$employee) {
                return $this->response('Employee cannot add.', null, false);
            }
            $employee->profile_picture = asset('media/employee/profile_picture' . $employee->profile_picture);

            DB::commit();
            return $this->response('Employee successfully created.', null, true);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function employeeUpdate(UpdateRequest $request, $id)
    {
        $data = $request->validated();

        if ($request->profile_picture && $request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $fileName);
            $data['profile_picture'] = $fileName;
        }

        try {
            $employee = Employee::where("id", $id)->update($data);
            if (!$employee) {
                return $this->response('Employee cannot update.', null, false);
            }
            // $employee->profile_picture = asset('media/employee/profile_picture' . $request->profile_picture);
            return $this->response('Employee Details successfully updated.', $employee, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function employeeSingle($id)
    {
        return Employee::with("user")->find($id);
    }

    public function store(Request $request)
    {
        $user = [
            'name' => $request->display_name ?? 'null',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_role_id' => $request->role_id,
        ];
        $employee = [
            // employee info
            'display_name' => $request->display_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'file_no' => $request->file_no,
            'title' => $request->title,

            //other info
            'employee_id' => $request->employee_id,
            'joining_date' => $request->joining_date,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'system_user_id' => $request->system_user_id,
            'grade' => $request->grade,
            'type' => $request->type,
            'role_id' => $request->role_id,
            'status' => 1,

            //local contact
            'local_address' => $request->address,
            'local_email' => $request->email,
            'phone_number' => $request->phone_number,
            'whatsapp_number' => $request->whatsapp_number,
            'relation' => $request->relation,
            'phone_relative_number' => $request->phone_relative_number,
            'local_city' => $request->city,
            'local_country' => $request->country,
        ];
        if (isset($request->company_id)) {
            $employee['company_id'] = $request->company_id;
            $user['company_id'] = $request->company_id;
        }
        if (isset($request->sub_department_id)) {
            $employee['sub_department_id'] = $request->sub_department_id;
        }
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $fileName);
            $employee['profile_picture'] = $fileName;
        }
        DB::beginTransaction();
        try {
            $user = User::create($user);
            if (!$user) {
                return $this->response('User cannot add.', null, false);
            }
            $employee["user_id"] = $user->id;
            $employee = Employee::create($employee);
            if (!$employee) {
                return $this->response('Employee cannot add.', null, false);
            }
            $employee->profile_picture = asset('media/employee/profile_picture' . $employee->profile_picture);
            DB::commit();
            return $this->response('Employee successfully created.', null, true);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function index(Employee $employee, Request $request)
    {
        $data = $employee
        // ->with(["user" => function ($q) {
        //     return $q->with("employee_role:id,name");
        // }])
            ->with(["reportTo", "user", "role", "schedule", "department", "sub_department", "designation", "payroll", "timezone"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->whereHas('department', fn(Builder $query) => $query->where('department_id', $request->department_id));
            })
            ->paginate($request->per_page ?? 100);
        $data = $this->getPayslipstatus($data, $request);

        return $data;
    }
    public function searchby_emp_table(Request $request, $text)
    {

        $text = strtolower($text);
        $data = Employee::query()
            ->latest()
            ->with(["reportTo", "schedule", "user", "department", "sub_department", "designation", "role", "payroll", "timezone"])
            ->where('company_id', $request->company_id)
            ->when($request->filled('search_column_name'), function ($q) use ($request, $text) {
                $q->where(DB::raw('lower(' . $request->search_column_name . ')'), 'LIKE', "$text%");
            })
            ->when($request->filled('search_department_name'), function ($q) use ($request, $text) {
                $q->whereHas('department', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$text%"));
            })
            ->when($request->filled('search_designation_name'), function ($q) use ($request, $text) {
                $q->whereHas('designation', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$text%"));
            })
            ->when($request->filled('searchBybasic_salary'), function ($q) use ($request, $text) {
                $q->whereHas('payroll', fn(Builder $query) => $query->where('basic_salary', '>=', $text));
            })
            ->when($request->filled('searchBynet_salary'), function ($q) use ($request, $text) {
                $q->whereHas('payroll', fn(Builder $query) => $query->where('net_salary', '>=', $text));
            })

            ->paginate($request->perPage ?? 20);

        $data = $this->getPayslipstatus($data, $request);

        return $data;
    }

    public function getPayslipstatus($data, $request)
    {
        if (isset($request["company_id"]) && $request["year"] && $request["month"]) {
            foreach ($data as $key => $value) {

                $pdfFile_name = 'payslips/' . $request["company_id"] . '/' . $request["company_id"] . '_' . $value->employee_id . '_' . $request["month"] . '_' . $request["year"] . '_payslip.pdf';
                if (Storage::disk('local')->exists($pdfFile_name)) {
                    $value->payslip_status = true;
                } else {
                    $value->payslip_status = false;
                }

                $value->payroll_month = $request["month"];
                $value->payroll_year = $request["year"];
            }
        }

        return $data;
    }
    public function scheduled_employees(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $employee = ScheduleEmployee::query();
        $model = $employee->where('company_id', $request->company_id);
        // $model =  $model->whereBetween('from_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        $model->whereDate('from_date', '<=', $date);
        $model->whereDate('to_date', '>=', $date);
        $model = $this->custom_with($model, "shift", $request->company_id);
        $model = $this->custom_with($model, "roster", $request->company_id);
        $model = $this->custom_with($model, "employee", $request->company_id);

        return $model
            ->paginate($request->per_page ?? 20);
    }

    public function scheduled_employees_list(Employee $employee, Request $request)
    {
        return $employee->where("company_id", $request->company_id)
            ->whereHas('schedule', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            })->paginate($request->per_page);
    }
    public function scheduled_employees_with_type(Employee $employee, Request $request)
    {
        return $employee->where("company_id", $request->company_id)
            ->whereHas('schedule')
            ->withOut(["user", "department", "sub_department", "sub_department", "designation", "role", "schedule"])
            ->get(["first_name", "system_user_id", "employee_id", "display_name"]);

        return $employee->whereHas('schedule.shift_type', function ($q) use ($request) {
            $q->where('slug', '=', $request->shift_type);
        })->get(["first_name", "system_user_id", "employee_id"]);
    }
    public function attendance_employees(Employee $employee, Request $request)
    {
        //
        return Attendance::with('employeeAttendance')->get('employee_id');
    }
    public function not_scheduled_employees(Employee $employee, Request $request)
    {
        return $employee->where("company_id", $request->company_id)
            ->whereDoesntHave('schedule', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            })
            ->paginate($request->per_page);
    }
    public function show(Employee $employee)
    {
        return $employee->with(["reportTo", "schedule", "user", "department", "sub_department", "designation", "role"])->whereId($employee->id)->first();
    }
    public function employeesList(Request $request)
    {
        $columns = $request->columns;
        $condition = gettype($columns) == "array" && !in_array("*", $columns) && count($columns) > 0 ? true : false;

        $model = Employee::query();
        $model->where(function ($q) use ($request) {
            $q->where('company_id', $request->company_id);
        });
        $model->when($condition, function ($q) use ($columns) {
            $q->select($columns);
            $q->withOut(["schedule", "department"]);
        });

        return $model->paginate($request->per_page);
    }

    public function employeesByDepartment(Request $request)
    {
        $model = Employee::query();
        $model->where('company_id', $request->company_id);

        if (!in_array("---", $request->department_ids)) {
            $model->whereIn("department_id", $request->department_ids);
        }

        if ($request->search && $request->filled('search')) {
            $model->where(function ($q) use ($request) {
                $q->orWhere('employee_id', $request->search);
                $q->orWhere('display_name', 'Like', '%' . $request->search . '%');
                $q->orWhere('first_name', 'Like', '%' . $request->search . '%');
            });
        }
        $model->with("department", function ($q) use ($request) {
            $q->whereCompanyId($request->company_id);
        });
        return $model->paginate($request->per_page);
    }
    public function employeesBySubDepartment(Request $request)
    {
        $model = Employee::query();
        $model->whereCompanyId($request->company_id);
        if (!in_array("---", $request->sub_department_ids)) {
            $model->whereIn("sub_department_id", $request->sub_department_ids);
        }

        if ($request->search && $request->filled('search')) {
            $model->where(function ($q) use ($request) {
                $q->orWhere('employee_id', $request->search);
                $q->orWhere('display_name', 'Like', '%' . $request->search . '%');
                $q->orWhere('first_name', 'Like', '%' . $request->search . '%');
            });
        }

        return $model->whereIn("department_id", $request->department_ids)->paginate($request->per_page);
    }
    public function employeesByDesignation($id, Request $request)
    {
        $model = Employee::query();
        $model = $this->FilterCompanyList($model, $request);
        if ($id) {
            $model->whereDesignationId($id);
            $model->whereCompanyId($request->company_id);
        }
        return $model->select('id', 'first_name', 'last_name')->get();
    }
    public function designationsByDepartment($id, Request $request, Designation $model)
    {
        $model = $this->FilterCompanyList($model, $request);
        if ($id) {
            $model->whereDepartmentId($id);
            $model->whereCompanyId($request->company_id);
        }
        return $model->select('id', 'name')->get();
    }
    public function update(Request $request, $id)
    {
        $data = $request->except(['contact_name', 'contact_no', 'contact_position', 'contact_whatsapp', 'user_name', 'email', 'password_confirmation', 'password']);
        if (isset($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        if (isset($request->logo)) {
            $data['logo'] = saveFile($request, 'media/company/logo', 'logo', $request->name, 'logo');
        }
        DB::beginTransaction();
        try {
            $record = Company::find($id);
            $record->update($data);
            $company = $request->setContactFields();
            CompanyContact::where('company_id', $record->id)->update($company);
            $user = $request->setUserFields();
            if (@$request->password) {
                $user['password'] = Hash::make($request->password);
            }
            User::find($record->user_id)->update($user);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        return Response::json([
            'record' => Company::with(['contact'])->find($id),
            'message' => 'Company Successfully updated.',
            'status' => true,
        ], 200);
    }
    public function employeeContactUpdate(Employee $model, ContactRequest $request)
    {
        try {
            $model->find($request->employee_id)->update($request->validated());
            return Response::json([
                'message' => 'Contact Successfully updated.',
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function destroy($id)
    {
        try {
            $record = Employee::find($id);
            if ($record->delete()) {
                $user = User::find($record->user_id);
                if ($user) {
                    $user->delete();
                }
                return Response::json(['message' => 'Employee Successfully deleted.', 'status' => true], 200);
            } else {
                return Response::json(['message' => 'No such record found.'], 404);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function search(Request $request, $key)
    {

        if ($request->datatable_column_filter && $request->datatable_column_filter == true) {

            $key = strtolower($key);

            return Employee::query()
                ->latest()
                ->with(["user", "department", "sub_department", "designation", "timezone"])
                ->where('company_id', $request->company_id)
                ->when($request->filled('search_employee_id'), function ($q) use ($request, $key) {
                    $q->where('employee_id', 'LIKE', "$key%");
                })
                ->when($request->filled('search_phone_number'), function ($q) use ($request, $key) {
                    $q->where('phone_number', 'LIKE', "$key%");
                })
                ->when($request->filled('search_employee_name'), function ($q) use ($request, $key) {
                    $q->where(function ($q) use ($key) {
                        $q->Where(DB::raw('lower(first_name)'), 'LIKE', "$key%");
                        $q->orWhere(DB::raw('lower(last_name)'), 'LIKE', "$key%");
                    });
                })
                ->when($request->filled('search_emailid'), function ($q) use ($request, $key) {
                    $q->where('local_email', 'LIKE', "$key%");
                })
                ->when($request->filled('search_department_name'), function ($q) use ($request, $key) {
                    $q->whereHas('department', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$key%"));
                    // $q->orWhereHas('sub_department', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$key%"));
                })
                ->when($request->filled('search_shiftname'), function ($q) use ($request, $key) {
                    $q->whereHas('schedule.shift', fn(Builder $query) => $query->where(DB::raw('lower(name)'), 'LIKE', "$key%"));
                    $q->whereHas('schedule.shift', fn(Builder $query) => $query->whereNotNull('name'));
                    $q->whereHas('schedule.shift', fn(Builder $query) => $query->where('name', '<>', '---'));

                })
                ->when($request->filled('search_timezonename'), function ($q) use ($request, $key) {
                    $q->whereHas('timezone', fn(Builder $query) => $query->where(DB::raw('lower(timezone_name)'), 'LIKE', "$key%"));

                })
                ->paginate($request->perPage ?? 20);
        } else {
            return Employee::query()
                ->latest()
                ->filter($key)
                ->with(["user", "department", "sub_department", "designation", "timezone"])
                ->where('company_id', $request->company_id)
                ->paginate($request->perPage ?? 20);
        }

    }
    public function scheduled_employees_search(Request $request, $input)
    {
        // $model = Employee::query();
        $model = ScheduleEmployee::query();
        $model->where('employee_id', $input);
        $model->where('company_id', $request->company_id);

        $model = $this->custom_with($model, "shift", $request->company_id);
        $model = $this->custom_with($model, "roster", $request->company_id);
        $model = $this->custom_with($model, "employee", $request->company_id);

        // return $model->whereHas('schedule', function ($q) use ($request) {
        //     $q->where('company_id', $request->company_id);
        // })

        return $model->paginate($request->perPage ?? 10);

        //  return $model->whereHas('schedule')->with(["reportTo", "schedule", "user", "department", "sub_department", "designation", "role"])->paginate($request->perPage ?? 10);
    }
    public function updateEmployee(EmployeeUpdateRequest $request, $id)
    {
        $data = $request->except(['user_name', 'email', 'password', 'password_confirmation']);
        $data['role_id'] = $request->role_id ?? 0;
        $employee = Employee::find($id);

        $user_arr = [
            'name' => $request->display_name,
            'email' => $request->email,
            'employee_role_id' => $request->role_id ?? 0,
        ];

        if ($request->password) {
            $user_arr['password'] = Hash::make($request->password);
        }

        User::where('id', $employee->user_id)->update($user_arr);

        // if ($request->role_id) {
        //     $user_arr['employee_role_id'] = $request->role_id;
        //     User::where('id', $employee->user_id)->update($user_arr);
        // }

        // $user = User::where('id', $employee->user_id)->update($user_arr);
        // if (isset($request->profile_picture)) {
        //     $arr['profile_picture'] = saveFile($request, 'media/employee/profile_picture', 'profile_picture', $request->name, 'profile_picture');
        // }
        if ($request->hasFile('profile_picture')) {
            //$profile_picture = $request->profile_picture->getClientOriginalName();
            $profile_picture = $id . '.jpg';
            $request->profile_picture->move(public_path('media/employee/profile_picture/'), $profile_picture);
            $product_image = url('media/employee/profile_picture/' . $profile_picture);
            $data['profile_picture'] = $profile_picture;
        }
        $employee->update($data);
        return Response::json([
            'record' => $employee,
            'message' => 'Employee Successfully Updated.',
            'status' => true,
        ], 200);
    }

    public function employeeLoginUpdate(Request $request, $id)
    {
        $arr = [];
        $arr["name"] = "null";
        $arr["email"] = $request->email;
        $arr["company_id"] = $request->company_id;
        $arr['password'] = Hash::make($request->password ?? "secret");

        try {
            if ($id > 0) {
                $user = User::where('id', $id)->update($arr);
            } else {

                $user = User::updateOrCreate(['email' => $request->email], $arr);

                Employee::where("id", $request->employee_id)->update(["user_id" => $user->id]);
            }

            if (!$user) {
                return $this->response('Employee cannot update.', null, false);
            }

            return $this->response('Employee successfully updated.', null, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateContact(Employee $model, EmployeeUpdateContact $request, $id)
    {
        // return $request->all();
        $model->whereId($id)->update($request->all());
        return Response::json([
            'record' => $model,
            'message' => 'Contact successfully Updated.',
            'status' => true,
        ], 200);
    }
    public function updateOther(Employee $model, EmployeeOtherRequest $request, $id): JsonResponse
    {
        $data = $request->except(['sub_department_id']);
        $model->whereId($id)->update($data);
        return Response::json([
            'record' => $model,
            'message' => 'Other details successfully Updated.',
            'status' => true,
        ], 200);
    }
    public function import(EmployeeImportRequest $request)
    {
        $file = $request->file('employees');
        $rowCount = file($file);

        $this->company_id = $request->company_id ?? 0;

        $company = Company::withCount('employees')->find($this->company_id);
        $totalEmployee = $company->employees_count ?? 0;
        $maxEmployee = $company->max_employee ?? 0;
        $remainingEmployee = max(0, (int) $maxEmployee - (int) $totalEmployee);

        if (!(count($rowCount) - 1 <= $remainingEmployee)) {
            return ["status" => false, "errors" => ["Employee limit exceed. Maximum limit is " . $maxEmployee]];
        }

        $data = $this->saveFile($file);

        if (is_array($data) && !$data["status"]) {
            return ["status" => false, "errors" => $data["errors"]];
        }

        $data = $this->csvParser($data);

        if (array_key_exists("status", $data)) {
            return ["status" => false, "errors" => $data["errors"]];
        }
        $success = false;
        DB::beginTransaction();
        try {
            foreach ($data as $data) {
                $validator = $this->validateImportData($data);
                if ($validator->fails()) {
                    return [
                        "status" => false,
                        "errors" => $validator->errors()->all(),
                    ];
                }
                $iteration = [
                    'name' => 'null',
                    'email' => $data['email'],
                    'password' => Hash::make('secret'),
                ];

                $record = User::create($iteration);
                $arr = [
                    'display_name' => trim($data['display_name']),
                    'phone_number' => trim($data['phone_number']),
                    'employee_id' => trim($data['employee_id']),
                    'joining_date' => trim(date('Y-m-d', strtotime($data['joining_date']))),
                    'user_id' => $record->id,
                    'company_id' => $this->company_id,
                    'system_user_id' => trim($data['employee_device_id']),
                    'department_id' => trim($data['department_code']),
                ];
                $success = Employee::create($arr) ? true : false;
            }
            if ($success) {
                DB::commit();
                return response()->json([
                    'message' => 'Employee imported successfully.',
                    'status' => true,
                ], 200);
            }

            return response()->json([
                'message' => 'Employee cannot import.',
                'status' => true,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    public function validateImportData($data)
    {
        $data["system_user_id"] = $data["employee_device_id"];

        $employee = [
            "employee_id" => $data["employee_id"],
            "company_id" => $this->company_id,
        ];

        $employeeDevice = [
            "system_user_id" => $data["system_user_id"],
            "company_id" => $this->company_id,
        ];

        $rules = [
            'employee_id' => ['required', $this->uniqueRecord("employees", $employee)],
            'system_user_id' => ['required', $this->uniqueRecord("employees", $employeeDevice)],
            'display_name' => ['required', 'min:3', 'max:10'],
            'email' => 'required|min:3|max:191|unique:users',
            'phone_number' => ['required', 'min:1', 'max:15'],
            'joining_date' => ['required', 'date'],
            'department_code' => ['required'],
        ];

        $messages = [
            'system_user_id.required' => "The employee device id is required",
            'employee_id.unique' => "The employee id (" . $data["employee_id"] . ") has already been taken",
            'system_user_id.unique' => "The employee device id (" . $data["system_user_id"] . ") has already been taken",
            'email.unique' => "The employee email (" . $data["email"] . ") has already been taken",
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function saveFile($file)
    {

        $filename = $file->getClientOriginalName();
        if ($filename != "employees.csv") {
            return [
                "status" => false,
                "errors" => ["wrong file " . $filename . " (valid file is employees.csv)"],
            ];
        }
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $location = 'upload';
        $file->move($location, $filename);
        // $file->storePubliclyAs($location, 'employee', "do");
        return public_path($location . "/" . $filename);
    }
    public function csvParser($filepath)
    {
        $columns = [
            "employee_id",
            "employee_device_id",
            "display_name",
            "email",
            "phone_number",
            "joining_date",
            "department_code",
        ];
        $header = null;
        $data = [];
        if (($filedata = fopen($filepath, "r")) !== false) {
            while (($row = fgetcsv($filedata, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                    // dd($row);
                    if ($header != $columns) {
                        return [
                            "status" => false,
                            "errors" => ["header mismatch"],
                        ];
                    }
                } else {
                    if (count($header) != count($row)) {
                        return [
                            "status" => false,
                            "errors" => ["column mismatch"],
                        ];
                    }
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($filedata);
        }
        if (count($data) == 0) {
            return [
                "status" => false,
                "errors" => "file is empty",
            ];
        }
        // dd($data);
        return $data;
    }
    public function employeeUpdateSetting(Request $request)
    {
        $model = Employee::query();
        $model
            ->where('employee_id', $request->employee_id)
            ->where('company_id', $request->company_id)
            ->update($request->only(['status', 'overtime', 'mobile_application']));

        return response()->json(['status' => true, 'message' => 'Setting successfully updated']);
    }
    public function employeeToReporter(Request $request, $id)
    {
        $model = Employee::Find($id);
        $model->reportTo()->attach($request->report_id);
        return response()->json(['status' => true, 'message' => 'Reporter successfully Assigned']);
    }
    public function employeeReporters()
    {
        $ids = DB::table('employee_report')->pluck('employee_id');
        return Employee::with('reportTo')->whereIn('id', $ids)->paginate();
    }
    public function getReporterByEmployee($id)
    {
        $emp = Employee::find($id);
        return $emp->reportTo;
    }
    public function employeeRemoveReporter(Request $request, $id)
    {
        DB::table('employee_report')
            ->where('employee_id', $id)
            ->where('report_id', $request->report_id)
            ->delete();
        return response()->json(['status' => true, 'message' => 'Reporter successfully Deleted']);
    }

    // public function employeeRemoveReporter($id)
    // {
    //     DB::table('employee_report')->where('employee_id', $id)->delete();
    //     return response()->json(['status' => true, 'message' => 'Reporter successfully Deleted']);
    // }
}