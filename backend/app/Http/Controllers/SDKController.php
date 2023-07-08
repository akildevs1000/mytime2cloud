<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Timezone;
use App\Models\TimezoneDefaultJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SDKController extends Controller
{
    // $url = "https://sdk.ideahrms.com";
//http://139.59.69.241:5000
//http://192.168.002.210:5000  _device 9000
//localhost:5000
//https://stagingsdk.ideahrms.com

    protected $endpoint = "https://stagingsdk.ideahrms.com";

    public function processTimeGroup(Request $request, $id)
    {
        $timezones = Timezone::where('company_id', $request->company_id)
            ->select('timezone_id', 'json')
            ->get();

        $timezoneIDArray = $timezones->pluck('timezone_id');
        $jsonArray = $timezones->pluck('json')->toArray();

        $TimezoneDefaultJson = TimezoneDefaultJson::query();
        $TimezoneDefaultJson->whereNotIn("index", $timezoneIDArray);
        $defaultArray = $TimezoneDefaultJson->get(["index", "dayTimeList"])->toArray();

        $data = array_merge($defaultArray, $jsonArray);
        //ksort($data);

        asort($data);

        $url = "{$this->endpoint}/{$id}/WriteTimeGroup";
        $sdkResponse = $this->processSDKRequest($url, $data);

        return $sdkResponse;
    }

    public function renderEmptyTimeFrame()
    {
        $arr = [];

        for ($i = 0; $i <= 6; $i++) {
            $arr[] = [
                "dayWeek" => $i,
                "timeSegmentList" => [
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00",
                    ],
                ],
            ];
        }
        return $arr;
    }

    public function PersonAddRange(Request $request)
    {
        $url = "{$this->endpoint}/Person/AddRange";

        return $this->processSDKRequest($url, $request->all());
    }
    public function PersonAddRangeWithData($data)
    {
        $url = "{$this->endpoint}/Person/AddRange";

        return $this->processSDKRequest($url, $data);
    }
    public function processSDKRequest($url, $data)
    {

        $personList = $data['personList'];
        $snList = $data['snList'];
        $returnFinalMessage = [];
        $devicePersonsArray = [];
        foreach ($snList as $key => $device) {

            $returnMsg = '';

            foreach ($personList as $keyPerson => $valuePerson) {
                # code...
                $newArray = [
                    "personList" => [$valuePerson],
                    "snList" => [$device],
                ];
                // try {
                $returnMsg = Http::withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($url, $newArray);
                if ($returnMsg && $returnMsg['data']) {
                    $returnFinalMessage[] = $returnMsg['data'][0];
                    //$devicePersonsArray[] = [$device => $returnMsg['data'][0]['userList']];
                } else {
                    $returnMsg = ["sn" => $device, "state" => false, "message" => "The device was not found - Network issue", "userList" => null];
                    $returnFinalMessage[] = $returnMsg;
                }

                // } catch (\Exception $e) {
                //     $returnMsg = [
                //         "status" => 102,
                //         "message" => $e->getMessage(),
                //     ];

                //     $returnMsg = ["sn" => $device, "state" => false, "message" => "The device was not found - Network issue", "userList" => null];

                //     $returnFinalMessage[] = $returnMsg;

                // }
            }
        }
        $returnFinalMessage = $this->mergeDevicePersonslist($returnFinalMessage);
        $returnContent = ["data" => $returnFinalMessage, "status" => 200,
            "message" => "",
            "transactionType" => 0];
        return $returnContent;

    }
    public function mergeDevicePersonslist($data)
    {
        $mergedData = [];

        foreach ($data as $item) {
            $sn = $item['sn'];
            $userList = $item['userList'];

            if (array_key_exists($sn, $mergedData)) {
                if (!empty($userList)) {
                    $mergedData[$sn] = array_merge($mergedData[$sn], $userList);
                }
            } else {
                $mergedData[$sn] = $item;
            }
        }

        $mergedList = [];

        foreach ($mergedData as $sn => $userList) {
            $mergedList[] = [
                "sn" => $sn,
                "state" => $userList['state'],
                "message" => $userList['message'],
                "userList" => $userList['userList'],
            ];
        }
        return $mergedList;
    }
    public function processSDKRequestBulk($url, $data)
    {

        try {
            return Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $data);
        } catch (\Exception $e) {
            return [
                "status" => 102,
                "message" => $e->getMessage(),
            ];
            // You can log the error or perform any other necessary actions here
        }
    }
    public function getDevicesCountForTimezone(Request $request)
    {
        return Device::where('company_id', $request->company_id)->pluck('device_id');
    }
}
