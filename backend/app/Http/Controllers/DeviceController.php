<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\Device\StoreRequest;
use App\Http\Requests\Device\UpdateRequest;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    public function getDeviceCompany(Request $request)
    {
        $device = DB::table("devices")->where("company_id", $request->company_id)->where("device_id", $request->DeviceID)->first(['name as device_name', 'short_name', 'device_id', 'location', "company_id"]);
        $model = DB::table("employees")->where("company_id", $request->company_id)->where("system_user_id", $request->UserCode)->first(['first_name', 'profile_picture']);

        if ($model && $model->profile_picture) {
            $model->profile_picture = asset('media/employee/profile_picture/' . $model->profile_picture);
        }

        return [
            "UserID" => $request->UserCode,
            "time" => date("H:i", strtotime($request->RecordDate)),
            "employee" => $model,
            "device" => $device,
        ];
    }

    public function getLastRecordsByCount($id, $count)
    {
        $model = AttendanceLog::query();
        $model->where('company_id', $id);
        $model->take($count);
        $model->orderByDesc("id");

        $logs = $model->get(["UserID", "LogTime", "DeviceID"]);

        $arr = [];

        foreach ($logs as $log) {


            $employee =  Employee::withOut(['schedule', 'department', 'sub_department', 'designation', 'first_log', 'last_log', 'user', 'role'])
                ->where('company_id', $id)
                ->where('system_user_id', $log->UserID)
                ->first(['first_name', 'profile_picture', 'company_id']);

            $dev =  Device::where('device_id', $log->DeviceID)
                ->first(['name as device_name', 'short_name', 'device_id', 'location']);

            if ($employee) {
                $arr[] = [
                    "company_id" => $employee->company_id,
                    "UserID" => $log->UserID,
                    "time" => date("H:i", strtotime($log->LogTime)),
                    "device" => $dev,
                    "employee" => $employee,
                ];
            }
        }

        return $arr;

        // Cache::forget("last-five-logs");
        return Cache::remember('last-five-logs', 300, function () use ($id, $count) {

            $model = AttendanceLog::query();
            $model->where('company_id', $id);
            $model->take($count);

            $logs = $model->get(["UserID", "LogTime", "DeviceID"]);

            $arr = [];

            foreach ($logs as $log) {


                $employee =  Employee::withOut(['schedule', 'department', 'sub_department', 'designation', 'first_log', 'last_log', 'user', 'role'])
                    ->where('company_id', $id)
                    ->where('system_user_id', $log->UserID)
                    ->first(['first_name', 'profile_picture', 'company_id']);

                $dev =  Device::where('device_id', $log->DeviceID)
                    ->first(['name as device_name', 'short_name', 'device_id', 'location']);

                if ($employee) {
                    $arr[] = [
                        "company_id" => $employee->company_id,
                        "UserID" => $log->UserID,
                        "time" => date("H:i", strtotime($log->LogTime)),
                        "device" => $dev,
                        "employee" => $employee,
                    ];
                }
            }

            return $arr;
        });
    }

    public function getLastRecordsByCountTEST($id, $count)
    {
        $model = AttendanceLog::query();
        $model->where('company_id', $id);
        $model->take($count);

        $logs = $model->get(["UserID", "LogTime", "DeviceID"]);

        $arr = [];

        foreach ($logs as $log) {

            $employee =  Employee::withOut(['schedule', 'department', 'sub_department', 'designation', 'first_log', 'last_log', 'user', 'role'])
                ->where('company_id', $id)
                ->where('system_user_id', $log->UserID)
                ->first(['first_name', 'profile_picture', 'company_id']);

            $dev =  Device::where('device_id', $log->DeviceID)
                ->first(['name as device_name', 'short_name', 'device_id', 'location']);

            if ($employee) {
                $arr[] = [
                    "company_id" => $employee->company_id,
                    "UserID" => $log->UserID,
                    "time" => date("H:i", strtotime($log->LogTime)),
                    "device" => $dev,
                    "employee" => $employee,
                ];
            }
        }

        return $arr;
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
            'name', 'device_id', 'location', 'short_name',
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

    public function sync_device_date_time(Request $request, $device_id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sdk.ideahrms.com/FC-8300T20094123/SyncDateTime",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{ "dateTime": "2022-11-01 11:11:16" }',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $status = json_decode($response);

        return [$status, $request->sync_able_date_time];

        if ($status->status !== 200) {
            return $this->response("The device is not connected to the server or is not registered", null, false);
        }

        try {
            $record = Device::where("device_id", $device_id)->update([
                "sync_date_time" => $request->sync_able_date_time
            ]);

            if ($record) {
                return $this->response('Time has been synced to the Device.', Device::where("device_id", $device_id)->first(), true);
            } else {
                return $this->response('Time cannot synced to the Device.', null, false);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
