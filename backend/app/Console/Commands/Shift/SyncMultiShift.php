<?php

namespace App\Console\Commands\Shift;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncMultiShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_multi_shift {company_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logFilePath = 'logs/shifts/multi_shift/command';

        $id = $this->argument("company_id", 1);

        (new Controller)->logOutPut($logFilePath, "*****Cron started for task:sync_multi_shift $id *****");

        $date = $this->argument("date", date("Y-m-d", strtotime("yesterday")));

        $nextDate = date("Y-m-d", strtotime($date . "+1 day"));

        $found = Shift::where("company_id", $id)->where("shift_type_id", 2)->count();

        if ($found == 0) {
            (new Controller)->logOutPut($logFilePath, "*****Cron ended for task:sync_multi_shift: no shift found for $id*****");
            return;
        }

        $all_ids = Employee::whereHas("attendance_logs", function ($q) use ($id, $date, $nextDate) {
            // $q->where("UserID", 698);
            $q->where("company_id", $id);
            $q->where("LogTime", ">=", $date);
            $q->where("LogTime", "<=", $nextDate);
            $q->where("checked", false);
        })->pluck("system_user_id")->take(5)->toArray();

        $employee_ids = array_values(array_unique($all_ids));

        if (count($employee_ids) == 0) {
            $this->info("No data");
            return;
        }

        $payload = [
            'date' => '',
            'UserID' => '',
            'updated_by' => "26",
            'company_ids' => [$id],
            'manual_entry' => true,
            'reason' => '',
            'employee_ids' => $employee_ids,
            'dates' => [$date, $nextDate],
            'shift_type_id' => 2,
            'company_id' => $id,
            'channel' => "kernel",
        ];

        // $this->info(json_encode($payload));

        $url = 'https://backend.mytime2cloud.com/api/render_logs';

        // if (env("APP_ENV") == "desktop") {

        //     $localIp = gethostbyname(gethostname());
        //     $port = 8000;
        //     $url = "http://$localIp:$port/api/render_logs";
        //     // $url = 'https://mytime2cloud-backend.test/api/render_logs';
        // }

        $response = Http::withoutVerifying()->get($url, $payload);

        if ($response->successful()) {

            (new Controller)->logOutPut(
                $logFilePath,
                [
                    'message' => 'Cron Execution Success: task:sync_multi_shift',
                    'app' => env('APP_NAME'),
                    'company_id' => $id,
                    'date' => date("Y-m-d"),
                    'response_status' => $response->status(),
                    'response_body' => "---",
                    'employee_ids' => $employee_ids,
                ]
            );
        } else {
            (new Controller)->logOutPut(
                $logFilePath,
                [
                    'message' => 'Cron Execution Failed: task:sync_multi_shift',
                    'app' => env('APP_NAME'),
                    'company_id' => $id,
                    'date' => $date,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]
            );
        }
        (new Controller)->logOutPut($logFilePath, "*****Cron ended for task:sync_multi_shift $id*****");
    }
}
