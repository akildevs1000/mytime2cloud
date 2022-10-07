<?php

namespace App\Console\Commands;

use App\Http\Controllers\AttendanceLogController;
use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;


class SyncAttendanceForReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_attendance_for_reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Attendance For Reports';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $AttendanceLogController = new AttendanceLogController;
        $result = $AttendanceLogController->generate_logs();
        Logger::channel("custom")->info("processed");
    }
}
