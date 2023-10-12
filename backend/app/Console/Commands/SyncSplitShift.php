<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\SplitShiftController;
use Illuminate\Console\Command;


class SyncSplitShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_split_shift {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Split Shift';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("company_id");
        $date = $this->argument("date");
        $shift_type_id = 5;
        echo (new SplitShiftController)->render($id, $date, $shift_type_id, [], false) . "\n";
    }
}
