<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\RenderController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class SyncAutoShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Auto Shift';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            echo (new RenderController)->renderAutoCron();
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncAuto. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: SyncAuto. Error occurred while inserting logs.\n";
        }
    }
}
