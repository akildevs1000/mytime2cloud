<?php

namespace App\Console\Commands\Shift;

use App\Jobs\Shift\SyncExceptAutoShiftJob;
use Illuminate\Console\Command;

class SyncExceptAutoShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_except_auto_shift {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Other Shifts like (Filo,Single,Night,Multi) except Auto Shift';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        $date = $this->argument('date');

        SyncExceptAutoShiftJob::dispatch($companyId, $date);
        $this->info("SyncExceptAutoShiftJob dispatched for Company: $companyId Date: $date");
    }
}
