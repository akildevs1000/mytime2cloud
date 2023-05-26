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

    protected $endpoint = "localhost:5000";

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

        $url = "{$this->endpoint}/{$id}/WriteTimeGroup";
        return $this->processSDKRequest($url, $data);
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
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ],
                    [
                        "begin" => "00:00",
                        "end" => "00:00"
                    ]
                ]
            ];
        }
        return $arr;
    }

    public function PersonAddRange(Request $request)
    {
        $url = "{$this->endpoint}/Person/AddRange";

        return $this->processSDKRequest($url, $request->all());
    }

    public function processSDKRequest($url, $data)
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
