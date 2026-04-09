<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Facades\Artisan;

class SyncCompanyAlerts extends Command
{
    // We'll call it 'company:sync-alerts'
    protected $signature = 'company:sync-alerts';
    protected $description = 'Sync alert records for all companies in one go';

    public function handle()
    {
        $today = date("Y-m-d");
        $companyIds = Company::pluck('id');

        foreach ($companyIds as $id) {
            $this->info("Processing Alerts for Company ID: $id");

            Artisan::call("alert:access_control $id");
            Artisan::call("alert:attendance $id");

            $this->info("Done with Company $id");
        }

        info('All company shifts synchronized successfully.');
        $this->info('All company shifts synchronized successfully.');
    }
}
