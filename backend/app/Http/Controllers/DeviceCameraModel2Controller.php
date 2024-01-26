<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Device;
use App\Models\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class DeviceCameraModel2Controller extends Controller
{
    public  $camera_sdk_url = '';

    public function __construct($camera_sdk_url)
    {
        $this->camera_sdk_url = $camera_sdk_url;
    }


    public function pushUserToCameraDevice($name,  $system_user_id, $base65Image)
    {




        try {
            //code...

            $sessionIdArr = $this->getActiveSessionId();

            $sessionIdArr = json_decode($sessionIdArr, true);
            if (isset($sessionIdArr['session_id'])) {


                $sessionId = $sessionIdArr['session_id'];
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $this->camera_sdk_url . '/api/persons/item',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{
                    "recognition_type": "staff",
                    "is_admin": false,
                    "person_name": "' . $name . '",
                    "id": ' . $system_user_id . ',
                    "password": "123456",
                    "card_number": ' . $system_user_id . ',
                    "person_code":' . $system_user_id . ',
                    "visit_begin_time": "' . date('Y-m-d 00:00:00') . '",
                    "visit_end_time": "' .  date('Y-m-d 00:00:00', strtotime(date("Y-m-d 23:00:00") . " + 365 day")) . '",
                    "phone_num":"18686868686",
                    "group_list": [
                      1
                    ],
                    "feature_version":"8903",
                    "face_list": [
                      {
                        "idx": 3,
                        "data": "' . $base65Image . '"
                      }
                    ]
                  }',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Cookie: sessionID=' . $sessionId,
                        'sxdmToken: 7VOarATI4IfbqFWLF38VdWoAbHUYlpAY', //get from Device manufacturer
                        'sxdmSn: M014200892105001731' //get from Device serial number
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                //return $response;

                $this->devLog("camera-megeye-info", "Successfully Added ID:" . $system_user_id . ", Name :  " . $name);
            } else {

                $this->devLog("camera-megeye-error", "Unable to Generate session");
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->devLog("camera-megeye-error", "Exception - Unable to Generate session" . $th);
        }
    }

    public function getActiveSessionId()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->camera_sdk_url . '/api/auth/login/challenge?username=admin',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'sxdmToken: 7VOarATI4IfbqFWLF38VdWoAbHUYlpAY', //get from Device manufacturer
                'sxdmSn: M014200892105001731' //get from Device serial number
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return  $response;
    }
}
