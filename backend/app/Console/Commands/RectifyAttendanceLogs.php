<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RectifyAttendanceLogs extends Command
{
    /**
     * Added {date?} argument. 
     * Usage: php artisan attendance:rectify 2024-03-14
     */
    protected $signature = 'attendance:rectify {date? : The date to start fixing from (YYYY-MM-DD)}';

    protected $description = 'Re-evaluates and fixes incorrect log_types starting from a specific date';

    public function handle()
    {
        // 1. Determine the start date (Default to Today if no argument is passed)
        $dateArgument = $this->argument('date');
        $startDate = $dateArgument ? Carbon::parse($dateArgument)->toDateString() : Carbon::today()->toDateString();

        $this->warn("!!! Rectifying logs from: {$startDate} onwards !!!");

        // 2. Fetch Device Mapping
        $deviceFunctionMap = Device::excludeMobile()
            ->get(['device_id', 'function'])
            ->pluck('function', 'device_id')
            ->toArray();

        $inTypeValues = ['in', 'auto', 'entry'];
        $correctedCount = 0;
        $processedCount = 0;

        // 3. Query logs from the chosen date onwards
        $query = DB::table('attendance_logs')
            ->whereDate('log_date', '>=', $startDate);

        $total = $query->count();
        $this->info("Found {$total} total logs to check in this range.");

        $query->orderBy('id')->chunk(500, function ($logs) use ($deviceFunctionMap, $inTypeValues, &$correctedCount, &$processedCount) {
            // ... inside the loop ...
            foreach ($logs as $log) {
                $deviceId = trim($log->DeviceID); // Added trim

                $deviceFunction = isset($deviceFunctionMap[$deviceId])
                    ? strtolower(trim($deviceFunctionMap[$deviceId]))
                    : '';

                if (in_array($deviceFunction, $inTypeValues)) {
                    $expectedType = 'In';
                } elseif ($deviceFunction === 'out') {
                    $expectedType = 'Out';
                } else {
                    $expectedType = (str_contains(strtolower($deviceId), 'in')) ? 'In' : 'Out';
                }

                // DEBUG: Let's see why it's not correcting
                if ($processedCount < 5) {
                    $this->info("Log ID: {$log->id} | Device: {$deviceId} | Func: {$deviceFunction} | Current: '{$log->log_type}' | Expected: '{$expectedType}'");
                }

                if (trim($log->log_type) !== $expectedType) {
                    DB::table('attendance_logs')
                        ->where('id', $log->id)
                        ->update(['log_type' => $expectedType]);

                    $correctedCount++;
                }
                $processedCount++;
            }
            $this->output->write("<info>.</info>");
        });

        $this->newline();
        $this->table(
            ['Total Processed', 'Total Corrected'],
            [[$processedCount, $correctedCount]]
        );

        $this->info("Done!");
    }
}
