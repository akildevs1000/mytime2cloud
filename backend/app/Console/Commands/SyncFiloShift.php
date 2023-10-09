<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\FiloShiftController;
use Illuminate\Console\Command;

class SyncFiloShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_filo_shift {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Filo Shift';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("company_id");
        $date = $this->argument("date");
        $shift_type_id = 1;

        echo (new FiloShiftController)->render($id, $date, $shift_type_id, [], false) . "\n";
    }
}
