<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Facades\Artisan;

class SyncCompanyShifts extends Command
{
    // We'll call it 'company:sync-shifts'
    protected $signature = 'company:sync-shifts';
    protected $description = 'Sync all shift types for all companies in one go';

    public function handle()
    {
        $today = date("Y-m-d");
        $yesterday = date("Y-m-d", strtotime("yesterday"));
        $companyIds = Company::whereHas('attendance_logs', function ($query) use ($today) {
            $query->whereDate('LogTime', $today); // Adjust 'LogTime' to your actual column name
        })->pluck('id');

        foreach ($companyIds as $id) {
            info("Processing Shifts for Company ID: $id");
            $this->info("Processing Shifts for Company ID: $id");

            $hour = (int)date('H');

            Artisan::call("task:sync_attendance_missing_shift_ids $id $today");
            Artisan::call("task:sync_auto_shift $id $today");
            Artisan::call("task:sync_except_auto_shift $id $today");

            // 5. Multi & Split Shifts (30 mins - between 5:00 and 23:59)
            if ($hour >= 5) {
                Artisan::call("task:sync_multi_shift_v1 $id $today");
                Artisan::call("task:sync_split_shift $id $today");
            }

            info("Done with Company $id");
            $this->info("Done with Company $id");
        }

        info('All company shifts synchronized successfully.');
        $this->info('All company shifts synchronized successfully.');
    }
}
