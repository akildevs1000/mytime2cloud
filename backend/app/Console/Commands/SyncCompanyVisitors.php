<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Facades\Artisan;

class SyncCompanyVisitors extends Command
{
    // We'll call it 'company:sync-visitors'
    protected $signature = 'company:sync-visitors';
    protected $description = 'Sync visitor records for all companies in one go';

    public function handle()
    {
        $today = date("Y-m-d");
        $companyIds = Company::pluck('id');

        foreach ($companyIds as $id) {
            $this->info("Processing Visitors for Company ID: $id");

            Artisan::call("task:sync_visitor_set_expire_dates $id");
            Artisan::call("task:sync_visitor_attendance $id $today");

            $this->info("Done with Company $id");
        }

        info('All company shifts synchronized successfully.');
        $this->info('All company shifts synchronized successfully.');
    }
}
