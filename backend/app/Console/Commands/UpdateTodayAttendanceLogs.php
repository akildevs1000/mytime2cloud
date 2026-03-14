<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateTodayAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:fix-today';

    /**
     * The console command description.
     */
    protected $description = 'Updates null log_type values for logs created from today onwards';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Scanning for logs dated: {$today} and future...");

        // 1. Fetch Mapping logic
        $deviceFunctionMap = Device::excludeMobile()
            ->get(['device_id', 'function'])
            ->pluck('function', 'device_id')
            ->toArray();

        $inTypeValues = ['in', 'auto', 'entry'];
        $count = 0;

        // 2. Query logs where log_date >= today AND log_type is missing
        $query = DB::table('attendance_logs')
            ->whereDate('log_date', '>=', $today)
            ->where(function ($q) {
                $q->whereNull('log_type')->orWhere('log_type', '');
            });

        $totalFound = $query->count();

        if ($totalFound === 0) {
            $this->info("No logs found requiring updates for today.");
            return;
        }

        // 3. Process records
        $query->orderBy('id')->chunk(200, function ($logs) use ($deviceFunctionMap, $inTypeValues, &$count) {
            foreach ($logs as $log) {
                $deviceId = $log->DeviceID;
                
                // Determine Log Type Logic
                $deviceFunction = isset($deviceFunctionMap[$deviceId])
                    ? strtolower($deviceFunctionMap[$deviceId])
                    : '';

                if (in_array($deviceFunction, $inTypeValues)) {
                    $logType = 'In';
                } elseif ($deviceFunction === 'out') {
                    $logType = 'Out';
                } else {
                    $logType = (str_contains(strtolower($deviceId), 'in')) ? 'In' : 'Out';
                }

                DB::table('attendance_logs')
                    ->where('id', $log->id)
                    ->update(['log_type' => $logType]);

                $count++;
            }
            $this->output->write("."); // Progress indicator
        });

        $this->newline();
        $this->info("Task complete. {$count} logs updated.");
    }
}