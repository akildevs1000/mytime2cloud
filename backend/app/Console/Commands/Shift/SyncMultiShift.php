<?php

namespace App\Console\Commands\Shift;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        $id = $this->argument("company_id", 1);

        $formattedDate = (new DateTime())->format('d M Y \a\t H:i:s');

        $message = "Attendance Log Processing Alert !\n\n";

        $message .= "Dear Admin\n";

        $message .= "Attendance Logs Processed for Company id $id at $formattedDate\n\n";

        $logFilePath = 'logs/shifts/multi_shift/command';

        $date = $this->argument("date", date("Y-m-d"));

        // date_default_timezone_set('UTC');

        (new Controller)->logOutPut($logFilePath, "*****Cron started for task:sync_multi_shift $id *****");

        $found = Shift::where("company_id", $id)->where("shift_type_id", 2)->count();

        if ($found == 0) {
            return;
        }

        $all_new_employee_ids = DB::table('employees as e')
            ->join('attendance_logs as al', 'e.system_user_id', '=', 'al.UserID')
            ->select('al.UserID')
            ->where('e.status', 1)
            ->where('al.checked', false)
            // ->where('al.UserID', 57)
            ->where('al.company_id', $id)
            // ->whereBetween('al.LogTime', [$logStartTime, $logEndTime])
            ->where('al.LogTime', ">=", $date)
            ->whereDate('al.log_date', $date)
            ->orderBy("al.LogTime")
            ->take(50)
            ->pluck("al.UserID")
            ->toArray();

        if (!$all_new_employee_ids || count($all_new_employee_ids) == 0) {
            $this->info("No data");

            if ($id == 22) {

                $message .= "No Data Found\n";

                $message .= "Thank you!\n";

                SendWhatsappMessageJob::dispatch(
                    env("ADMIN_WHATSAPP_NUMBER"),
                    $message,
                    0,
                    env("WHATSAPP_CLIENT_ID"),
                    $logFilePath
                );
            }

            return;
        }

        $filtered_all_new_employee_ids = array_values(array_unique($all_new_employee_ids));

        $all_logs_for_employee_ids = DB::table('employees as e')
            ->join('attendance_logs as al', 'e.system_user_id', '=', 'al.UserID')
            ->join('schedule_employees as se', 'e.system_user_id', '=', 'se.employee_id')
            ->join('shifts as sh', 'sh.id', '=', 'se.shift_id')
            ->select(
                'e.first_name',
                'e.employee_id',
                'e.company_id',
                'e.system_user_id',
                'al.id as log_id',
                'al.LogTime',
                'al.UserID',
                'sh.id as shift_id',
                'sh.on_duty_time',
                'sh.off_duty_time',
                'sh.working_hours',
                'sh.overtime_interval',
                'se.isOverTime'
            )
            ->where('e.status', 1)
            ->where('al.company_id', $id)
            ->where('e.company_id', $id)
            ->whereIn('al.UserID', $filtered_all_new_employee_ids)
            ->where('al.LogTime', ">=", $date)
            ->whereDate('al.log_date', $date)
            ->distinct('al.LogTime', 'al.UserID', 'e.company_id')
            ->orderBy("al.LogTime")
            ->get()
            ->groupBy("UserID")
            ->toArray();

        $items = [];

        if (!$all_logs_for_employee_ids || count($all_logs_for_employee_ids) == 0) {
            $this->info("No data");

            if ($id == 22) {

                $message .= "No Data Found\n";

                $message .= "Thank you!\n";

                SendWhatsappMessageJob::dispatch(
                    env("ADMIN_WHATSAPP_NUMBER"),
                    $message,
                    0,
                    env("WHATSAPP_CLIENT_ID"),
                    $logFilePath
                );
            }

            return;
        }

        $UserIDs = [];

        $responseMessage = "";

        foreach ($all_logs_for_employee_ids as $UserID => $uniqueEntries) {


            if (!$uniqueEntries || count($uniqueEntries) == 0) {
                continue;
            }

            $item = [
                "employee_id" => $UserID,
                "total_hrs" => 0,
                "ot" => "---",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "date" => $date,
                "company_id" => $id,
                "shift_id" => $uniqueEntries[0]->shift_id,
                "shift_type_id" => 2,
                "status" => count($uniqueEntries) % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $logs = $this->processLogs($date, $uniqueEntries);
            $total_hrs = $this->calculateTotalHrs(array_column($logs, "total_minutes"));

            if ($uniqueEntries[0]->isOverTime) {
                $item["ot"] = (new Controller)->calculatedOT($total_hrs, $uniqueEntries[0]->working_hours, $uniqueEntries[0]->overtime_interval);
            }

            $item["logs"] = json_encode($logs);
            $item["total_hrs"] =  $total_hrs;

            $items[] = $item;
            $UserIDs[] = $UserID;


            $responseMessage .= "Name {$uniqueEntries[0]->first_name} with id: {$uniqueEntries[0]->employee_id} \n";
        }

        // ld($items);

        Attendance::whereIn("employee_id", $UserIDs)
            ->where("date", $date)
            ->where("company_id", $id)
            ->delete();

        Attendance::where("company_id", $id)->where("date", $date)->insert($items);

        $all_new_employee_ids = DB::table('attendance_logs')
            ->whereIn('UserID', $UserIDs)
            ->where('LogTime', ">=", $date)
            ->where('log_date', $date)
            ->where('company_id', $id)
            ->update(
                [
                    "checked" => true,
                    "checked_datetime" => date('Y-m-d H:i:s'),
                    "channel" => "kernel",
                    "log_message" => substr(json_encode($items), 0, 200)
                ]
            );


        $remaining_logs = DB::table('attendance_logs')
            ->where('log_date', $date)
            ->where('checked', false)
            ->where('company_id', $id)
            ->count();

        // $this->info(json_encode($items));

        $message .= "$responseMessage\n";

        $message .= "$remaining_logs logs are pending\n";

        $message .= "Thank you!\n";

        if ($id == 22) {
            SendWhatsappMessageJob::dispatch(
                env("ADMIN_WHATSAPP_NUMBER"),
                $message,
                0,
                env("WHATSAPP_CLIENT_ID"),
                $logFilePath
            );
        }

        (new Controller)->logOutPut($logFilePath, "*****task:sync_multi_shift payload start Company Id: $id,  *****");
        (new Controller)->logOutPut($logFilePath, $items);
        (new Controller)->logOutPut($logFilePath, "*****task:sync_multi_shift payload end Company Id: $id,  *****");
        (new Controller)->logOutPut($logFilePath, "*****Cron ended for task:sync_multi_shift $id*****");
    }

    function processLogs($date, $employeeLogs)
    {
        // Assuming $employeeLogs[0]->on_duty_time and $employeeLogs[0]->off_duty_time are strings like "06:00" and "03:00"
        $on_duty_time = $employeeLogs[0]->on_duty_time; // "06:00"
        $off_duty_time = $employeeLogs[0]->off_duty_time; // "03:00"

        // Create DateTime objects for on_duty and off_duty
        $on_duty = new DateTime("$date $on_duty_time");
        $off_duty = new DateTime("$date $off_duty_time");

        // If off_duty_time is earlier than on_duty_time, it means it's on the next day
        if ($off_duty < $on_duty) {
            $off_duty->modify('+1 day'); // Add one day to off_duty
        }

        // Convert duty times to timestamps for comparison
        $on_duty_timestamp = $on_duty->getTimestamp();
        $off_duty_timestamp = $off_duty->getTimestamp();

        $pairedLogs = [];
        $logsCount = count($employeeLogs);

        for ($i = 0; $i < $logsCount; $i++) {
            $currentLog = $employeeLogs[$i];
            $nextLog = isset($employeeLogs[$i + 1]) ? $employeeLogs[$i + 1] : false;

            $currentTime = date("H:i", strtotime($currentLog->LogTime));

            $parsed_in = strtotime("$date $currentTime");

            // Skip logs outside the duty period
            if ($parsed_in < $on_duty_timestamp || $parsed_in > $off_duty_timestamp) {
                continue;
            }

            if ($nextLog) {

                $nextTime = date("H:i", strtotime($nextLog->LogTime));

                $parsed_out = strtotime("$date $nextTime");

                if ($parsed_in > $parsed_out) {
                    $parsed_out += 86400;
                }


                $nextTime = date("H:i", strtotime($nextLog->LogTime));
                $parsed_out = strtotime("$date $nextTime");

                // Skip if the "out" time is outside the duty period
                if ($parsed_out < $on_duty_timestamp || $parsed_out > $off_duty_timestamp) {
                    continue;
                }

                // Calculate the duration in minutes
                $diff = $parsed_out - $parsed_in;
                $minutes = ($diff / 60);
                $pairedLogs[] = [
                    'in' => $currentTime,
                    'out' => $nextTime,
                    'total_minutes' => (new Controller)->minutesToHours($minutes),
                    "device_in" =>  "---",
                    "device_out" =>  "---",
                ];
            } else {
                // Handle the last log if there's no pair
                $pairedLogs[] = [
                    'in' => $currentTime,
                    'out' => "---",
                    'total_minutes' => "00:00",
                    "device_in" =>  "---",
                    "device_out" =>  "---",
                ];
            }

            $i++;
        }

        return $pairedLogs;
    }

    function calculateTotalHrs($times)
    {
        $totalMinutes = 0;

        // Convert all times to total minutes
        foreach ($times as $time) {
            list($hours, $minutes) = explode(':', $time);
            $totalMinutes += (int)$hours * 60 + (int)$minutes;
        }

        // Convert total minutes back to HH:MM format
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        // Format as HH:MM
        return sprintf("%02d:%02d", $totalHours, $remainingMinutes);
    }
}
