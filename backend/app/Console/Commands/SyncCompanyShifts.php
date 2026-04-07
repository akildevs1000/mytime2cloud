<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Traits\LogsTable; // 1. Use the Trait
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncCompanyShifts extends Command
{
    use LogsTable; // 2. Enable the Trait functionality

    protected $signature = 'company:sync-shifts';
    protected $description = 'Sync all shift types for all companies and log as a table';

    public function handle()
    {
        $today = date("Y-m-d");
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $hour = (int)date('H');

        // 1. Fully Optimized Query: Selecting only 'id' for Company
        $companies = Company::query()
            // ->with(['shifts' => function ($query) {
            //     // company_id is mandatory for the relationship to link properly
            //     $query->select('id', 'company_id', 'on_duty_time', 'shift_type_id')
            //         ->whereIn('shift_type_id', [1, 2]);
            // }])
            ->whereHas('attendance_logs', function ($query) use ($yesterday, $today) {
                $query->whereBetween("LogTime", [
                    $yesterday . ' 00:00:00',
                    $today . ' 23:59:59'
                ]);
            })
            ->get(['id']);

        $rows = [];

        foreach ($companies as $company) {
            $id = $company->id;

            // 2. Extract cutoff hour (0-23) from the "HH:MM" string
            // $cutoff = $company->shifts->map(function ($shift) {
            //     return (int) explode(':', $shift->on_duty_time)[0];
            // })->min() ?? 6;

            // $logMsg = "Processing ID: $id (Cutoff: $cutoff:00, Current Hour: $hour)";
            // $this->info($logMsg);
            // Log::channel('shift')->info($logMsg);

            // 3. Execution Phase
            Artisan::call("task:sync_attendance_missing_shift_ids $id $today");
            Artisan::call("task:sync_auto_shift $id $today");

            $multiShift = 'Skipped';

            if ($hour >= 6) {
                Artisan::call("task:sync_multi_shift_v1 $id $today");
                Artisan::call("task:sync_split_shift $id $today");
                Artisan::call("task:sync_except_auto_shift $id $today");
                $multiShift = "Synced (Today)";
            } else {
                Artisan::call("task:sync_except_auto_shift $id $yesterday");
                $multiShift = "Synced (Yesterday)";
            }

            $rows[] = [$id, $today, 'Synced', $multiShift, date('H:i:s')];
        }

        // 4. Log the final summary table to the 'shift' channel
        $this->logAsTable(
            ['ID', 'Date', 'Std Sync', 'Multi/Split', 'Finished'],
            $rows,
            'Shift Sync Report',
            'shift' // Directs the Trait to use the 'shift' channel
        );

        $this->info('Sync process completed and logged to "shift" channel.');
    }
}
