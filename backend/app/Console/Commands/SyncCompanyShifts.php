<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Traits\LogsTable; // 1. Use the Trait
use Illuminate\Support\Facades\Artisan;

class SyncCompanyShifts extends Command
{
    use LogsTable; // 2. Enable the Trait functionality

    protected $signature = 'company:sync-shifts';
    protected $description = 'Sync all shift types for all companies and log as a table';

    public function handle()
    {
        $today = date("Y-m-d");
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
            Artisan::call("task:sync_except_auto_shift $id $today");

            $multiShift = 'Skipped';
            if ($hour >= 5) {
                Artisan::call("task:sync_multi_shift_v1 $id $today");
                Artisan::call("task:sync_split_shift $id $today");
                $multiShift = 'Synced';
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