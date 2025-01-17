<?php

namespace App\Console\Commands\Shift;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $id = $this->argument("company_id", 1);

        $date = $this->argument("date", date("Y-m-d", strtotime("yesterday")));

        $nextDate = date("Y-m-d", strtotime($date . "+1 day"));

        // $employeeIdsString = $this->argument("employee_ids");

        $all_ids = Employee::whereHas("attendance_logs", function ($q) use ($id, $date, $nextDate) {
            $q->where("company_id", $id);
            $q->where("LogTime", ">=", $date);
            $q->where("LogTime", "<=", $nextDate);
            $q->where("checked", false);
        })->pluck("system_user_id")->take(5)->toArray();

        $employee_ids = array_values(array_unique($all_ids));

        Log::info("employee_ids: " . json_encode($employee_ids));

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
            'company_id' => 2,
        ];

        $this->info(json_encode($payload));

        $url = 'https://backend.mytime2cloud.com/api/render_logs';

        if (env("APP_ENV") == "desktop") {
            $url = 'https://mytime2cloud-backend.test/api/render_logs';
        }

        $response = Http::withoutVerifying()->get($url, $payload);

        if ($response->successful()) {

            $payload = [
                'message' => 'Cron Execution Success: task:sync_multi_shift',
                'app' => env('APP_NAME'),
                'company_id' => $id,
                'date' => $date,
                'response_status' => $response->status(),
                'response_body' => "---",
                'employee_ids' => json_encode($employee_ids),
            ];

            Log::info($payload);
            $this->info(json_encode($payload));
        } else {
            $payload = [
                'message' => 'Cron Execution Failed: task:sync_multi_shift',
                'app' => env('APP_NAME'),
                'company_id' => $id,
                'date' => $date,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ];

            Log::info($payload);
            $this->info(json_encode($payload));
        }
    }
}
