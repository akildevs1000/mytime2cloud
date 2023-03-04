<?php

namespace App\Console\Commands;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogController;
use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;


class SyncAbsent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Absent';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            echo (new AttendanceController)->SyncAbsent();
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncAbsent. Error Details: ' . $th);
            echo "[" . date("Y-m-d H:i:s") . "] Cron: SyncAbsent. Error occurred while inserting logs.\n";
        }
    }
}
