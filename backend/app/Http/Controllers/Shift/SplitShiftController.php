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

        // 1. Holiday Check (Cached)
        $isHoliday = Cache::remember("holiday_{$id}_{$date}", 3600, function () use ($id, $date) {
            return DB::table('holidays')
                ->where('company_id', $id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
        });

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsNightToRender($params);
        }

        $employees = (new Employee)->attendanceEmployeeForMultiRender($params);

        $items = [];
        $message = "";

        foreach ($employees as $row) {
            $employeeId = $row->system_user_id;
            $params["isOverTime"] = $row->schedule->isOverTime ?? false;
            $params["shift"]      = $row->schedule->shift ?? false;

            if (!$params["shift"]) {
                $message .= "{$employeeId}: No shift; ";
                continue;
            }

            // 2. Default Status (H/W/A)
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

            // 3. Fetch Logs & Deduplicate
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

            // 4. Sequential Pairing Logic for the 'logs' column
            while ($i < $count) {
                $currentLog = $data[$i];
                $nextLog    = $data[$i + 1] ?? null;

                $inTimeRaw  = $currentLog->LogTime;
                $outTimeRaw = "---";
                $minutes    = 0;

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
                    // Single Log - Output '---' for the out time in JSON
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

            // 5. Prepare Attendance Row (Setting main IN/OUT to '---' as requested)
            $item = [
                "employee_id"   => $employeeId,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $params["shift"]->id ?? 0,
                "shift_type_id" => $params["shift"]->shift_type_id ?? 0,
                "total_hrs"     => ($totalMinutes > 0) ? $this->minutesToHours($totalMinutes) : "00:00",

                // Main columns are kept as '---' for this multi-shift function
                "in"            => "---",
                "out"           => "---",

                // Status Logic: 0 logs = Default (H/W/A), Odd = Missing, Even = Present
                "status"        => ($count == 0) ? $defaultStatus : (($count % 2 !== 0) ? Attendance::MISSING : Attendance::PRESENT),

                // All pair data is stored here
                "logs"          => json_encode($logsJson),
                "ot"            => "---",
            ];

            if ($params["isOverTime"] && $totalMinutes > 0) {
                $item["ot"] = $this->calculatedOT($item["total_hrs"], $params["shift"]->working_hours, $params["shift"]->overtime_interval);
            }

            $items[] = $item;
        }

        // 6. Bulk Save
        if (count($items) > 0) {
            Attendance::where("company_id", $id)
                ->where("date", $date)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->delete();

            foreach (array_chunk($items, 100) as $chunk) {
                Attendance::insert($chunk);
            }

            if (!empty($UserIds)) {
                AttendanceLog::where("company_id", $id)
                    ->whereIn("UserID", $UserIds)
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
