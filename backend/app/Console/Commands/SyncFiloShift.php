<?php

namespace App\Console\Commands;

use App\Http\Controllers\Shift\FiloShiftController;
use App\Http\Controllers\Shift\ShiftRenderController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;
use App\Models\Company;

class SyncFiloShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_filo_shift';

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
        try {
            $date = date("Y-m-d");
            $companies = Company::pluck("id");
            foreach ($companies as $company) {
                echo (new ShiftRenderController)->render($company, $date) . "\n";
            }
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncFiloShift. Error Details: ' . $th);
            $date = date("Y-m-d H:i:s");
            echo "[$date] Cron: SyncFiloShift. Error occured while inserting logs.\n";
        }
    }
}
