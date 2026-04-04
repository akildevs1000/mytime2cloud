<?php

namespace App\Console\Commands\Shift;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shift\MultiShiftController;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;
use Illuminate\Support\Facades\Log;

class SyncMultiShiftV1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_multi_shift_v1 {company_id} {date} {checked?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync multi shift attendance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("company_id", 1);
        $date = $this->argument("date", date("Y-m-d"));
        $checked = $this->argument("checked", false);

        $formattedDate = (new DateTime())->format('d M Y \a\t H:i:s');
        $logFilePath = "logs/shifts/multi_shift/command/$id";

        // Check if company has multi-shift
        $found = Shift::where("company_id", $id)
            ->where("shift_type_id", 2)
            ->count();

        if ($found == 0) {
           Log::channel('multi')->info("No multi-shift found for company $id");
            return Command::SUCCESS;
        }

       Log::channel('multi')->info("*****Cron started at $formattedDate for task:sync_multi_shift*****");

        // Get all employee IDs with logs for multi-shift
        $all_new_employee_ids = DB::table('schedule_employees as se')
            ->join('attendance_logs as al', 'se.employee_id', '=', 'al.UserID')
            ->join('shifts as sh', 'sh.id', '=', 'se.shift_id')
            ->select('al.UserID')
            ->where('sh.shift_type_id', 2)
            ->where('al.checked', $checked ? true : false)
            ->where('se.company_id', $id)
            ->where('al.company_id', $id)
            ->whereDate('al.log_date', $date)
            ->orderBy("al.LogTime")
            ->distinct()
            ->pluck("al.UserID")
            ->toArray();

        if (empty($all_new_employee_ids)) {
           Log::channel('multi')->info("No data found");
            return Command::SUCCESS;
        }

        $filtered_employee_ids = array_values(array_unique($all_new_employee_ids));

       Log::channel('multi')->info("Processing " . count($filtered_employee_ids) . " employees");
       Log::channel('multi')->info("Employee IDs: " . json_encode($filtered_employee_ids));

        // Initialize MultiShiftController and call render method
        $controller = new MultiShiftController();

        $shift_type_id = 2; // Multi-shift
        $custom_render = false; // Let the render method fetch employee IDs
        $channel = 'kernel'; // Command channel

        try {
            $result = $controller->render(
                $id,                        // company_id
                $date,                      // date
                $shift_type_id,             // shift_type_id
                $filtered_employee_ids,     // UserIds
                $custom_render,             // custom_render
                $channel                    // channel
            );

           Log::channel('multi')->info("Render Result: " . $result);

            // Get remaining unchecked logs count
            $remaining_logs = DB::table('attendance_logs as al')
                ->join('employees as e', 'e.system_user_id', '=', 'al.UserID')
                ->join('schedule_employees as se', 'e.system_user_id', '=', 'se.employee_id')
                ->join('shifts as sh', 'sh.id', '=', 'se.shift_id')
                ->where('sh.shift_type_id', 2)
                ->where('al.company_id', $id)
                ->where('e.company_id', $id)
                ->where('e.status', 1)
                ->whereDate('al.log_date', $date)
                ->where('al.checked', false)
                ->count();

            $message = "Attendance Log Processing Alert!\n\n";
            $message .= "Dear Admin\n";
            $message .= "Attendance Logs Processed for Company id $id at $formattedDate\n\n";
            $message .= "Processed " . count($filtered_employee_ids) . " employees\n";
            $message .= "$remaining_logs logs are pending\n\n";
            $message .= "Result: $result\n\n";
            $message .= "Thank you!\n";


           Log::channel('multi')->info("*****Cron ended for task:sync_multi_shift*****");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());

            $errorMessage = "Error processing attendance: " . $e->getMessage();

            return Command::FAILURE;
        }
    }
}
