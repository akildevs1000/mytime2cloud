<?php
namespace App\Console\Commands;

use App\Http\Controllers\DeviceCameraModel2Controller;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchMissingLogs extends Command
{
    protected $signature   = 'logs:fetch-missing {device_id?} {date?}';
    protected $description = 'Fetch missing logs from a device for the last 24 hours with offset loop';

    public function handle()
    {
        $deviceId = $this->argument('device_id');
        $dateArg  = $this->argument('date');

        if (! $deviceId) {
            $deviceId = $this->ask('Please enter the Device Id', "M014200892110002790");
        }

        if (! $dateArg) {
            $defaultDate = now()->format('Y-m-d'); // current date as string
            $dateArg     = $this->ask('Please enter the date (YYYY-MM-DD)', $defaultDate);
        }
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

        $insertedCount = 0; // Count of new inserted logs
        $ignoredCount  = 0; // Count of logs already existing

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
            info("Fetching logs: Offset = {$offset}");

            $responseArray = $deviceSession->getHistory($deviceId, $json);
            

            if (empty($responseArray['data'])) {
                $this->info("No more logs found.");
                info("No more logs found.");
                break;
            }

            // Extract composite keys for all fetched logs
            $keys = [];
            foreach ($responseArray['data'] as $record) {
                $timestamp = Carbon::parse($record["timestamp"])->format('Y-m-d H:i:s');
                $keys[]    = [
                    'UserID'   => $record['person_code'],
                    'DeviceID' => $deviceId,
                    'LogTime'  => $timestamp,
                ];
            }

            // Get existing logs in bulk
            $existingLogs = AttendanceLog::where('DeviceID', $deviceId)
                ->whereIn('LogTime', array_column($keys, 'LogTime'))
                ->whereIn('UserID', array_column($keys, 'UserID'))
                ->get(['UserID', 'DeviceID', 'LogTime'])
                ->map(function ($item) {
                    return $item->UserID . '|' . $item->DeviceID . '|' . $item->LogTime;
                })->toArray();

            // Now insert only non-existing logs
            foreach ($responseArray['data'] as $record) {
                $timestamp = Carbon::parse($record["timestamp"])->format('Y-m-d H:i:s');
                $key       = $record['person_code'] . '|' . $deviceId . '|' . $timestamp;

                if (in_array($key, $existingLogs)) {
                    $ignoredCount++;
                    $this->info("Ignored (exists): User {$record['person_code']} at {$timestamp}");
                    info("Ignored (exists): User {$record['person_code']} at {$timestamp}");
                    continue;
                }

                $clock_status = $clockStatusOption === 'Clock On' ? 'In' :
                ($clockStatusOption === 'Clock Off' ? 'Out' : $clockStatusOption);

                $data = [
                    "UserID"        => $record['person_code'],
                    "DeviceID"      => $deviceId,
                    "LogTime"       => $timestamp,
                    "SerialNumber"  => null,
                    "status"        => "Allowed",
                    "mode"          => $record['pass_mode'] ?? "---",
                    "log_type"      => $clock_status,
                    "company_id"    => $company_id,
                    "source_info"   => $source_info,
                    "log_date_time" => $timestamp,
                    "log_date"      => Carbon::parse($record["timestamp"])->format('Y-m-d'),
                ];

                AttendanceLog::create($data);
                $insertedCount++;
                $this->info("Inserted: User {$record['person_code']} at {$timestamp}");
                info("Inserted: User {$record['person_code']} at {$timestamp}");

                // Also add to existingLogs to avoid duplicates within this batch
                $existingLogs[] = $key;
            }

            $offset += $limit;
        }

        $this->info("✅ Completed fetching logs for {$dateArg} for device {$deviceId}.");
        $this->info("Total inserted logs: {$insertedCount}");
        $this->info("Total ignored logs: {$ignoredCount}");

        info("✅ Completed fetching logs for {$dateArg} for device {$deviceId}.");
        info("Total inserted logs: {$insertedCount}");
        info("Total ignored logs: {$ignoredCount}");
    }
}
