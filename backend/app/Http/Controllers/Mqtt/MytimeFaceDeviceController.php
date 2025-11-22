<?php

namespace App\Http\Controllers\Mqtt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;



class MytimeFaceDeviceController extends Controller
{
    /**
     * Base URL of the Node.js MQTT gateway, e.g. http://127.0.0.1:4000
     *
     * In .env:
     *   MQTT_GATEWAY_URL=http://127.0.0.1:4000
     */
    protected string $gatewayBase;

    public function __construct()
    {
        // You can also put this in config/services.php if you prefer.
        $this->gatewayBase = rtrim(
            config('services.mqtt_gateway.url', env('MQTT_GATEWAY_URL', 'http://127.0.0.1:4000')),
            '/'
        );
    }

    /* ------------------------------------------------------------------
     * Helper: proxy to MQTT gateway
     * ------------------------------------------------------------------ */

    protected function gatewayRequest(string $method, string $path, array $body = [], array $query = [])
    {
        $url = $this->gatewayBase . '/' . ltrim($path, '/');

        $client = Http::acceptJson();

        if (!empty($query)) {
            $client = $client->query($query);
        }

        switch (strtoupper($method)) {
            case 'GET':
                $response = $client->get($url);
                break;
            case 'POST':
                $response = $client->post($url, $body);
                break;
            case 'DELETE':
                // For DELETE with body, use withBody
                if (!empty($body)) {
                    $response = $client
                        ->withBody(json_encode($body), 'application/json')
                        ->delete($url);
                } else {
                    $response = $client->delete($url);
                }
                break;
            default:
                return response()->json([
                    'error' => "Unsupported HTTP method: {$method}",
                ], 500);
        }

        return response()->json($response->json(), $response->status());
    }

    /* ------------------------------------------------------------------
     * Door Control
     * ------------------------------------------------------------------ */

    // POST /api/face-device/{deviceId}/open-door
    public function openDoor(string $deviceId)
    {
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/open-door");
    }

    // OPTIONAL: POST /api/face-device/{deviceId}/close-door
    // Protocol usually only has OpenDoor and IOStayTime auto-close.
    public function closeDoor(string $deviceId)
    {
        // If you implement custom close behaviour in Node, proxy it here:
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/close-door");
    }

    // GET /api/face-device/{deviceId}/door-config
    public function getDoorConfig(string $deviceId)
    {
        return $this->gatewayRequest('GET', "api/device/{$deviceId}/door-config");
    }

    // POST /api/face-device/{deviceId}/door-config
    public function setDoorConfig(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "VerifyMode": 1,
        //   "IOStayTime": 3000,
        //   ...
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/door-config", $request->all());
    }

    /* ------------------------------------------------------------------
     * Time / Timezone
     * ------------------------------------------------------------------ */

    // GET /api/face-device/{deviceId}/time
    public function getTime(string $deviceId)
    {
        return $this->gatewayRequest('GET', "api/device/{$deviceId}/time");
    }

    // POST /api/face-device/{deviceId}/time
    public function setTime(Request $request, string $deviceId)
    {
        // Expect body: { "sysTime": "2025-11-22T18:30:00" }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/time", $request->all());
    }

    /* ------------------------------------------------------------------
     * Personnel
     * ------------------------------------------------------------------ */

    // POST /api/face-device/{deviceId}/person
    // Add / update one person
    public function savePerson(Request $request, string $deviceId)
    {
        // Body should match device protocol (EditPerson: customId, name, pic, etc.)
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/person", $request->all());
    }

    // DELETE /api/face-device/{deviceId}/person/{customId}
    public function deletePerson(string $deviceId, string $customId)
    {
        return $this->gatewayRequest('DELETE', "api/device/{$deviceId}/person/{$customId}");
    }

    // POST /api/face-device/{deviceId}/persons/batch
    public function batchSavePersons(Request $request, string $deviceId)
    {
        // Expect: { "persons": [ {...}, {...} ] }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/persons/batch", $request->all());
    }

    // POST /api/face-device/{deviceId}/persons/batch-delete
    public function batchDeletePersons(Request $request, string $deviceId)
    {
        // Expect: { "customIds": ["E0001", "E0002", ...] }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/persons/batch-delete", $request->all());
    }

    // POST /api/face-device/{deviceId}/persons/delete-all
    public function deleteAllPersons(string $deviceId)
    {
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/persons/delete-all");
    }

    // GET /api/face-device/{deviceId}/person/{customId}?picture=1
    public function getPerson(Request $request, string $deviceId, string $customId)
    {
        $query = [];
        if ($request->boolean('picture')) {
            $query['picture'] = 1;
        }

        return $this->gatewayRequest('GET', "api/device/{$deviceId}/person/{$customId}", [], $query);
    }

    // POST /api/face-device/{deviceId}/persons/search
    public function searchPersonList(Request $request, string $deviceId)
    {
        // Example body in Postman:
        // {
        //   "BeginTime": "2025-11-01 00:00:00",
        //   "EndTime": "2025-11-30 23:59:59",
        //   "personType": 0,
        //   "BeginNO": 0,
        //   "RequestCount": 50
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/persons/search", $request->all());
    }

    /* ------------------------------------------------------------------
     * Snapshot & QR
     * ------------------------------------------------------------------ */

    // POST /api/face-device/{deviceId}/snapshot
    public function snapshot(Request $request, string $deviceId)
    {
        // Example body: { "imgType": 2, "imgQuality": 55 }
        $payload = [
            'imgType'    => $request->input('imgType', 2),
            'imgQuality' => $request->input('imgQuality', 55),
        ];

        return $this->gatewayRequest('POST', "api/device/{$deviceId}/snapshot", $payload);
    }

    // POST /api/face-device/{deviceId}/qrcode
    public function showQRCode(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "ImageType": 1,
        //   "AbsX": 0,
        //   "AbsY": 0,
        //   "ImageW": 300,
        //   "ImageH": 300,
        //   "QRCodeData": "TOKEN-123456",
        //   "ShowStatus": 1
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/qrcode", $request->all());
    }

    /* ------------------------------------------------------------------
     * Advertisements
     * ------------------------------------------------------------------ */

    // POST /api/face-device/{deviceId}/ad
    public function saveAd(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "adslot": 0,
        //   "path": "https://your-domain.com/ad-01.jpg",
        //   "polltime": 10,
        //   "adid": ""
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/ad", $request->all());
    }

    // DELETE /api/face-device/{deviceId}/ad
    public function deleteAd(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "adslot": 0,
        //   "adid": ""
        // }
        return $this->gatewayRequest('DELETE', "api/device/{$deviceId}/ad", $request->all());
    }

    /* ------------------------------------------------------------------
     * Access Strategy
     * ------------------------------------------------------------------ */

    // POST /api/face-device/{deviceId}/strategy
    public function saveStrategy(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "strategyID": 1,
        //   "strategyName": "Office Hours",
        //   ...
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/strategy", $request->all());
    }

    // DELETE /api/face-device/{deviceId}/strategy
    public function deleteStrategies(Request $request, string $deviceId)
    {
        // Example body: { "strategyIds": [1, 2] }
        return $this->gatewayRequest('DELETE', "api/device/{$deviceId}/strategy", $request->all());
    }

    // POST /api/face-device/{deviceId}/strategy/bind
    public function bindStrategies(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "personsInfo": [
        //     { "customId": "E0001", "strategyID": [1] },
        //     { "customId": "E0002", "strategyID": [1, 2] }
        //   ]
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/strategy/bind", $request->all());
    }

    // POST /api/face-device/{deviceId}/strategy/unbind
    public function unbindStrategies(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "personsInfo": [
        //     { "customId": "E0001", "strategyID": [1] }
        //   ]
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/strategy/unbind", $request->all());
    }

    /* ------------------------------------------------------------------
     * Temperature Config (TPT)
     * ------------------------------------------------------------------ */

    // GET /api/face-device/{deviceId}/temperature-config
    public function getTemperatureConfig(string $deviceId)
    {
        return $this->gatewayRequest('GET', "api/device/{$deviceId}/temperature-config");
    }

    // POST /api/face-device/{deviceId}/temperature-config
    public function setTemperatureConfig(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "TemperatureMode": 0,
        //   "ShowAbnormalTemp": 1,
        //   "TemperatureCheck": 0.0,
        //   "HeightTemp": 37.3,
        //   "LowTemp": 34.0
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/temperature-config", $request->all());
    }

    /* ------------------------------------------------------------------
     * GPS
     * ------------------------------------------------------------------ */

    // GET /api/face-device/{deviceId}/gps
    public function getGps(string $deviceId)
    {
        return $this->gatewayRequest('GET', "api/device/{$deviceId}/gps");
    }

    // POST /api/face-device/{deviceId}/gps
    public function setGps(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "GpsType": 1,
        //   "Longitude": 55.2708,
        //   "Latitude": 25.2048,
        //   "UTCOffset": "+04:00"
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/gps", $request->all());
    }

    /* ------------------------------------------------------------------
     * Sound & UI
     * ------------------------------------------------------------------ */

    // GET /api/face-device/{deviceId}/sound-config
    public function getSoundConfig(string $deviceId)
    {
        return $this->gatewayRequest('GET', "api/device/{$deviceId}/sound-config");
    }

    // POST /api/face-device/{deviceId}/sound-config
    public function setSoundConfig(Request $request, string $deviceId)
    {
        // Example body:
        // {
        //   "Volume": 80,
        //   "WelcomeVoice": 1,
        //   "LcdBLDisable": 1,
        //   "LcdBLDisableAfterSec": 60
        // }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/sound-config", $request->all());
    }

    /* ------------------------------------------------------------------
     * System Control
     * ------------------------------------------------------------------ */

    // POST /api/face-device/{deviceId}/reboot
    public function reboot(string $deviceId)
    {
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/reboot");
    }

    // POST /api/face-device/{deviceId}/factory-reset
    public function factoryReset(Request $request, string $deviceId)
    {
        // Example body: { "keepNetwork": 1 }
        return $this->gatewayRequest('POST', "api/device/{$deviceId}/factory-reset", $request->all());
    }
}
