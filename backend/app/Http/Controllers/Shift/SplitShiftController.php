<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SplitShiftController extends Controller
{
    public $logFilePath = 'logs/shifts/dual_shift/controller';

    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        // $endDateString = $request->dates[1];
        if (isset($request->dates[1])) {
            $endDateString = $request->dates[1];
        } else {
            $endDateString = $request->dates[0];
        }

        $company_id = $request->company_ids[0];
        $employee_ids = $request->employee_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $response = [];

        // while ($startDate <= $currentDate && $startDate <= $endDate) {
        while ($startDate <= $endDate) {
            //$response[] = $this->render($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, true);

            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 5, $employee_ids, $request->filled("auto_render") ? false : true, $request->channel ?? "unknown");


            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        // return $departmentIds = Department::where("company_id",$request->company_id)->pluck("id");
        // $employee_ids = Employee::where("department_id", 31)->pluck("system_user_id");

        return $this->render($request->company_id, $request->date, $request->shift_type_id, $request->UserIds, $request->custom_render ?? true, $request->channel ?? "unknown");
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel)
    {
        $params = [
            "company_id"    => $id,
            "date"          => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds"       => $UserIds,
        ];

        // 1. Cache Holiday Check (Crucial for performance during range regeneration)
        $isHoliday = Cache::remember("holiday_{$id}_{$date}", 3600, function () use ($id, $date) {
            return DB::table('holidays')
                ->where('company_id', $id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
        });

        if (!$custom_render) {
            // If we are regenerating a range, we MUST ensure UserIds is not empty
            // If it's empty, we get all employees for the company
            $params["UserIds"] = $UserIds ?: (new Employee)->where("company_id", $id)->pluck("system_user_id")->toArray();
        }

        // 2. Get the target employees 
        // We use a simple pluck if the multi-render query is skipping people with no logs
        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        $items = [];
        $message = "";

        // 3. Loop through EVERY employee found
        foreach ($employees as $row) {
            $employeeId = $row->system_user_id;
            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift ?? false;

            // Skip if no shift is assigned (you can't render attendance without a shift)
            if (!$params["shift"]) {
                $message .= "{$employeeId}: No shift; ";
                continue;
            }

            // 4. Default Status (H/W/A) for days with NO logs
            $dayOfWeek = date('D', strtotime($date));
            $currentDayKey = Attendance::DAY_MAP[$dayOfWeek] ?? '';

            if ($isHoliday) {
                $defaultStatus = "H";
            } else {
                $defaultStatus = Attendance::processWeekOffFunc(
                    $currentDayKey,
                    $params["shift"]->weekoff_rules ?? "A",
                    $id,
                    $date,
                    $employeeId,
                    null
                );
            }

            // 5. Fetch and Deduplicate Logs
            $logs = AttendanceLog::where("company_id", $id)
                ->where("UserID", $employeeId)
                ->where("log_date", $date)
                ->orderBy("LogTime", 'asc')
                ->get()
                ->load("device");

            $data = collect($logs)->unique('LogTime')->values();
            $count = $data->count();

            $totalMinutes = 0;
            $logsJson     = [];
            $i = 0;

            // 6. Pairing Logic (Handles incomplete pairs with ---)
            while ($i < $count) {
                $currentLog = $data[$i];
                $nextLog    = $data[$i + 1] ?? null;

                $inTimeRaw  = $currentLog->LogTime;
                $outTimeRaw = "---";

                if ($nextLog) {
                    $outTimeRaw = $nextLog->LogTime;
                    $parsedIn   = strtotime($inTimeRaw);
                    $parsedOut  = strtotime($outTimeRaw);

                    if ($parsedOut < $parsedIn) {
                        $parsedOut += 86400;
                    }

                    $minutes = ($parsedOut - $parsedIn) / 60;
                    $totalMinutes += $minutes;

                    $logsJson[] = [
                        "in"            => date("H:i", $parsedIn),
                        "out"           => date("H:i", $parsedOut),
                        "device_in"     => $currentLog->device->name ?? "---",
                        "device_out"    => $nextLog->device->name ?? "---",
                        "total_minutes" => (int)$minutes,
                    ];
                    $i += 2;
                } else {
                    $logsJson[] = [
                        "in"            => date("H:i", strtotime($inTimeRaw)),
                        "out"           => "---",
                        "device_in"     => $currentLog->device->name ?? "---",
                        "device_out"    => "---",
                        "total_minutes" => 0,
                    ];
                    $i += 1;
                }
            }

            // 7. PREPARE RECORD (Even if $count is 0, we push this item)
            $item = [
                "employee_id"   => $employeeId,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $params["shift"]->id ?? 0,
                "shift_type_id" => $params["shift"]->shift_type_id ?? 0,
                "total_hrs"     => ($totalMinutes > 0) ? $this->minutesToHours($totalMinutes) : "00:00",

                // Per your request: main table shows ---
                "in"            => "---",
                "out"           => "---",

                // Status: 0 logs = Default (H/W/A), Odd = MISSING, Even = PRESENT
                "status"        => ($count == 0) ? $defaultStatus : (($count % 2 !== 0) ? Attendance::MISSING : Attendance::PRESENT),
                "logs"          => json_encode($logsJson),
                "ot"            => "---",
            ];

            if ($params["isOverTime"] && $totalMinutes > 0) {
                $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
            }

            // Pushing to items ensures the row is created even for empty dates
            $items[] = $item;
        }

        // 8. Bulk Persistence
        if (count($items) > 0) {
            Attendance::where("company_id", $id)
                ->where("date", $date)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->delete();

            foreach (array_chunk($items, 100) as $chunk) {
                Attendance::insert($chunk);
            }

            if (!empty($params["UserIds"])) {
                AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", $params["UserIds"])
                    ->where("log_date", $date)
                    ->update([
                        "checked"          => true,
                        "checked_datetime" => date('Y-m-d H:i:s'),
                        "channel"          => $channel
                    ]);
            }
        }

        return "[" . $date . " " . date("H:i:s") . "] Processed " . count($items) . " records.";
    }
}
