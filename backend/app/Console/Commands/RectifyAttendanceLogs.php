<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RectifyAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan attendance:rectify {date?}
     */
    protected $signature = 'attendance:rectify {date? : The date to start fixing from (YYYY-MM-DD)}';

    /**
     * The console command description.
     */
    protected $description = 'Syncs attendance log_types with device function settings (Auto, In, Out, Option) - Only updates NULL log_types';

    public function handle()
    {
        // 1. Determine Start Date (Default to today)
        $dateArgument = $this->argument('date');
        $startDate = $dateArgument ? Carbon::parse($dateArgument)->toDateString() : Carbon::today()->toDateString();

        $this->warn("!!! Rectifying NULL log_types from: {$startDate} onwards !!!");

        // 2. Fetch Device Mapping (id -> function)
        // Exclude mobile to focus on hardware devices
        $deviceFunctionMap = Device::excludeMobile()
            ->get(['device_id', 'function'])
            ->pluck('function', 'device_id')
            ->toArray();

        $correctedCount = 0;
        $processedCount = 0;
        $skippedCount = 0;

        // 1. Convert your input into a Carbon instance
        $currentDate = Carbon::parse($startDate);

        // 2. Calculate the "Yesterday" relative to that date
        $relativeYesterday = $currentDate->copy()->subDay()->toDateString();
        $relativeToday = $currentDate->toDateString();

        // 3. Query logs in the specified range - ONLY NULL log_type
        $query = DB::table('attendance_logs')
            ->whereDate('log_date', '>=', $relativeYesterday)
            ->whereDate('log_date', '<=', $relativeToday)
            ->whereNull('log_type'); // ✅ Only process NULL values

        $totalFound = $query->count();
        $this->info("Found {$totalFound} logs with NULL log_type to fix.");

        if ($totalFound === 0) {
            $this->info("Nothing to process. All logs have log_type set.");
            return;
        }

        // 4. Process in chunks for memory efficiency
        $query->orderBy('id')->chunk(500, function ($logs) use ($deviceFunctionMap, &$correctedCount, &$processedCount) {

            // Group IDs by their expected log_type
            $grouped = [
                'Auto'   => [],
                'In'     => [],
                'Out'    => [],
                'Option' => [],
            ];

            foreach ($logs as $log) {
                $deviceId       = trim($log->DeviceID);

                $deviceFunction = $deviceFunctionMap[$deviceId] ?? 'auto'; // ✅ default to auto

                $expectedType = match ($deviceFunction) {
                    'auto'   => 'Auto',
                    'In'     => 'In',
                    'Out'    => 'Out',
                    'option' => 'Option',
                    default  => 'Auto', // ✅ default to Auto instead of null
                };

                $grouped[$expectedType][] = $log->id;
                $processedCount++;
            }

            // ✅ 4 queries max per chunk instead of 500
            foreach ($grouped as $type => $ids) {
                if (!empty($ids)) {
                    DB::table('attendance_logs')
                        ->whereIn('id', $ids)
                        ->update(['log_type' => $type]);

                    $correctedCount += count($ids);
                }
            }

            $this->output->write(".");
        });

        $this->newLine();
        $this->table(
            ['Total Processed', 'Total Corrected', 'Start Date'],
            [[$processedCount, $correctedCount, $startDate]]
        );

        $this->info("✓ Successfully rectified NULL attendance logs.");
    }
}
