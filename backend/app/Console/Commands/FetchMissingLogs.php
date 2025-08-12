<?php
namespace App\Console\Commands;

use App\Http\Controllers\DeviceCameraModel2Controller;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchMissingLogs extends Command
{
    protected $signature   = 'logs:fetch-missing {device_id} {date?}';
    protected $description = 'Fetch missing logs from a device for the last 24 hours with offset loop';

    public function handle()
    {
        $deviceId = $this->argument('device_id');
        $dateArg  = $this->argument('date');

                                // Defaults
        $company_id        = 1; // Replace with actual
        $source_info       = 'Auto';
        $clockStatusOption = 'Clock On';

        // Example device (replace with DB lookup)
        $device = (object) [
            'camera_sdk_url' => 'http://192.168.50.101:8888',
            'device_id'      => $deviceId,
        ];

        $deviceSession = new DeviceCameraModel2Controller($device->camera_sdk_url, $device->device_id);

        $startDate = $dateArg ? Carbon::parse($dateArg)->startOfDay() : Carbon::now()->subHours(24);
        $endDate   = $dateArg ? Carbon::parse($dateArg)->endOfDay() : Carbon::now();

        $beginTime = $startDate->toIso8601String();
        $endTime   = $endDate->toIso8601String();

        $offset = 0;
        $limit  = 1;

        while (true) {
            $json = json_encode([
                "limit"            => $limit,
                "offset"           => $offset,
                "sort"             => "asc",
                "begin_time"       => $beginTime,
                "end_time"         => $endTime,
                "query_string"     => "",
                "query_person_idx" => "",
                "query_nopass"     => false,
            ]);

            $this->info("Fetching logs: Offset = {$offset}");

            $responseArray = $deviceSession->getHistory($deviceId, $json);

            // Reset session if needed
            if (isset($responseArray['status'])) {
                $this->warn("Session reset for device {$deviceId}");
                // (new SDKController())->clearSessionData($deviceId);
                $deviceSession = new DeviceCameraModel2Controller($device->camera_sdk_url, $device->device_id);
                $responseArray = $deviceSession->getHistory($deviceId, $json);
            }

            // Stop if no data
            if (empty($responseArray['data'])) {
                $this->info("No more logs found.");
                break;
            }

            foreach ($responseArray['data'] as $record) {

                $timestamp = $record["timestamp"];

                $logtime = Carbon::parse($timestamp)->format('Y-m-d H:i:s');

                $clock_status = $clockStatusOption === 'Clock On' ? 'In' :
                ($clockStatusOption === 'Clock Off' ? 'Out' : $clockStatusOption);

                $data = [
                    "UserID"        => $record['person_code'],
                    "DeviceID"      => $deviceId,
                    "LogTime"       => $logtime,
                    "SerialNumber"  => null,
                    "status"        => "Allowed",
                    "mode"          => $record['pass_mode'] ?? "---",
                    "log_type"      => $clock_status,
                    "company_id"    => $company_id,
                    "source_info"   => $source_info,
                    "log_date_time" => $logtime,
                    "log_date"      => Carbon::parse($timestamp)->format('Y-m-d'),
                ];

                $this->info($record['person_code'] . "," . $timestamp);
                info($record['person_code'] . "," . $timestamp);

                $exists = AttendanceLog::where('UserID', $record['person_code'])
                    ->where('DeviceID', $deviceId)
                    ->where('LogTime', $logtime)
                    ->exists();

                if (! $exists) {
                    AttendanceLog::create($data);
                    $this->info("Inserted: User {$record['person_code']} at {$logtime}");
                }
            }

            $offset += $limit;
        }

        $this->info("✅ Completed fetching logs for last 24 hours for device {$deviceId}.");
    }
}
