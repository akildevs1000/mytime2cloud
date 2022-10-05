<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyIfLogsDoesNotGenerate;


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

        $file = base_path() . "/logs/logs.csv";

        if (!file_exists($file)) {

            Logger::channel("custom")->info('No new data found');

            return [
                'status' => false,
                'message' => 'No new data found',
            ];
        }

        $header = null;
        $data = [];

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                $data[] = array_combine(["UserID", "DeviceID", "LogTime", "SerialNumber"], $row);
                
            }
            fclose($handle);
        }
        try {
            $created = AttendanceLog::insert($data);
            $created ? unlink($file) : 0;
            $count = count($data);
            Logger::channel("custom")->info($count . ' new logs has been inserted. Old file has been deleted.');
            return $created ?? 0;
        } catch (\Throwable $th) {
        
            Logger::channel("custom")->error('Error occured while inserting logs.');
            Logger::channel("custom")->error('Error Details: ' . $th);

            $data = [
                'title' => 'Quick action required',
                'body' => $th,
            ];

            Mail::to(env("ADMIN_MAIL_RECEIVERS"))->send(new NotifyIfLogsDoesNotGenerate($data));
            return;
        }
    }
}
