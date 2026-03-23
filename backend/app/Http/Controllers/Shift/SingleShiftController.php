<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\API\SharjahUniversityAPI;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use App\Models\Shift;
use DateTime;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SingleShiftController extends Controller
{
    public function renderData(Request $request)
    {
        // Extract start and end dates from the JSON data
        $startDateString = $request->dates[0];
        //$endDateString = $request->dates[1];
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
            // $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 6, $employee_ids, true);
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 6, $employee_ids, $request->filled("auto_render") ? false : true, false, $request->channel ?? "unknown");

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        $version = env("VERSION");

        Log::info("Using: $version");

        if ($request->company_id == 60) {
            return $this->renderV1($request->company_id ?? 0, $request->date ?? date("Y-m-d"), $request->shift_type_id, $request->UserIds, true, false, $request->channel ?? "unknown");
        }

        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"), $request->shift_type_id, $request->UserIds, true, false, $request->channel ?? "unknown");
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $isRequestFromAutoshift = false, $channel = "unknown")
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsToRender($params);
            // return json_encode($params["UserIds"]);
        }
        $logsEmployees = [];


        if ($isRequestFromAutoshift) {
            $logsEmployees =  (new AttendanceLog)->getLogsForRenderOnlyAutoShift($params);
        } else {
            //$logsEmployees =  (new AttendanceLog)->getLogsForRender($params);
            $logsEmployees =  (new AttendanceLog)->getLogsForRenderNotAutoShift($params);
        }

        $items = [];


        // $shifts = Shift::with("employee_schedule")->where("company_id", $params["company_id"])->orderBy("id", "desc")->get()->toArray();

        $schedule = ScheduleEmployee::where("company_id", $params["company_id"])->get();



        $previousShifts = Attendance::where("company_id", $params["company_id"])
            ->whereDate("date", date("Y-m-d", strtotime($params["date"] . " -1 day")))
            ->where("shift_type_id", 4)
            ->get()
            ->keyBy("employee_id");

        foreach ($logsEmployees as $key => $logs) {


            $logs = $logs->toArray() ?? [];

            // Find the first log based on the schedule and previous shift
            $firstLog = collect($logs)->first(function ($record) use ($key, $previousShifts) {
                $previousShift = $previousShifts->get($key);

                // Validate against previous shift's out time if shift type is 6
                if ($previousShift && $previousShift->shift_type_id == 6) {
                    return $previousShift->out != $record["time"];
                }

                // Validate against schedule timings
                $beginning_in = $record["schedule"]["shift"]["beginning_in"] ?? false;
                $beginning_out = $record["schedule"]["shift"]["beginning_out"] ?? false;

                return $beginning_in && $beginning_out && $record["time"] >= $beginning_in && $record["time"] <= $beginning_out;
            });

            $lastLog = collect($logs)->last(function ($record) {
                return in_array($record["log_type"], ["Out", "out", "Auto", "auto", null], true);
            });

            $schedules = ScheduleEmployee::where("company_id", $params["company_id"])->where("employee_id", $key)->get()->toArray();

            $schedule = $firstLog["schedule"] ?? false;

            $shift =  $schedule["shift"] ?? false;

            if (!$schedule) continue;

            $dayOfWeek = date('D', strtotime($firstLog["LogTime"])); // Convert to timestamp and get the day

            foreach ($schedules as $singleSchedule) {
                $day = $singleSchedule["shift"]["days"];

                if (isset($shift["days"]) && is_array($shift["days"]) && in_array($dayOfWeek, $day, true)) {
                    $schedule = $singleSchedule ?? false;
                    $shift =  $schedule["shift"] ?? false;
                    break;
                }
            }

            $item = [
                "roster_id" => 0,
                "total_hrs" => "---",
                "in" => $firstLog["time"] ?? "---",
                "out" =>  "---",
                "ot" => "---",
                "device_id_in" =>  $firstLog["DeviceID"] ?? "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "company_id" => $params["company_id"],
                "employee_id" => $key,
                "shift_id" => $shift["id"] ?? 0,
                "shift_type_id" => $shift["shift_type_id"] ?? 0,
                "status" => "M",
                "late_coming" => "---",
                "early_going" => "---",
            ];

            if ($shift && $item["shift_type_id"] == 6) {
                $item["late_coming"] =  $this->calculatedLateComing($item["in"], $shift["on_duty_time"], $shift["late_time"]);

                if ($item["late_coming"] != "---") {
                    $item["status"] = "LC";
                }
            }

            if ($shift && $lastLog && count($logs) > 1 && $firstLog["time"] !== $lastLog["time"]) {

                $item["status"] = "P";
                $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                $item["out"] = $lastLog["time"] ?? "---";

                if ($item["out"] !== "---") {
                    $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                }

                if ($schedule["isOverTime"] ?? false) {
                    $otTime = $this->calculatedOT($item["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);

                    if ($otTime == "---") {
                        $otTime = "00:00";
                    }

                    // Convert "HH:MM" to total minutes
                    [$otHours, $otMinutes] = explode(':', $otTime);
                    $totalOtMinutes = ($otHours * 60) + $otMinutes;

                    $in = $item["in"];               // e.g. 08:20
                    $out = $item["out"];             // e.g. 19:00
                    $on_duty_time = $shift["on_duty_time"];
                    $off_duty_time = $shift["off_duty_time"] ?? null;

                    $inTime = new DateTime($in);
                    $onDutyTime = new DateTime($on_duty_time);
                    $outTime = new DateTime($out);
                    $offDutyTime = $off_duty_time ? new DateTime($off_duty_time) : null;

                    if ($shift["overtime_type"] === "Both") {
                        $item["ot"] = $otTime;
                    } else if ($shift["overtime_type"] === "After") {
                        $earlyMinutes = 0;
                        if ($inTime < $onDutyTime) {
                            $earlyDiff = $onDutyTime->diff($inTime);
                            $earlyMinutes = ($earlyDiff->h * 60) + $earlyDiff->i;
                        }

                        $totalOtMinutes = max(0, $totalOtMinutes - $earlyMinutes);
                    } else if ($shift["overtime_type"] === "Before") {
                        $lateMinutes = 0;
                        if ($offDutyTime && $outTime > $offDutyTime) {
                            $lateDiff = $outTime->diff($offDutyTime);
                            $lateMinutes = ($lateDiff->h * 60) + $lateDiff->i;
                        }

                        $totalOtMinutes = max(0, $totalOtMinutes - $lateMinutes);
                    }

                    // Convert total minutes back to HH:MM
                    $otHours = floor($totalOtMinutes / 60);
                    $otMinutes = $totalOtMinutes % 60;
                    $item["ot"] = str_pad($otHours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($otMinutes, 2, "0", STR_PAD_LEFT);
                }

                if ($item["shift_type_id"] == 6) {
                    if ($shift["halfday"] == date("l")) {
                        $time2 = $shift["on_duty_time"];
                        $time1 = $shift["halfday_working_hours"];
                        $shift["off_duty_time"] = gmdate("H:i", (strtotime($time1) - strtotime('00:00')) + strtotime($time2) - strtotime('00:00'));
                    }

                    $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $shift["off_duty_time"], $shift["early_time"]);

                    if ($item["early_going"] != "---") {
                        $item["status"] = "EG";
                    }
                }
            }

            $items[] = $item;
        }

        if (!count($items)) {
            $message = '[' . $date . " " . date("H:i:s") . '] Single Shift: No data found';
            $this->devLog("render-manual-log", $message);
            return $message;
        }

        try {

            DB::beginTransaction();
            $model = Attendance::query();
            $model->where("company_id", $id);
            $model->whereIn("employee_id", array_column($items, "employee_id"));
            $model->where("date", $date);
            $model->delete();
            DB::commit();
            $model->insert($items);
            $message = "[" . $date . " " . date("H:i:s") .  "] Single Shift.   Affected Ids: " . json_encode($UserIds);
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") .  "] Single Shift. " . $e->getMessage();

            DB::rollback();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }

    public function renderV1($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $isRequestFromAutoshift = false, $channel = "unknown")
    {
        $params = [
            "company_id"    => $id,
            "date"          => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds"       => $UserIds,
        ];

        // If no specific users provided, get ALL employees to ensure Absentees/Week-offs are recorded
        if (empty($params["UserIds"])) {
            $params["UserIds"] = Employee::where("company_id", $id)
                ->where('status', 1) // Only active employees
                ->pluck('system_user_id')
                ->toArray();
        }

        $logsEmployees = [];
        if ($isRequestFromAutoshift) {
            $logsEmployees = (new AttendanceLog)->getLogsForRenderOnlyAutoShift($params);
        } else {
            $logsEmployees = (new AttendanceLog)->getLogsForRenderNotAutoShift($params);
        }

        $previousShifts = Attendance::where("company_id", $id)
            ->whereDate("date", date("Y-m-d", strtotime($date . " -1 day")))
            ->get()
            ->keyBy("employee_id");

        $dayOfWeekThreeLetter = date('D', strtotime($date));
        $currentDayKey = Attendance::DAY_MAP[$dayOfWeekThreeLetter] ?? '';

        $holidayQuery = DB::table('holidays')
            ->where("company_id", $id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        $items = [];

        foreach ($params["UserIds"] as $employeeId) {
            // Get logs for this specific employee (handle empty case)
            $employeeLogs = $logsEmployees[$employeeId] ?? collect([]);
            $logsArray = $employeeLogs->toArray();

            // 1. Resolve Schedule and Shift
            $schedule = ScheduleEmployee::where("company_id", $id)
                ->where("employee_id", $employeeId)
                ->with('shift')
                ->first();

            $shift = $schedule->shift ?? null;

            // Skip if no shift is assigned to this user
            if (!$shift) {
                continue;
            }

            // 2. Identify first/last logs
            $firstLog = $employeeLogs->first(function ($record) use ($employeeId, $previousShifts) {
                $prev = $previousShifts->get($employeeId);
                // Handle split-shift / overnight logic
                if ($prev && $prev->shift_type_id == 6) {
                    return $prev->out != $record["time"];
                }
                $bin = $record["schedule"]["shift"]["beginning_in"] ?? null;
                $bout = $record["schedule"]["shift"]["beginning_out"] ?? null;
                return $bin && $bout && $record["time"] >= $bin && $record["time"] <= $bout;
            });

            $lastLog = $employeeLogs->last(function ($record) {
                return in_array($record["log_type"], ["Out", "out", "Auto", "auto", null], true);
            });

            // 3. Default Status Logic (Absent by default)
            $status = "A";

            if ($shift->weekoff_rules) {
                // This checks if the current day is a weekend
                $status = Attendance::processWeekOffFunc($currentDayKey, $shift->weekoff_rules, $id, $date, $employeeId, $firstLog);
            }

            if ($holidayQuery) {
                $status = "H";
            }

            // 4. Initialize Item structure
            $item = [
                "roster_id"     => 0,
                "total_hrs"     => "---",
                "in"            => $firstLog["time"] ?? "---",
                "out"           => "---",
                "ot"            => "---",
                "device_id_in"  => $firstLog["DeviceID"] ?? "---",
                "device_id_out" => "---",
                "date"          => $date,
                "company_id"    => $id,
                "employee_id"   => $employeeId,
                "shift_id"      => $shift->id ?? 0,
                "shift_type_id" => $shift->shift_type_id ?? 0,
                "status"        => $status,
                "late_coming"   => "---",
                "early_going"   => "---",
            ];

            // 5. Process Present/Late/Early if logs exist
            if ($firstLog) {
                // Initial Present status if logs exist
                if ($item["status"] !== "H" && $item["status"] !== "W") {
                    $item["status"] = "P";
                }

                // Late Coming Logic
                if (($shift->attendanc_rule_late_coming ?? 'No Action') !== 'No Action') {
                    $lcMins = calculateTimeDiff($item["in"], $shift->on_duty_time, 'late', $shift->late_time);
                    if ($lcMins) {
                        $item["late_coming"] = formatMinutes($lcMins);
                        $item["status"] = "LC";
                    }
                }

                // Significant Late Coming (Escalation)
                $sigLateRule = $shift->significant_attendanc_rule_late_coming ?? 'No Action';
                if ($sigLateRule !== 'No Action') {
                    $sigLcMins = calculateTimeDiff($item["in"], $shift->on_duty_time, 'late', $shift->absent_min_in);
                    if ($sigLcMins) {
                        $item["late_coming"] = formatMinutes($sigLcMins);
                        $item["status"] = ($sigLateRule === "Half Day") ? "HD" : "A";
                    }
                }

                // 6. Process Out Log & Overtime
                if ($employeeLogs->count() == 1) {
                    $item["status"] = "M"; // Missing Out Log
                } elseif ($lastLog && $firstLog["time"] !== $lastLog["time"]) {
                    $item["out"] = $lastLog["time"];
                    $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                    $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);

                    // OT Logic
                    if (($schedule->isOverTime ?? false) &&
                        ($shift->weekend_allowed_ot || $item["status"] != "W") &&
                        ($shift->holiday_allowed_ot || $item["status"] != "H")
                    ) {

                        $otBefore = calculateTimeDiff($item["in"], $shift->on_duty_time, 'early', '00:00') ?: 0;
                        $otAfter  = calculateTimeDiff($item["out"], $shift->off_duty_time, 'late', '00:00') ?: 0;

                        $finalOtMins = 0;
                        switch ($shift->overtime_type) {
                            case "Before":
                                $finalOtMins = $otBefore;
                                break;
                            case "After":
                                $finalOtMins = $otAfter;
                                break;
                            case "Both":
                                $finalOtMins = $otBefore + $otAfter;
                                break;
                        }

                        // Apply Interval and Cap
                        $intervalMins = $this->timeToMinutes($shift->overtime_interval);
                        if ($finalOtMins < $intervalMins) $finalOtMins = 0;

                        $allowedCapMins = $this->timeToMinutes($shift->daily_ot_allowed_mins);
                        if ($allowedCapMins > 0) $finalOtMins = min($finalOtMins, $allowedCapMins);

                        $item["ot"] = formatMinutes($finalOtMins);
                    }

                    // Early Going Logic
                    if (($shift->attendanc_rule_early_going ?? 'No Action') !== 'No Action') {
                        $egMins = calculateTimeDiff($item["out"], $shift->off_duty_time, 'early', $shift->early_time);
                        if ($egMins && !in_array($item["status"], ["HD", "A"])) {
                            $item["early_going"] = formatMinutes($egMins);
                            $item["status"] = "EG";
                        }
                    }
                }
            }

            $items[] = $item;
        }

        // Database Sync
        if (empty($items)) return "No items to process";

        try {
            DB::beginTransaction();
            Attendance::where("company_id", $id)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->whereDate("date", $date)
                ->delete();

            Attendance::insert($items);
            DB::commit();
            $message = "[$date] Success. Count: " . count($items);
        } catch (\Throwable $e) {
            DB::rollback();
            $message = "[$date] Error: " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }

    /** Helper to convert HH:mm to minutes **/
    private function timeToMinutes($time)
    {
        if (empty($time) || $time == "00:00") return 0;
        list($h, $m) = explode(':', $time);
        return ($h * 60) + (int)$m;
    }
}
