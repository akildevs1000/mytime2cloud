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
        $Attendance = new AttendanceController;
        $i = $Attendance->SyncAttendance();

        if (!$i) {
            Logger::channel("custom")->info("No new logs found");
            return;
        }

        Logger::channel("custom")->info("Log processed " . $i);
        return;

        // $AttendanceLogController = new AttendanceLogController;
        // $AttendanceLogController->generate_logs();
    }
}
