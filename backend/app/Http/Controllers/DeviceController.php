<?php

namespace App\Http\Controllers;

use App\Http\Requests\Device\StoreRequest;
use App\Http\Requests\Device\UpdateRequest;
use App\Mail\EmailNotificationForOfflineDevices;
use App\Mail\SendEmailNotificationForOfflineDevices;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceActivesettings;
use App\Models\DeviceNotification;
use App\Models\DeviceNotificationsLog;
use App\Models\DevicesActiveWeeklySettings;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class DeviceController extends Controller
{
    const ONLINE_STATUS_ID = 1;
    const OFFLINE_STATUS_ID = 2;

    public function dropdownList()
    {
        $model = Device::query();
        $model->where('company_id', request('company_id'));
        $model->when(request()->filled('branch_id'), fn ($q) => $q->where('branch_id', request('branch_id')));
        $model->orderBy(request('order_by') ?? "name", request('sort_by_desc') ? "desc" : "asc");
        return $model->get(["id", "name", "location", "device_id", "device_type"]);
    }

    public function index(Request $request)
    {
        $model = Device::query();

        $model->excludeMobile();

        $cols = $request->cols;
        $model->with(['status', 'company', 'companyBranch']);
        $model->where('company_id', $request->company_id);
        $model->when($request->filled('name'), function ($q) use ($request) {
            $q->where('name', 'ILIKE', "$request->name%");
        });
        $model->when($request->filled('short_name'), function ($q) use ($request) {
            $q->where('short_name', 'ILIKE', "$request->short_name%");
        });
        $model->when($request->filled('location'), function ($q) use ($request) {
            $q->where('location', 'ILIKE', "$request->location%");
        });
        $model->when($request->filled('device_id'), function ($q) use ($request) {
            $q->where('device_id', 'ILIKE', "%$request->device_id%");
        });
        $model->when($request->filled('device_type'), function ($q) use ($request) {
            $q->where('device_type', 'ILIKE', "$request->device_type%");
        });
        $model->when($request->filled('Status'), function ($q) use ($request) {
            $q->where('status_id', $request->Status);
        });
        $model->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        });



        // array_push($cols, 'status.id');

        $model->when(isset($cols) && count($cols) > 0, function ($q) use ($cols) {
            $q->select($cols);
        });

        $model->when($request->filled('sortBy'), function ($q) use ($request) {
            $sortDesc = $request->input('sortDesc');
            if (strpos($request->sortBy, '.')) {
                // if ($request->sortBy == 'department.name.id') {
                //     $q->orderBy(Department::select("name")->whereColumn("departments.id", "employees.department_id"), $sortDesc == 'true' ? 'desc' : 'asc');

                // }

            } else {
                $q->orderBy($request->sortBy . "", $sortDesc == 'true' ? 'desc' : 'asc'); {
                }
            }
        });

        if (!$request->sortBy)
            $model->orderBy("name", "ASC");
        return $model->paginate($request->per_page ?? 1000);

        //return $model->with(['status', 'company'])->where('company_id', $request->company_id)->paginate($request->per_page ?? 1000);
    }

    public function getDeviceList(Device $model, Request $request)
    {
        return $model->with(['status'])->where('company_id', $request->company_id)->get();
    }
    public function getDevicePersonDetails(Request $request)
    {
        if ($request->system_user_id > 0) {
            $deviceName = Device::where('device_id', $request->device_id)->pluck('name')[0];

            $responseData = (new SDKController())->getPersonDetails($request->device_id, $request->system_user_id);

            return ["SDKresponseData" => json_decode($responseData), "deviceName" => $deviceName, "device_id" => $request->device_id];
        } else {
            return ["SDKresponseData" => "", "message" => "Visitor Device id is not avaialble ", "deviceName" => false, "device_id" => $request->device_id];
        }
    }
    public function store(StoreRequest $request)
    {
        try {

            $maxDevices = Company::find($request->company_id)->max_devices;
            $totalAvailable = Device::where("company_id", $request->company_id)->count();



            if ($maxDevices - $totalAvailable <= 0) {
                return $this->response('Device limit reached. Max Devices :' . $maxDevices, null, false);
            }




            $model = Device::query();
            $model->where("company_id", $request->company_id);
            $model->where("device_id", $request->device_id);
            $model->where("name", $request->name);

            if ($model->exists()) {
                return $this->response('Device already exist.', null, true);
            }

            $data = $request->validated();




            $data["ip"] = "0.0.0.0";
            $data["port"] = "0000";
            $record = $model->create($data);

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

    public function getDeviceByUserId(Device $model, $id)
    {
        return $model->where("device_id", $id)->first();
    }

    public function getDeviceCompany(Request $request)
    {
        $device = DB::table("devices")->where("company_id", $request->company_id)->where("device_id", $request->SN)->first(['name as device_name', 'short_name', 'device_id', 'location', "company_id"]);
        $model = DB::table("employees")->where("company_id", $request->company_id)->where("system_user_id", $request->UserCode)->first(['first_name', 'display_name', 'profile_picture']);

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
    public function getLastRecordsHistory($id = 0, $count = 0, Request $request)
    {

        // return Employee::select("system_user_id")->where('company_id', $request->company_id)->get();

        $model = AttendanceLog::query();
        $model->with(array('employee' => function ($query) use ($request) {
            $query->where('company_id', $request->company_id);
        }))->first();

        $model->with(['device']);
        $model->where('company_id', $id);
        $model->when($request->filled("branch_id"), function ($q) use ($request) {
            $q->whereHas("employee", fn ($q) => $q->where("branch_id", $request->branch_id));
        });
        $model->whereIn('UserID', function ($query) use ($request) {
            // $model1 = Employee::query();
            // $model1->select("system_user_id")->where('employees.company_id', $request->company_id);

            $query->select('system_user_id')->from('employees')->where('employees.company_id', $request->company_id);
        });
        $model->when($request->filled('system_user_id'), function ($q) use ($request) {

            $q->Where('UserID',   $request->system_user_id);
        });
        $model->when($request->filled('search_time'), function ($q) use ($request) {
            $key = date('Y-m-d') . ' ' . $request->search_time;
            $q->Where('LogTime', 'LIKE', "$key%");
        });
        $model->when($request->filled('search_device_id'), function ($q) use ($request) {
            $key = strtoupper($request->search_device_id);
            //$q->Where(DB::raw('lower(DeviceID)'), 'LIKE', "$key%");
            $q->Where('DeviceID', 'LIKE', "$key%");
        });

        if (!$request->sortBy) {

            $model->orderBy("LogTime", 'desc');
        }
        //$model->orderByDesc("LogTime");
        $logs = $model->paginate($request->per_page);

        return $logs;
    }
    public function getLastRecordsByCount($id = 0, $count = 0, Request $request)
    {
        $model = AttendanceLog::query();
        $model->where('company_id', $id);
        $model->with(["employee" => function ($q) use ($id) {
            $q->where('company_id', $id);
            $q->withOut(['schedule', 'department', 'sub_department', 'designation', 'user', 'role']);
            $q->select('first_name', 'last_name', 'employee_id', 'system_user_id', 'display_name', 'profile_picture', 'company_id');
        }]);
        $model->take($count);
        $model->orderByDesc("id");
        return $model->get()->toArray();

        // Cache::forget("last-five-logs");
        return Cache::remember('last-five-logs', 300, function () use ($id, $count) {

            $model = AttendanceLog::query();
            $model->where('company_id', $id);
            $model->take($count);

            $logs = $model->get(["UserID", "LogTime", "DeviceID"]);

            $arr = [];

            foreach ($logs as $log) {

                $employee = Employee::withOut(['schedule', 'department', 'sub_department', 'designation', 'user', 'role'])
                    ->where('company_id', $id)
                    ->where('system_user_id', $log->UserID)
                    ->first(['first_name', 'profile_picture', 'company_id']);

                $dev = Device::where('device_id', $log->DeviceID)
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
        // $url = "http://139.59.69.241:7000/$device_id/SyncDateTime";
        $url = env('SDK_URL') . "/$device_id/SyncDateTime";

        $data = [
            'dateTime' => $request->sync_able_date_time
        ];

        // return ["url" => $url, "data" => $data];

        $response = $this->SDKCommand($url, $data);

        $result = json_decode($response);

        if ($result && $result->status == 200) {
            try {
                $record = Device::where("device_id", $device_id)->update([
                    "sync_date_time" => $request->sync_able_date_time,
                ]);

                if ($record) {
                    return $this->response('Time has been synced to the Device.', Device::with(['status', 'company'])->where("device_id", $device_id)->first(), true);
                } else {
                    return $this->response('Time cannot synced to the Device.', null, false);
                }
            } catch (\Throwable $th) {
                return $this->response('Time cannot synced to the Device.', null, false);
            }
        } else if ($result && $result->status == 102) {
            return $this->response("The device is not connected to the server or is not registered", $result, false);
        }

        return $this->response("Unkown Error. Please retry again after 1 min or contact to technical team", null, false);
    }

    public function devcieCountByStatus($company_id)
    {
        // Use query builder to build the queries more fluently
        $statusCounts = Device::where('company_id', $company_id)
            ->whereIn('status_id', [1, 2])
            ->selectRaw('status_id, COUNT(*) as count')
            ->groupBy('status_id')
            ->get();

        $onlineDevices = 0;
        $offlineDevices = 0;

        foreach ($statusCounts as $statusCount) {
            if ($statusCount->status_id == 1) {
                $onlineDevices = $statusCount->count;
            } elseif ($statusCount->status_id == 2) {
                $offlineDevices = $statusCount->count;
            }
        }

        return [
            "total" => $onlineDevices + $offlineDevices,
            "labels" => ["Online", "Offline"],
            "series" => [$onlineDevices, $offlineDevices],
        ];
    }
    public function getActiveTimeSettings(Request $request, $key_id)
    {
        $model =  DeviceActivesettings::where('company_id', $request->company_id)
            ->where('device_id', $key_id)
            ->where('date_from', $request->date_from)
            ->where('date_to', $request->date_to)
            ->get();
        $input_time_slots = $request->input_time_slots;

        $open_array = [];
        if (isset($model[0])) {

            $open_json = $model[0]->open_json;
            $open_array = json_decode($open_json, true);
        }
        $return_araay = [];

        foreach ($open_array as $values) {

            foreach ($values as $key => $val) {

                $return_araay[] = $key . '-' . $key = array_search($val, $input_time_slots);
            }
        }


        return $return_araay;
    }

    public function checkDeviceHealth(Request $request)
    {
        $devices = Device::where("company_id", $request->company_id ?? 0)->where("device_type", "!=", "Mobile")->pluck("device_id");

        $total_iterations = 0;
        $online_devices_count = 0;
        $offline_devices_count = 0;

        $sdk_url = env("SDK_URL");

        if (checkSDKServerStatus($sdk_url) === 0) {
            return "Failed to connect to the SDK Server: $sdk_url";
        }
        $return_araay = [];
        foreach ($devices as $device_id) {
            $curl = curl_init();

            if (!$sdk_url) {
                return "sdk url not defined.";
            }

            curl_setopt_array($curl, array(

                // CURLOPT_URL => "https://sdk.ideahrms.com/CheckDeviceHealth/$device_id",
                // CURLOPT_URL => "http://139.59.69.241:5000/CheckDeviceHealth/$device_id",
                CURLOPT_URL => "$sdk_url/CheckDeviceHealth/$device_id",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));

            $response = curl_exec($curl);



            curl_close($curl);

            $status = json_decode($response);

            if ($status && $status->status == 200) {
                $online_devices_count++;
            } else {
                $offline_devices_count++;
            }

            Device::where("device_id", $device_id)->update(["status_id" => $status->status == 200 ? 1 : 2]);

            $total_iterations++;
        }

        return   "$offline_devices_count Devices offline. $online_devices_count Devices online. $total_iterations records found.";
    }
    public function updateActiveTimeSettings(Request $request, $device_id)
    {




        $input_days = $request->input_days;
        $input_time_slots = $request->input_time_slots;
        $span_time_minutes = $request->span_time_minutes;

        $selected_matrix = json_decode($request->selected_matrix);


        $days_array = [];
        $open_time_array = [];
        $closing_time_array = [];

        $input_strings = $selected_matrix; //["0-0", "1-1", "2-2", "3-3", "4-4", "5-5", "6-6", "0-2", "1-3", "2-4", "3-5", "4-6", "5-7", "6-8"];

        $open_time_array = [];
        $closing_time_array = [];

        foreach ($input_strings as $string) {
            list($key, $value) = explode('-', $string);
            if (isset($input_time_slots[$value])) {
                $newtimestamp = strtotime(date('Y-m-d ' . $input_time_slots[$value] . ':00 ') . '+ ' . $span_time_minutes . ' minute');
                $close_time = date('H:i', $newtimestamp);

                $open_time_array[] = ['' .  $key . '' => $input_time_slots[$value]]; //$key . '-' . $input_time_slots[$value];
                $closing_time_array[] = ['' .  $key . '' => $close_time]; //$key . '-' . $input_time_slots[$value];

            }
        }

        $device_settings_id = '';

        $devices_active_settings_array = [
            'device_id' => $device_id, 'company_id' =>  $request->company_id, 'date_from' => $request->date_from, 'date_to' => $request->date_to,   'open_json' => json_encode($open_time_array), 'close_json' => json_encode($closing_time_array)
        ];

        $record = DeviceActivesettings::where("device_id", $device_id)->where("company_id", $request->company_id);
        if ($record->count()) {


            $device_settings_id = $record->get()[0]->id;
            $record->update($devices_active_settings_array);
        } else {
            $device_settings_id =  DeviceActivesettings::create($devices_active_settings_array)->id;
        }


        return   [
            'record' => $device_settings_id,
            'message' => 'Successfully Updated.',
            'status' => true
        ];
    }

    public function handleNotification($id)
    {

        $company = Company::where("id", $id)->where("is_offline_device_notificaiton_sent", false)->first();

        if ($company) {
            $notifications = DeviceNotification::with("managers")->where("company_id", $id)->get();


            foreach ($notifications as $key => $notification) {



                $company = $company->load(['devices' => function ($q) use ($notification) {
                    $q->where("status_id", self::OFFLINE_STATUS_ID)
                        ->where("branch_id", $notification->branch_id)
                        ->where("name", "!=", "Mobile");
                }]);

                $counter = 1;
                if ($company) {
                    $offlineDevicesCount = count($company->devices);

                    // if (!$offlineDevicesCount) {
                    //     return $this->getMeta("SendNotificatinForOfflineDevices", "All Devices Online");
                    // }
                    $location_array = array_column($company->devices->toArray(), "location");
                    $devicesLocation = json_encode($location_array);
                    $devicesLocations = '';
                    foreach ($location_array as $key => $location) {
                        $devicesLocations .= ($counter++) . ':' . $location;
                        if ($key < count($location_array) - 1) {
                            $devicesLocations .= ",\n";
                        }
                    }

                    // $this->sendWhatsappNotification($message, '971554501483');
                    // $this->sendWhatsappNotification($message, '971553303991'); 


                    $this->sendNotification($notification, $company, $offlineDevicesCount, $devicesLocations, "", $company->devices);

                    $company->update(["is_offline_device_notificaiton_sent" => true, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
                }
            }
            return "Notification sent to WhatsApp and email.";
        } else {
            return "Already sent. Waiting for schedule time";
        }
    }

    public function sendNotification($notification, $company, $offlineDevicesCount, $devicesLocations, $message, $devices)
    {

        foreach ($notification->managers as $key => $manager) {

            $message = "🔔 *Notification for offline devices* 🔔\n\n";
            $message .= "*Hello, {$manager->name}*\n\n";
            $message .= "*Company: , {$company->name}*\n\n";
            $message .= "Total *({$offlineDevicesCount})* of your devices are currently offline. Please take a look and address the issue as needed to avoid any errors in report.\n\n";
            $message .= "*Devices location(s):* \n{$devicesLocations}  \n\n";
            $message .= "If you have any questions or need assistance, feel free to reach out.\n\n";
            $message .= "Best regards\n";
            $message .= "*MyTime2Cloud*";



            $data = [
                "company_id" => $company->id,
                "branch_id" => $notification->branch_id,
                "notification_id" => $notification->id,
                "notification_manager_id" => $manager->id,
                "email" => null,
                "whatsapp_number" => null,
                "message" => $message
            ];

            // return  view('emails.TestmailFormat', ["company" => $company, "offlineDevicesCount" => $offlineDevicesCount, "devicesLocations" => $devicesLocations, "manager" => $manager]);
            // return false;
            $branch_name = $notification->branch ?  $notification->branch->branch_name :  '---';
            if (in_array("Email", $notification->mediums)) {
                if ($manager->email != '') {
                    Mail::to($manager->email)->send(new EmailNotificationForOfflineDevices($company, $offlineDevicesCount, $devices, $manager, $branch_name));

                    $data["email"] = $manager->email;
                }
            }
            if (in_array("Whatsapp", $notification->mediums)) {
                if ($manager->whatsapp_number != '' && strlen($manager->whatsapp_number) > 10) {
                    (new WhatsappController)->sendWhatsappNotification($company, $message, $manager->whatsapp_number);
                    $data["whatsapp_number"] = $manager->whatsapp_number;
                }
            }

            DeviceNotificationsLog::create($data);
        }
    }

    public function seedDefaultData($company_id)
    {
        $data = [];

        foreach (range(1, 10) as $i) {
            $data[] = [
                "company_id" => $company_id,
                "branch_id" => 1,
                "status_id" => 1,
                "name" => "demo-" . $i,
                "short_name" => "demo-" . $i,
                "device_id" => "OX-886202011" . $i,
                "location" => "demo-" . $i,
                "ip" => "0.0.0.0",
                "port" => "00000",
                "model_number" => "demo-" . $i,
                "device_type" => "Access Control",
                "synced" => 0,
                "function" => "demo",
                "serial_number" => "demo-" . $i,
                "utc_time_zone" => "demo"
            ];
        }

        Device::insert($data);

        return "Cron DeviceSeeder: " . count($data) . " record has been inserted.";
    }
}
