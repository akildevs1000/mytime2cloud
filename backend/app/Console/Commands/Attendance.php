<?php

namespace App\Console\Commands;

use App\Http\Controllers\AttendanceLogController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;

class Attendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $AttendanceLogController = new AttendanceLogController;
        $result = $AttendanceLogController->generate_logs();
        $arr = [$result["id"], $result["UserID"], $result["LogTime"], $result["DeviceID"]];
        Logger::channel("custom")->info(json_encode($arr));

    }
}