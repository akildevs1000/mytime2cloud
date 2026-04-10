<?php

namespace App\Jobs;

use DateTime;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Logger;

class ProcessCamera2AttendanceLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10; // seconds between retries

    public function __construct(
        private readonly string $deviceSn,
        private readonly int    $companyId,
        private readonly string $timeZone,
        private readonly string $cardNumber,
        private readonly int    $timestampMs,
        private readonly float  $recognitionScore,
        private readonly string $clockStatus,
    ) {}

    public function handle(): void
    {
        // Convert millisecond timestamp → formatted datetime
        $dateTime = new DateTime('@' . intdiv($this->timestampMs, 1000));
        $dateTime->setTimezone(new DateTimeZone($this->timeZone));

        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        $logDate           = $dateTime->format('Y-m-d');

        DB::table('attendance_logs')->insertOrIgnore([[
            'UserID'               => $this->cardNumber,
            'company_id'           => $this->companyId,
            'DeviceID'             => $this->deviceSn,
            'LogTime'              => $formattedDateTime,
            'SerialNumber'         => $this->recognitionScore,
            'log_date_time'        => $formattedDateTime,
            'index_serial_number'  => $this->recognitionScore,
            'log_date'             => $logDate,
            'source_info'          => 'Camera2 Push Event',
            'log_type'             => $this->clockStatus,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]]);

        Log::channel('camera_OX_900')->info('Attendance log inserted successfully', [
            'UserID'   => $this->cardNumber,
            'DeviceID' => $this->deviceSn,
            'LogTime'  => $formattedDateTime,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::channel('camera_OX_900')->error('Job permanently failed for device: ' . $this->deviceSn, [
            'card_number' => $this->cardNumber,
            'error'       => $e->getMessage(),
            'trace'       => $e->getTraceAsString(),
        ]);
    }
}