<?php

namespace App\Console\Commands\Shift;

use App\Http\Controllers\Controller;
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
        $logFilePath = 'logs/shifts/multi_shift/command';

        $id = $this->argument("company_id", 1);

        date_default_timezone_set('UTC');

        (new Controller)->logOutPut($logFilePath, "*****Cron started for task:sync_multi_shift $id *****");

        $date = $this->argument("date", date("Y-m-d"));

        $logStartTime = Carbon::parse($date)->startOfDay();

        $logEndTime = Carbon::parse($date)->endOfDay();

        $found = Shift::where("company_id", $id)->where("shift_type_id", 2)->count();

        if ($found == 0) {
            // (new Controller)->logOutPut($logFilePath, "*****Cron ended for task:sync_multi_shift: no shift found for $id*****");
            return;
        }

        $all_new_employee_ids = DB::table('employees as e')
            ->join('attendance_logs as al', 'e.system_user_id', '=', 'al.UserID')
            ->select('al.UserID')
            ->where('e.status', 1)
            ->where('al.checked', true)
            ->where('al.company_id', $id)
            ->where('al.UserID', 115)
            ->whereBetween('al.LogTime', [$logStartTime, $logEndTime])
            ->orderBy("al.LogTime")
            ->take(10)
            ->pluck("al.UserID")
            ->toArray();

        if (!$all_new_employee_ids || count($all_new_employee_ids) == 0) {
            $this->info("No data");
            return;
        }

        $filtered_all_new_employee_ids = array_values(array_unique($all_new_employee_ids));

        $all_logs_for_employee_ids = DB::table('employees as e')
            ->join('attendance_logs as al', 'e.system_user_id', '=', 'al.UserID')
            ->join('schedule_employees as se', 'e.system_user_id', '=', 'se.employee_id')
            ->join('shifts as sh', 'sh.id', '=', 'se.shift_id')
            ->select(
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
            ->whereIn('al.UserID', $filtered_all_new_employee_ids)
            ->whereBetween('al.LogTime', [$logStartTime, $logEndTime]) // can i directly get the filtered record from this parent function
            ->orderBy("al.LogTime")
            ->get()
            ->groupBy("UserID") // Use the correct key from the selection
            ->toArray();



        $items = [];

        $foundKeys = [];

        if (!$all_logs_for_employee_ids || count($all_logs_for_employee_ids) == 0) {
            $this->info("No data");
            return;
        }

        $log_ids = [];

        foreach ($all_logs_for_employee_ids as $employeeId => $employeeLogs) {

            $uniqueEntries = [];
            $seen = [];

            foreach ($employeeLogs as $entry) {
                // Create a unique key based on specific fields
                $key = $employeeId . '-' . $id . '-' . $entry->LogTime;

                // Check if the key has already been seen
                if (!isset($seen[$key])) {
                    $seen[$key] = true; // Mark the key as seen
                    $uniqueEntries[] = $entry; // Add to unique entries
                }
            }

            if (!$uniqueEntries || count($uniqueEntries) == 0) {
                continue;
            }

            $item = [
                "employee_id" => $employeeId,
                "total_hrs" => 0,
                "ot" => "---",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "date" => $date,
                "company_id" => $id,
                "shift_id" => $uniqueEntries[0]->shift_id,
                "shift_type_id" => 2,
                "status" => count($uniqueEntries) % 2 !== 0 ?  Attendance::MISSING : Attendance::PRESENT,
            ];

            $logs = $this->processLogs($date, $uniqueEntries);
            $total_hrs = $this->calculateTotalHrs(array_column($logs, "total_minutes"));

            if ($uniqueEntries[0]->isOverTime) {
                $item["ot"] = (new Controller)->calculatedOT($total_hrs, $uniqueEntries[0]->working_hours, $uniqueEntries[0]->overtime_interval);
            }

            $log_ids = array_column($uniqueEntries, "log_id");

            $item["logs"] = json_encode($logs);
            $item["total_hrs"] =  $total_hrs;

            $items[] = $item;

            if (count($logs)) {
                $foundKeys[] = $employeeId;
            }
        }

        Attendance::whereIn("employee_id", $foundKeys)
            ->where("date", $date)
            ->where("company_id", $id)
            ->delete();

        Attendance::where("company_id", $id)->where("date", $date)->insert($items);

        $message = "*****task:sync_multi_shift affected ids " . json_encode($foundKeys) . " *****";

        $this->info($message);

        $all_new_employee_ids = DB::table('attendance_logs')
            ->whereIn('id', $log_ids)
            ->update(
                [
                    "checked" => true,
                    "checked_datetime" => date('Y-m-d H:i:s'),
                    "channel" => "kernel",
                    "log_message" => substr($message, 0, 200)
                ]
            );

        (new Controller)->logOutPut($logFilePath, $message);

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

        for ($i = 0; $i < $logsCount; $i += 2) {
            $currentLog = $employeeLogs[$i];
            $nextLog = isset($employeeLogs[$i + 1]) ? $employeeLogs[$i + 1] : false;

            $currentTime = date("H:i", strtotime($currentLog->LogTime));

            $parsed_in = strtotime("$date $currentTime");

            if ($nextLog) {

                $nextTime = date("H:i", strtotime($nextLog->LogTime));

                $parsed_out = strtotime("$date $nextTime");

                if ($parsed_in > $parsed_out) {
                    //$item["extra"] = $nextLog['time'];
                    $parsed_out += 86400;
                }

                // Skip logs outside the duty period
                if ($parsed_in < $on_duty_timestamp || $parsed_in > $off_duty_timestamp) {
                    continue;
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
