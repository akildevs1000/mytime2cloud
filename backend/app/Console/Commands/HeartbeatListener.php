<?php
namespace App\Console\Commands;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Console\Command;
use WebSocket\Client;
use WebSocket\TimeoutException;

class HeartbeatListener extends Command
{
    protected $signature   = 'heartbeat:listener';
    protected $description = 'Listen to WebSocket heartbeat events from devices';

    public function handle()
    {
        $baseUrl        = $this->getBaseUrl();
        $dotnetEndpoint = "ws://$baseUrl:8080/WebSocket";
        $javaEndpoint   = "$baseUrl:8888";

        $ignoredDeviceList = [];

        $ox900  = Device::whereNotIn("device_id", $ignoredDeviceList)->where("model_number", "OX-900")->pluck('device_id')->toArray();
        $others = Device::whereNotIn("device_id", $ignoredDeviceList)->whereNot("model_number", "OX-900")->pluck('device_id')->toArray();

        $this->processDotNetDevices($others, $dotnetEndpoint);

        $this->processJavaDevices($ox900, $javaEndpoint);
        while (true) {
            $this->processJavaDevices($ox900, $javaEndpoint);
            sleep(30); // wait for 30 seconds
        }
    }

    public function processDotNetDevices($devices, $dotnetEndpoint)
    {
        $lastSeen = [];

        foreach ($devices as $deviceId) {
            $lastSeen[$deviceId] = null;
        }
        try {
            $this->info("Connecting to WebSocket at $dotnetEndpoint");

            $client = new Client($dotnetEndpoint, [
                'timeout' => 10, // ⏳ Short timeout to prevent blocking forever
            ]);

            static $lastCheck = null;

            while (true) {
                try {
                    $message = $client->receive(); // Will throw TimeoutException if nothing is received
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

                            Device::where('device_id', $sn)
                                ->where('status_id', 2)
                                ->update(['status_id' => 1]);

                            $this->info("💓 KeepAliveTime: $keepAliveTime | SN: $sn");
                        }
                    } else {
                        $this->warn("Unknown message format: $message");
                    }
                } catch (TimeoutException $e) {
                    // No message received within timeout — continue to next loop iteration
                } catch (\Exception $e) {
                    // Show which devices have not sent heartbeat
                    $offlineDevices = [];
                    foreach ($lastSeen as $deviceId => $lastTime) {
                        if (! $lastTime || now()->diffInSeconds($lastTime) > 30) {
                            $offlineDevices[] = $deviceId;
                            $this->warn("❌ No heartbeat from: " . $deviceId);

                        }
                    }
                    sleep(5);        // wait before trying to reconnect
                    $this->handle(); // retry connection
                    return;
                }

                // Perform device check every 30 seconds
                if (! $lastCheck || now()->diffInSeconds($lastCheck) >= 30) {
                    $lastCheck = now();

                    $offlineDevices   = [];
                    $offlineCompanies = [];

                    foreach ($devices as $deviceId) {
                        $lastTime = $lastSeen[$deviceId];

                        if (! $lastTime) {
                            $this->warn("⏳ Device $deviceId has not connected yet.");
                        } elseif (now()->diffInSeconds($lastTime) > 30) {
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

                usleep(100000); // 0.1 second delay to reduce CPU usage
            }
        } catch (\Exception $e) {
            $this->error("Failed to connect to WebSocket: " . $e->getMessage());
            sleep(5);
            $this->handle(); // Retry on initial connection failure
        }
    }

    public function processJavaDevices($devices, $javaEndpoint)
    {
        $onlineDevices  = [];
        $offlineDevices = [];

        $keepAliveTime = date("Y-m-d H:i:s");

        foreach ($devices as $device_id) {
            $response = $this->getCURL($device_id, $javaEndpoint);
            if (isset($response["serial_no"])) {
                $onlineDevices[] = $device_id;
                $this->info("💓 KeepAliveTime: $keepAliveTime | SN: $device_id");
            } else {
                $offlineDevices[] = $device_id;
                $this->warn("❌ No heartbeat from: " . $device_id);
            }
        }

        $onlineUpdatedDevices  = Device::whereIn("device_id", $onlineDevices)->update(["status_id" => 1, "last_live_datetime" => $keepAliveTime]);
        $offlineUpdatedDevices = Device::whereIn("device_id", $offlineDevices)->update(["status_id" => 2]);

        $this->info("$onlineUpdatedDevices devices online, $offlineUpdatedDevices devices offline");
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
                'sxdmToken: ' . $this->getToken(),
                'sxdmSn: ' . $device_id,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
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
        return $response["session_id"] ?? '';
    }

    public function getToken()
    {
        return "7VOarATI4IfbqFWLF38VdWoAbHUYlpAY";
    }

    public function getBaseUrl()
    {
        return gethostbyname(gethostname());
    }

    public function showJson($arr)
    {
        return json_encode($arr, JSON_PRETTY_PRINT);
    }
}
