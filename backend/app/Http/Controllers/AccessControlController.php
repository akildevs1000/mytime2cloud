<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AccessControlController extends Controller
{
    public function index(Request $request)
    {

        $model = AttendanceLog::query();

        $model->whereHas('device', fn ($q) => $q->where('device_type', 'Access Control'));

        $model->where("company_id", $request->company_id);

        $model->when($request->filled('employee_id'), fn ($q) => $q->where('UserID', $request->employee_id));

        $model->when($request->filled('device_id'), fn ($q) => $q->where('DeviceID', $request->device_id));

        $model->when($request->filled('branch_id'), function ($query) {
            $query->whereHas("employee", fn ($q) => $q->where('branch_id', request("branch_id")));
        });

        $model->when($request->filled('dates') && count($request->dates) > 1, function ($q) use ($request) {
            $q->where(function ($query) use ($request) {
                $query->where('LogTime', '>=', $request->dates[0])
                    ->where('LogTime', '<=',   date("Y-m-d", strtotime($request->dates[1] . " +1 day")));
            });
        });

        $model->with(["device" => fn ($q) => $q->where("company_id", $request->company_id)]);

        $model->with([
            "employee" => function ($q) use ($request) {
                $q->select("system_user_id", "first_name", "last_name", "display_name", "employee_id", "profile_picture", "branch_id");
                $q->where("company_id", $request->company_id);
                $q->withOut(["schedule", "department", "sub_department", "designation"]);
            }
        ]);

        $model->orderBy('LogTime', 'DESC');

        return $model->paginate($request->per_page);
    }
}
