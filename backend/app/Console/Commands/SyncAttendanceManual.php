<?php

namespace App\Console\Commands;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\Shift\MultiInOutShiftController;
use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;


class SyncAttendanceManual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_attendance_manual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Attendance Manual';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = date("Y-m-d H:i:s");
        $script_name = "SyncAttendanceManual";

        $meta = "[$date] Cron: $script_name.";

        try {
            $Attendance = new MultiInOutShiftController;
            $result = $Attendance->processByManual();
            $message =  $meta . " " . $result . ".\n";
            echo $message;
            return;
        } catch (\Throwable $th) {
            Logger::channel("custom")->error("Cron: $script_name. Error Details: $th");
            echo "[$date] Cron: $script_name. Error occured while inserting logs.\n";
            return;
        }
    }
}
