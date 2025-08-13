<?php
namespace App\Jobs;

use App\Http\Controllers\DeviceCameraModel2Controller;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchMissingLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deviceId;
    protected $dateArg;

    public function __construct($deviceId, $dateArg)
    {
        $this->deviceId = $deviceId;
        $this->dateArg  = $dateArg;
    }

    public function handle()
    {
        $company_id        = 1;
        $source_info       = 'Auto';
        $clockStatusOption = 'Clock On';

        $device = (object) [
            'camera_sdk_url' => 'http://192.168.50.101:8888',
            'device_id'      => $this->deviceId,
        ];

        $deviceSession = new DeviceCameraModel2Controller(
            $device->camera_sdk_url,
            $device->device_id
        );

        $startDate = $this->dateArg
        ? Carbon::parse($this->dateArg)->startOfDay()
        : Carbon::now()->subHours(24);

        $endDate = $this->dateArg
        ? Carbon::parse($this->dateArg)->endOfDay()
        : Carbon::now();

        $beginTime = $startDate->toIso8601String();
        $endTime   = $endDate->toIso8601String();

        $offset = 0;
        $limit  = 1;

        $insertedCount = 0;
        $ignoredCount  = 0;

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

            echo "Fetching logs: Offset = {$offset}\n";
            info("Fetching logs: Offset = {$offset}");

            $responseArray = $deviceSession->getHistory($this->deviceId, $json);

            if (empty($responseArray['data'])) {
                echo "No more logs found\n";
                info("No more logs found.");
                break;
            }

            $keys = [];
            foreach ($responseArray['data'] as $record) {
                $timestamp = Carbon::parse($record["timestamp"])
                    ->format('Y-m-d H:i:s');
                $keys[] = [
                    'UserID'   => $record['person_code'],
                    'DeviceID' => $this->deviceId,
                    'LogTime'  => $timestamp,
                ];
            }

            $existingLogs = AttendanceLog::where('DeviceID', $this->deviceId)
                ->whereIn('LogTime', array_column($keys, 'LogTime'))
                ->whereIn('UserID', array_column($keys, 'UserID'))
                ->get(['UserID', 'DeviceID', 'LogTime'])
                ->map(fn($item) => $item->UserID . '|' . $item->DeviceID . '|' . $item->LogTime)
                ->toArray();

            foreach ($responseArray['data'] as $record) {
                $timestamp = Carbon::parse($record["timestamp"])
                    ->format('Y-m-d H:i:s');
                $key = $record['person_code'] . '|' . $this->deviceId . '|' . $timestamp;

                if (in_array($key, $existingLogs)) {
                    $ignoredCount++;
                    echo "Ignored (exists): User {$record['person_code']} at {$timestamp}\n";
                    info("Ignored (exists): User {$record['person_code']} at {$timestamp}");
                    continue;
                }

                $clock_status = $clockStatusOption === 'Clock On' ? 'In' :
                ($clockStatusOption === 'Clock Off' ? 'Out' : $clockStatusOption);

                AttendanceLog::create([
                    "UserID"        => $record['person_code'],
                    "DeviceID"      => $this->deviceId,
                    "LogTime"       => $timestamp,
                    "SerialNumber"  => null,
                    "status"        => "Allowed",
                    "mode"          => $record['pass_mode'] ?? "---",
                    "log_type"      => $clock_status,
                    "company_id"    => $company_id,
                    "source_info"   => $source_info,
                    "log_date_time" => $timestamp,
                    "log_date"      => Carbon::parse($record["timestamp"])->format('Y-m-d'),
                ]);

                $insertedCount++;
                echo "Inserted: User {$record['person_code']} at {$timestamp}\n";
                info("Inserted: User {$record['person_code']} at {$timestamp}");
                $existingLogs[] = $key;
            }

            $offset += $limit;
        }

        info("✅ Completed fetching logs for {$this->dateArg} for device {$this->deviceId}.");
        info("Total inserted logs: {$insertedCount}");
        info("Total ignored logs: {$ignoredCount}");

        echo "✅ Completed fetching logs for {$this->dateArg} for device {$this->deviceId}.\n";
        echo "Total inserted logs: {$insertedCount}\n";
        echo "Total ignored logs: {$ignoredCount}\n";

    }
}
