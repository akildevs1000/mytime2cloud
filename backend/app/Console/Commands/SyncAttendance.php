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


class SyncAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Attendance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = date("Y-m-d H:i:s");
        $script_name = "SyncAttendance";

        $meta = "[$date] Cron: $script_name.";

        try {
            $Attendance = new AttendanceController;
            $result = $Attendance->SyncAttendance();
            $message =  $meta . " " . $result . ".\n";
            echo $message;
            return;
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('Cron: SyncAttendance. Error Details: ' . $th);
            echo "[$date] Cron: $script_name. Error occured while inserting logs.\n";
            return;
        }
    }
}
