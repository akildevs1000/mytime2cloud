<?php
namespace App\Console\Commands;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Console\Command;
use WebSocket\Client;

class HeartbeatListener extends Command
{
    protected $signature   = 'heartbeat:listener'; // ✅ Updated here
    protected $description = 'Listen to WebSocket heartbeat events';

    public function handle()
    {
        $baseUrl = $this->getBaseUrl();

        $dotnetEndpoint = "ws://$baseUrl:8080/WebSocket";
        $javaEndpoint   = "$baseUrl:8888";

        // $this->info($dotnetEndpoint);
        // $this->info($javaEndpoint);
        // return;

        $ignoredDeviceList = [];

        $devices = Device::whereNotIn("device_id", $ignoredDeviceList)->pluck('device_id')->toArray();

        $lastSeen = [];

        foreach ($devices as $deviceId) {
            $lastSeen[$deviceId] = null;

            // $this->info("DeviceId: $deviceId, BaseUrl:$baseUrl");

            // $response              = $this->getCURL($deviceId, $baseUrl);
            // if (isset($response["serial_no"])) {

            //     Device::where("device_id", $response["serial_no"])->update(["status_id" => 1, "last_live_datetime" => date("Y-m-d H:i:s")]);
            //     $online_devices_count++;
            // }

        }

        // $this->info(json_encode($lastSeen, JSON_PRETTY_PRINT));

        try {
            $this->info("Connecting to WebSocket at $dotnetEndpoint");

            $client = new Client($dotnetEndpoint, [
                'timeout' => 300,
            ]);

            while (true) {
                $message = $client->receive();
                $decoded = json_decode($message, true);

                if (isset($decoded['Data'])) {
                    $data    = $decoded['Data'];
                    $sn      = $data['SN'] ?? null;
                    $rawTime = $data['KeepAliveTime'] ?? null;

                    if ($sn && in_array($sn, $devices)) {
                        $keepAliveTime = $rawTime
                        ? Carbon::parse($rawTime)->format('Y-m-d H:i:s')
                        : 'N/A';

                        $lastSeen[$sn] = now();

                        Device::where('device_id', $sn)->where('status_id', 2)->update(['status_id' => 1]);

                        $this->info("💓 KeepAliveTime: $keepAliveTime | SN: $sn");
                    }
                } else {
                    $this->warn("Unknown message format: $message");
                }

                // Check only every 30 seconds
                static $lastCheck = null;
                if (! $lastCheck || now()->diffInSeconds($lastCheck) >= 30) {
                    $lastCheck = now();

                    $offlineDevices   = []; // Collect devices to be marked offline
                    $offlineCompanies = []; // Collect unique company IDs for Artisan call

                    foreach ($devices as $deviceId) {
                        $lastTime = $lastSeen[$deviceId];

                        // If no heartbeat received or heartbeat older than 30 seconds
                        if (! $lastTime || now()->diffInSeconds($lastTime) > 30) {

                            $this->warn("❌ No heartbeat from $deviceId in the last 30 seconds!");

                            $found = Device::where("device_id", $deviceId)
                                ->select("status_id", "company_id", "id", "device_id")
                                ->first();

                            if ($found) {
                                $offlineDevices[]                     = $found->id;
                                $offlineCompanies[$found->company_id] = true;
                            }
                        }
                    }

                    if (! empty($offlineDevices)) {
                        Device::whereIn('id', $offlineDevices)->update(['status_id' => 2]);
                        $this->info("🔧 Updated status for " . count($offlineDevices) . " offline device(s).");
                    }
                } 
                usleep(100000); // Small delay
            }
        } catch (\Exception $e) {
            $this->warn("Communication error: trying to reconnect in 5 seconds...");
            sleep(5);
            $this->handle();
        }
    }

    public function getCURL($device_id, $url)
    {
        $sessionId = $this->getActiveSessionId($device_id, $url);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "$url/api/devices/status",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 1,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Cookie: sessionID=' . $sessionId,
                'sxdmToken: ' . $this->getToken(), //get from Device manufacturer
                'sxdmSn:  ' . $device_id,          //get from Device serial number
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return $response = json_decode($response, true);
    }

    public function getActiveSessionId($device_id, $url)
    {

        set_time_limit(120);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url . '/api/auth/login/challenge?username=admin',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 1,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'sxdmToken:' . $this->getToken(),
                'sxdmSn:' . $device_id,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response, true);
        if (isset($response["session_id"])) {
            return $response["session_id"];
        } else {
            return '';
        }
    }

    public function getToken()
    {
        return "7VOarATI4IfbqFWLF38VdWoAbHUYlpAY";
    }
    public function getBaseUrl()
    {
        return gethostbyname(gethostname());
    }
}
