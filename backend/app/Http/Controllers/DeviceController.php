<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\Device\StoreRequest;
use App\Http\Requests\Device\UpdateRequest;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Cache;

class DeviceController extends Controller
{
    public function index(Device $model, Request $request)
    {
        return $model->with(['status', 'company'])->where('company_id', $request->company_id)->paginate($request->per_page ?? 1000);
    }

    public function getDeviceList(Device $model, Request $request)
    {
        return $model->with(['status'])->where('company_id', $request->company_id)->get();
    }

    public function store(Device $model, StoreRequest $request)
    {

        // $record = false;
        try {
            // $response = Http::post(env("LOCAL_IP") .':'. env("LOCAL_PORT") . '/Register', [
            //     'sn' => $request->device_id, //OX-8862021010010
            //     'ip' => $request->ip,
            //     'port' => $request->port,
            // ]);

            // if ($response->status() == 200) {
            //     $record = $model->create($request->validated());
            // }

            $record = $model->create($request->validated());

            if ($record) {
                return $this->response('Device successfully added.', $record, true);
            } else {
                return $this->response('Device cannot add.', null, 'device_api_error');
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show(Device $model, $id)
    {
        return $model->with(['status', 'company'])->find($id);
    }

    public function getDeviceCompany($id, $userId)
    {
        $emp = Employee::whereSystemUserId($userId)->without(['schedule', 'user', 'sub_department', 'role', 'first_log', 'last_log',])->first(['first_name', 'profile_picture'])->toArray();
        $device =  Device::where("device_id", $id)->first(['company_id', 'name as device_name', 'short_name', 'device_id', 'location'])->toArray();

        return array_merge($emp, $device);
    }

    public function getLastRecordsByCount($id, $count)
    {
        // Cache::forget("last-five-logs");
        return Cache::remember('last-five-logs', 300, function () use ($id, $count) {

            $model = AttendanceLog::query();
            $model->where('company_id', $id);
            $model->take($count);
            $model->orderByDesc("id");
            $model->with([
                "device:id,company_id,name as device_name,short_name,device_id,location",
                "employee:id,first_name,profile_picture,system_user_id",
            ]);
            return $model->get();
        });
    }

    public function update(Device $Device, UpdateRequest $request)
    {
        try {
            $record = $Device->update($request->validated());

            if ($record) {
                return $this->response('Device successfully updated.', $record, true);
            } else {
                return $this->response('Device cannot update.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Device $device)
    {
        try {
            $record = $device->delete();

            if ($record) {
                return $this->response('Device successfully deleted.', $record, true);
            } else {
                return $this->response('Device cannot delete.', null, 'device_api_error');
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function search(Request $request, $key)
    {
        $model = Device::query();

        $fields = [
            'name', 'device_id', 'location',
            'status' => ['name'],
            'company' => ['name'],
        ];

        $model = $this->process_search($model, $key, $fields);

        $model->with(['status', 'company']);

        return $model->paginate($request->per_page);
    }
    public function deleteSelected(Device $model, Request $request)
    {
        try {
            $record = $model->whereIn('id', $request->ids)->delete();

            if ($record) {
                return $this->response('Device successfully deleted.', $record, true);
            } else {
                return $this->response('Device cannot delete.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
