<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;


class SyncAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_attendance_logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Attendance Logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Logger::channel("custom")->info("testing...");
    }
}
