<?php

namespace App\Http\Controllers\Shift;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Holidays;
use App\Models\ScheduleEmployee;
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

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel = "unknown")
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {


            $params["UserIds"] = AttendanceLog::where("company_id", $params["company_id"])
                ->when(!$params["custom_render"], fn($q) => $q->where("checked", false))
                ->where("company_id", $params["company_id"])
                ->where("LogTime", ">=", $params["date"]) // Check for logs on or after the current date
                ->where("LogTime", "<=", date("Y-m-d", strtotime($params["date"] . " +1 day"))) // Check for logs on or before the next date
                ->whereNotIn('UserID', function ($query) {
                    $query->select('system_user_id')
                        ->where('visit_from', "<=", date('Y-m-d'))
                        ->where('visit_to', ">=", date('Y-m-d'))

                        ->from('visitors');
                })

                ->whereHas("schedule", function ($q) use ($params) {
                    $q->where("company_id", $params["company_id"]);
                    $q->where("shift_type_id", 5); // Check for logs on or after the current date
                    $q->where("isAutoShift", false); // Check for logs on or after the current date
                })

                ->distinct("UserID", "company_id")
                ->pluck('UserID');
        }

        $employees = Employee::query();
        $employees->where("company_id", $params["company_id"]);
        $employees->whereIn("system_user_id", $params["UserIds"] ?? []);
        $employees->withOut(["department", "sub_department", "designation"]);
        // $employees->whereHas("attendance_logs", function ($q) use ($params) {
        //     $q->where("company_id", $params["company_id"]);
        //     $q->where("LogTime", ">=", $params["date"]); // Check for logs on or after the current date
        //     $q->where("LogTime", "<=", date("Y-m-d", strtotime($params["date"] . " +1 day"))); // Check for logs on or before the next date
        // });

        $employees->with(["schedule" => function ($q) use ($params) {
            $q->where("company_id", $params["company_id"]);
            $q->where("to_date", ">=", $params["date"]);
            $q->where("shift_type_id", 5);
            $q->withOut("shift_type");
            $q->orderBy("to_date", "asc");
        }]);

        $employees->whereHas("schedule", function ($q) use ($params) {
            $q->where("company_id", $params["company_id"]);
            $q->where("shift_type_id", 5); // Check for logs on or after the current date
        });

        $employees = $employees->get(["system_user_id"]);


        $items = [];
        $debugSummary = [];

        // $isHoliday = Holidays::isHoliday($id, $date);

        // $dayOfWeekThreeLetter = date('D', strtotime($date));
        // $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';

        foreach ($employees as $row) {
            $shift = $row->schedule->shift ?? null;
            if (!$shift) continue;


            // $shift = Attendance::processHalfDay($currentDayKey, $shift['halfday_rules'] ?? null, $shift);


            // Fetch logs and load device relationship to avoid "Undefined key" errors later
            $allLogs = AttendanceLog::with('device')
                ->where("company_id", $id)
                ->where("UserID", $row->system_user_id)
                ->whereDate('LogTime', $date)
                ->orderBy("LogTime", 'asc')
                ->get();

            $totalMinutes = 0;
            $logsJson = [];
            $userSummary = [];

            // Define the two sessions from your shift object
            $sessions = [
                [
                    'name' => 'S1',
                    'in_s' => $shift["beginning_in"],
                    'in_e' => $shift["ending_in"],
                    'out_s' => $shift["beginning_out"],
                    'out_e' => $shift["ending_out"]
                ],
                [
                    'name' => 'S2',
                    'in_s' => $shift["beginning_in1"],
                    'in_e' => $shift["ending_in1"],
                    'out_s' => $shift["beginning_out1"],
                    'out_e' => $shift["ending_out1"]
                ]
            ];

            foreach ($sessions as $ses) {
                // STRICT WINDOW FILTERING
                $validInLog = $allLogs->filter(function ($log) use ($ses) {
                    $time = Carbon::parse($log->LogTime)->format('H:i');
                    return $time >= $ses['in_s'] && $time <= $ses['in_e'];
                })->first();

                $validOutLog = $allLogs->filter(function ($log) use ($ses) {
                    $time = Carbon::parse($log->LogTime)->format('H:i');
                    return $time >= $ses['out_s'] && $time <= $ses['out_e'];
                })->last();

                $min = 0;
                if ($validInLog && $validOutLog) {
                    $min = Carbon::parse($validInLog->LogTime)->diffInMinutes(Carbon::parse($validOutLog->LogTime));
                    $totalMinutes += $min;
                }

                // Include device keys to fix the "Undefined array key" error
                if ($validInLog || $validOutLog) {
                    $inTime = $validInLog ? Carbon::parse($validInLog->LogTime)->format('H:i') : "---";
                    $outTime = $validOutLog ? Carbon::parse($validOutLog->LogTime)->format('H:i') : "---";

                    $logsJson[] = [
                        "in"            => $inTime,
                        "out"           => $outTime,
                        "device_in"     => $validInLog ? ($validInLog->device->name ?? "Device") : "---",
                        "device_out"    => $validOutLog ? ($validOutLog->device->name ?? "Device") : "---",
                        "total_minutes" => $min,
                    ];

                    $userSummary[] = "({$ses['name']}: In $inTime, Out $outTime)";
                }
            }

            $debugSummary[] = "User {$row->system_user_id}: " . (empty($userSummary) ? "No valid logs" : implode(" ", $userSummary));

            // $status = "A";

            // // Default Status Logic
            // if ($isHoliday) {
            //     $status = "H";
            // } else {
            //     $status = Attendance::processWeekOffFunc($currentDayKey, $shift['weekoff_rules'] ?? "A", $id, $date, $row->system_user_id, $allLogs->first());
            // }


            $status = Attendance::determineStatus($id, $row->system_user_id, $date, $shift, []);

            $pairCount = collect($logsJson)
                ->sum(function ($log) {
                    $count = 0;
                    if (($log['in'] ?? '---') !== '---') $count++;
                    if (($log['out'] ?? '---') !== '---') $count++;

                    return $count;
                });

            $total_hour = $this->minutesToHours($totalMinutes);

            if ($pairCount == 1) {
                $status = Attendance::MISSING;
            }


            if ($pairCount > 1) {
                $status = Attendance::PRESENT;
            }

            $items[] = [
                "employee_id"   => $row->system_user_id,
                "company_id"    => $id,
                "date"          => $date,
                "shift_id"      => $shift->id,
                "shift_type_id" => $shift->shift_type_id,
                "total_hrs"     => $this->minutesToHours($totalMinutes),
                "status"        => $status,
                "logs"          => json_encode($logsJson, JSON_PRETTY_PRINT),
            ];
        }

        // DB Update
        if (count($items) > 0) {
            Attendance::whereIn("employee_id", array_column($items, "employee_id"))
                ->where("date", $date)
                ->where("company_id", $id)
                ->delete();
            Attendance::insert($items);
        }

        return "Done for $date. Log Summary: " . implode(" | ", $debugSummary);
    }
}
