<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\FiloShiftController;
use App\Http\Controllers\Shift\NightShiftController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;


class SyncNightShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_night_shift';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Night Shift';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        try {
            echo (new NightShiftController)->render();
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncNightShift. Error Details: ' . $th);
            $date = date("Y-m-d H:i:s");
            echo "[$date] Cron: SyncNightShift. Error occured while inserting logs.\n";
        }
    }
}
