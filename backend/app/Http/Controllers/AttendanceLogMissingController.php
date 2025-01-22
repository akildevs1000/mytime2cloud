<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Storage;

class AttendanceLogMissingController  extends Controller
{
    public function GetMissingLogs(Request $request)
    {
        $verification_methods = array(
            1 => "Card",
            2 => "Fing",
            3 => "Face",
            4 => "Fing + Card",
            5 => "Face + Fing",
            6 => "Face + Card",
            7 => "Card + Pin",
            8 => "Face + Pin",
            9 => "Fing + Pin",
            10 => "Manual",
            11 => "Fing + Card + Pin",
            12 => "Face + Card + Pin",
            13 => "Face + Fing + Pin",
            14 => "Face + Fing + Card",
            15 => "Repeated"
        );

        // PHP array for reasons
        $reasons = array(
            16 => "Date Expire",
            17 => "Timezone Expire",
            18 => "Holiday",
            19 => "Unregistered",
            20 => "Detection lock",
            23 => "Loss Card",
            24 => "Blacklisted",
            25 => "Without Verification",
            26 => "No Card Verification",
            27 => "No Fingerprint"
        );

        try {


            $source_info = $request->device_healthcheck ?? '';

            //$source_info .= $request->company_id == 0 ?  'Master_' : $request->company_id;
            $source_info .= $request->source   ??  '-';

            $source_info .= "_missing_logs_" . date("Y-m-d H:i:s");
            $total_records = 0;
            //$deviceId = "FC-8300T20094123";
            //$company_id = 2;
            ///$date = "2022-09-20";

            $company_id = $request->company_id;
            $date = $request->date;
            $finalResult = [];
            $date = date('Y-m-d', strtotime($date . ' + 1 days'));

            $device = null;
            $deviceId = $request->device_id;
            $model_number = '';
            //if ($company_id == 0) 
            {
                $device = Device::where("device_id", $deviceId)->orderBy("id", "DESC")->first();

                if ($device == null)     return [
                    "status" => 102,
                    "message" =>  "Device Serial Number is not found",
                ];;
                $company_id = $device["company_id"];
                $model_number = $device["model_number"];
            }
            $deviceSession = (new DeviceCameraModel2Controller($device->camera_sdk_url, $device->device_id));

            if ($device && $model_number == 'OX-900') {

                // 

                // // return $personsFromDevice = $deviceSession->getAllPersons(100);
                $startTime = \Carbon\Carbon::parse($request->date . 'T00:00:00');
                $endTime = \Carbon\Carbon::parse($request->date . 'T23:59:59');

                $interval = \Carbon\CarbonInterval::hour(); // 1-hour interval
                $limit = 10; // Your desired limit
                $return = [];
                while ($startTime->lessThan($endTime)) {
                    $beginTime = $startTime->toIso8601String();
                    $endTimeCurrent = $startTime->copy()->addHours(1)->toIso8601String();
                    $startTime->addHours(1); // Increment by one hour



                    $json = '{
                    "request_id": "87110c822b67e054f72b5c4d90fc51c2",
                    "limit": 10,
                    "offset": 0,
                    "sort": "asc",
                    "begin_time": "' . $beginTime . '",
                    "end_time": "' . $endTimeCurrent . '",
                    "query_string": "",
                    "query_person_idx": "",
                    "query_nopass": false
                  }';


                    $finalResult[] = $responseArray = $deviceSession->getHistory($deviceId, $json);

                    if (isset($response['status'])) {
                        $data = (new SDKController())->getAllData();
                        unset($data[$deviceId]);

                        (new SDKController())->clearSessionData($deviceId);

                        $deviceSession = (new DeviceCameraModel2Controller($device->camera_sdk_url, $device->device_id));

                        $finalResult[] = "Reset Session";
                        $finalResult[] = $responseArray = $deviceSession->getHistory($deviceId, $json);
                    }
                    if (isset($responseArray['data'])) {
                        foreach ($responseArray['data'] as $record) {
                            $timestamp = $record["timestamp"];
                            $logtime = \Carbon\Carbon::parse($timestamp)->format('Y-m-d H:i:s');
                            $clock_status = $request->clock_status;

                            if ($request->clock_status == 'Clock On') $clock_status = "In";
                            else if ($request->clock_status == 'Clock Off') $clock_status = "Out";

                            $data = [
                                "UserID" => $record['person_code'],
                                "DeviceID" => $deviceId,
                                "LogTime" =>  $logtime,
                                "SerialNumber" => null,
                                "status" => "Allowed",
                                "mode" =>  $record['pass_mode']  ?? "---",
                                "log_type" => $clock_status,
                                "company_id" => $company_id,
                                "source_info" => $source_info,
                            ];

                            $condition = ['UserID' => $record['person_code'], 'DeviceID' => $deviceId,  'LogTime' => $logtime];
                            $exists = AttendanceLog::where('UserID', $record['person_code'])
                                ->where('DeviceID', $deviceId)
                                ->where('LogTime', $logtime)
                                ->exists();

                            if (!$exists) {
                                AttendanceLog::create($data);

                                $finalResult[] =  ['UserID' => $record['person_code'], 'DeviceID' => $deviceId,  'LogTime' => $logtime];
                            }
                        }
                    } //if


                } //whil e

                if (count($finalResult) > 0)
                    log_message($deviceId . "Company: " . $company_id . " Missing Logs Updated count ---- " . count($finalResult), $company_id . "_check_device_health");



                // else
                //     log_message($deviceId . "Company: " . $company_id . " Missing Logs Updated count " . count($finalResult), $company_id . "_check_device_health");
                return  [
                    "status" => 200,
                    "message" => "success",
                    "updated_records" => $finalResult,
                    "total_device_records" => count($finalResult),

                ];
            } else {




                $indexSerialNumber = 0;

                // return $indexSerialNumberModel = AttendanceLog::query()
                //     ->where('company_id', $company_id)
                //     // ->where('SerialNumber', '>', 0)
                //     // ->where('DeviceID', $deviceId)
                //     //->whereRaw("SerialNumber ~ '^[0-9]+$'") // Filters numeric values only
                //     ->orderBy("id", "DESC") // Casts SerialNumber for sorting
                //     ->first();

                //find serial number 
                $indexSerialNumberModel = AttendanceLog::where("company_id", $company_id)
                    ->whereDate("LogTime", '<=', $date)
                    ->where("SerialNumber", '>', 0)

                    ->where("DeviceID",   $deviceId)->orderBy("LogTime", "DESC")->first();
                if ($indexSerialNumberModel) {
                    $indexSerialNumber = $indexSerialNumberModel->SerialNumber;
                }


                // if ($indexSerialNumber > 0) {

                $url = env("SDK_URL") . "/"  . $deviceId . "/GetRecordByIndex";
                //$url =   "https://sdk.mytime2cloud.com/" . $deviceId . "/GetRecordByIndex";
                $data =  [
                    "TransactionType" => 1,
                    "Quantity" => 100,
                    "ReadIndex" => $indexSerialNumber - 100
                ];

                $data = json_encode($data);


                $records = $this->culrmethod($url, $data);
                $records = json_decode($records, true);

                if (isset($records['status']))
                    if ($records['status'] != 200) {
                        $records['message'];

                        return [
                            "status" => 100,
                            "message" => $this->trasformResponseFromChineesetoEnglish($records['message'] ?? ""),
                            "updated_records" => [],
                            "total_device_records" => [],
                            "indexSerialNumber" => $indexSerialNumber,
                        ];
                    }
                $finalResult = [];
                $finalAlreadyExist = [];

                foreach ($records['data'] as $record) {

                    $logtime = substr(str_replace(" ", " ", $record['recordDate']), 0, -3);
                    $data = [
                        "UserID" => $record['userCode'],
                        "DeviceID" => $deviceId,
                        "LogTime" =>  $logtime,
                        "SerialNumber" => $record['recordNumber'],
                        "status" => $record['recordCode'] > 15 ? "Access Denied" : "Allowed",
                        "mode" => $verification_methods[$record['recordCode']] ?? "---",
                        "reason" => $reasons[$record['recordCode']] ?? "---",
                        "company_id" => $company_id,
                        "source_info" => $source_info,
                    ];

                    $condition = ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $logtime];
                    $exists = AttendanceLog::where('UserID', $record['userCode'])
                        ->where('DeviceID', $deviceId)
                        ->where('LogTime', $logtime)
                        ->exists();

                    if (!$exists) {
                        AttendanceLog::create($data);

                        $finalResult[] =  ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $logtime, "SerialNumber" => $record['recordNumber']];
                    } else {
                        $finalAlreadyExist[] =  ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $logtime, "SerialNumber" => $record['recordNumber'], "status" => "already exist"];
                    }
                    // $status = AttendanceLog::firstOrCreate(
                    //     $condition,
                    //     ['UserID' => $record['userCode'], 'DeviceID' => $deviceId,  'LogTime' => $record['recordDate']], // Search by email and username
                    //     $data  // Optional attributes to set if the user doesn't exist
                    // );


                }
                // } else {
                //     return [
                //         "status" => 120,
                //         "message" => "Device has no  records found on this date " . $date,
                //         "updated_records" => [],
                //         "total_device_records" => [],
                //         "indexSerialNumber" => $indexSerialNumber,

                //     ];
                // }




                $message = [
                    //"data" => $records['data'],
                    "status" => 200,
                    "message" => "success",
                    "updated_records" => $finalResult,
                    "finalAlreadyExist" => $finalAlreadyExist,
                    "total_device_records" => count($records['data']),
                    "indexSerialNumber" => $indexSerialNumber,
                ];
                if (count($finalResult) > 0)
                    log_message($deviceId . "Company: " . $company_id . " Missing Logs Updated count " . count($finalResult), $company_id . "_check_device_health");

                // $file_name_raw = "device_missing_logs_" . date("d-m-Y") . ".txt";
                // $message = date("Y-m-d H:i:s") . "_" . json_encode($message);
                // Storage::append($file_name_raw, json_encode($message));





                return $message;
            }
        } catch (\Exception $e) {
            return [
                "status" => 102,
                "message" => $e->getMessage(),
            ];
            // You can log the error or perform any other necessary actions here
        }
    }

    public function trasformResponseFromChineesetoEnglish($message)
    {
        if ($message == '设备未连接到服务器或者未注册') {
            return "Device Timeout.";
        }

        return "Device Communication Error. Check device settings";
    }

    public function culrmethod($url, $data)
    {






        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes





        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return  $response;
    }
}
