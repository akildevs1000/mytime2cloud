<?php

namespace App\Console\Commands\Shift;

use App\Jobs\Shift\SyncFlexibleShiftJob;
use Illuminate\Console\Command;

class SyncFlexibleShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_flexible_shift {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Flexible Shift';

    public function handle()
    {
        $companyId = $this->argument('company_id');

        $date = $this->argument('date');

        SyncFlexibleShiftJob::dispatch($companyId, $date);
        
        $this->info("SyncFlexibleShiftJob dispatched for Company: $companyId Date: $date");
    }
}
