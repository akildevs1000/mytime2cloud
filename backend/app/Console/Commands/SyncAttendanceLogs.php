<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
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

        $file = base_path() . "/logs/logs.csv";

        if (!file_exists($file)) {

            Logger::channel("custom")->info('No new data found');

            // return [
            //     'status' => false,
            //     'message' => 'No new data found',
            // ];
        }

        $header = null;
        $data = [];

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                $data[] = array_combine(["UserID", "DeviceID", "LogTime", "SerialNumber"], $row);


                // if (!$header) {
                //     $header = join(",", $row); //. ",company_id";
                //     $header = str_replace(" ", "", $header);
                //     $header = explode(",", $header);
                // } else {
                //     $row[] = Device::where("device_id", $row[1])->pluck("company_id")[0] ?? 0;

                //     $data[] = array_combine($header, $row);
                // }
            }
            fclose($handle);
        }
        try {
            $created = AttendanceLog::insert($data);
            $created ? unlink($file) : 0;
            Logger::channel("custom")->info('Old file has been deleted');
            Logger::channel("custom")->info('All data has been inserted');
            return $created ?? 0;
        } catch (\Throwable $th) {
            Logger::channel("custom")->error('error found');
        }
    }
}
