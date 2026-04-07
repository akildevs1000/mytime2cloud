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

        $companyIds = Company::whereHas('attendance_logs', function ($query) use ($today) {
            $query->whereDate('log_date', $today);
        })->pluck('id');

        $rows = [];

        foreach ($companyIds as $id) {
            $this->info("Processing Company ID: $id...");

            // Execute commands
            Artisan::call("task:sync_attendance_missing_shift_ids $id $today");
            Artisan::call("task:sync_auto_shift $id $today");

            $onDutyTime = $company->shift->on_duty_time ?? '06:00';
            $cutoff = (int) explode(':', $onDutyTime)[0] ?? "06";

            Log::channel('shift')->info("Company ID: $id - On Duty Time: $onDutyTime - Current Hour: $hour - Cutoff Hour: $cutoff");

            $multiShift = 'Skipped';
            if ($hour >= 6) {
                Artisan::call("task:sync_multi_shift_v1 $id $today");
                Artisan::call("task:sync_split_shift $id $today");
                Artisan::call("task:sync_except_auto_shift $id $today");
                $multiShift = 'Synced';
            } else {
                Artisan::call("task:sync_except_auto_shift $id $yesterday");
            }

            // Collect data for the table (Trait will handle formatting)
            $rows[] = [
                $id,
                $today,
                'Synced',
                $multiShift,
                date('H:i:s')
            ];
        }

        // 3. Call the Trait method 
        // Usage: logAsTable(headers, rows, title, custom_channel_name)
        $this->logAsTable(
            ['Company ID', 'Log Date', 'Standard Sync', 'Multi/Split', 'Finished At'],
            $rows,
            'Company Shift Sync Report',
            'shift' // You can change this to 'single' or any custom channel
        );

        $this->info('All company shifts synchronized successfully.');
    }
}
