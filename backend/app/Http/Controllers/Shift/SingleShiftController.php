<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\API\SharjahUniversityAPI;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Holidays;
use App\Models\ScheduleEmployee;
use App\Models\Shift;
use DateTime;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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
            if ($company_id == 60) {
                $response[] = $this->render_new($company_id, $startDate->format("Y-m-d"), 6, $employee_ids, $request->filled("auto_render") ? false : true, false, $request->channel ?? "unknown");
            } else {
                $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 6, $employee_ids, $request->filled("auto_render") ? false : true, false, $request->channel ?? "unknown");
            }
            // $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 6, $employee_ids, true);

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        $version = env("VERSION");

        Log::info("Using: $version");

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


        $dayOfWeek = date('D', strtotime($date));
        $currentDayKey = Attendance::DAY_MAP[$dayOfWeek] ?? '';

        $isHoliday = Holidays::isHoliday($id, $date);

        foreach ($logsEmployees as $key => $logs) {


            $defaultStatus = $isHoliday
                ? "H"
                : Attendance::processWeekOffFunc($currentDayKey, $shiftData['weekoff_rules'] ?? "A", $id, $date, $key, null);



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
                "status" =>  $defaultStatus ?? "A",
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

                if (count($logs) == 1) {
                    $item["status"] = "M";
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


    public function render_new($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $isRequestFromAutoshift = false, $channel = "unknown")
    {
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {
            $params["UserIds"] = AttendanceLog::where("company_id", $params["company_id"])
                ->when(!$params["custom_render"], fn($q) => $q->where("checked", false))
                ->where("LogTime", ">=", $params["date"])
                ->where("LogTime", "<=", date("Y-m-d", strtotime($params["date"] . " +1 day")))
                ->whereNotIn('UserID', function ($query) {
                    $query->select('system_user_id')
                        ->where('visit_from', "<=", date('Y-m-d'))
                        ->where('visit_to', ">=", date('Y-m-d'))
                        ->from('visitors');
                })
                ->whereHas("schedule", fn($q) => $q->where("isAutoShift", $isRequestFromAutoshift))
                ->distinct("UserID")
                ->pluck('UserID')
                ->toArray();
        } else {
            if ($id == 60) {
                $params["UserIds"] = AttendanceLog::where("company_id", $params["company_id"])
                    ->when(!$params["custom_render"], fn($q) => $q->where("checked", false))
                    ->where("LogTime", ">=", $params["date"])
                    ->where("LogTime", "<=", date("Y-m-d", strtotime($params["date"] . " +1 day")))
                    ->whereNotIn('UserID', function ($query) {
                        $query->select('system_user_id')
                            ->where('visit_from', "<=", date('Y-m-d'))
                            ->where('visit_to', ">=", date('Y-m-d'))
                            ->from('visitors');
                    })
                    ->whereHas("employee.schedule", function ($q) use ($params, $isRequestFromAutoshift) {
                        $q->where("company_id", $params["company_id"]);
                        $q->where("shift_type_id", 6);
                        $q->where("isAutoShift", $isRequestFromAutoshift);
                    })
                    ->distinct("UserID")
                    ->pluck('UserID')
                    ->toArray();
            }
        }

        if (empty($params["UserIds"])) return "[" . $date . "] No employees found.";

        $isHoliday = Holidays::isHoliday($id, $date);

        // $allSchedules = Cache::remember(
        //     "schedules_company_{$id}",
        //     600,
        //     fn() =>
        //     ScheduleEmployee::with('shift')
        //         ->where("company_id", $id)
        //         ->get()
        //         ->groupBy("employee_id")
        // );

        $allSchedules =  ScheduleEmployee::with('shift')
            ->where("company_id", $id)
            ->get()
            ->groupBy("employee_id");

        $days = ($params['shift_type_id'] == 4) ? 2 : 1;
        $logEndDate = date("Y-m-d", strtotime($params["date"] . " +" . $days . " day"));

        $logsEmployees = AttendanceLog::with(["visitor", "device", "schedule.shift"])
            ->where("company_id", $params["company_id"])
            ->where("LogTime", ">=", $params["date"])
            ->where("LogTime", "<=", $logEndDate)
            ->whereIn("UserID", $params["UserIds"])
            ->orderBy("LogTime", "asc")
            ->get()
            ->groupBy('UserID');

        $previousShifts = Attendance::where("company_id", $params["company_id"])
            ->whereDate("date", date("Y-m-d", strtotime($params["date"] . " -1 day")))
            ->where("shift_type_id", 4)
            ->get()
            ->keyBy("employee_id");

        $items = [];
        $dayOfWeek = date('D', strtotime($date));
        $currentDayKey = Attendance::DAY_MAP[$dayOfWeek] ?? '';

        foreach ($params["UserIds"] as $employeeId) {

            $employeeLogs = $logsEmployees->get($employeeId, collect([]));
            $empSchedules = $allSchedules->get($employeeId, collect([]));

            $matchedSchedule = $empSchedules->first(fn($sch) => in_array($dayOfWeek, $sch->shift->days ?? [])) ?: $empSchedules->first();
            $shiftData = $matchedSchedule->shift ?? null;


            $shiftData = Attendance::processHalfDay($currentDayKey, $shiftData['halfday_rules'] ?? null, $shiftData);


            $defaultStatus = $isHoliday
                ? "H"
                : Attendance::processWeekOffFunc($currentDayKey, $shiftData['weekoff_rules'] ?? "A", $id, $date, $employeeId, null);

            $item = [
                "roster_id" => 0,
                "total_hrs" => "---",
                "in" => "---",
                "out" => "---",
                "ot" => "---",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "date" => $date,
                "company_id" => $id,
                "employee_id" => $employeeId,
                "shift_id" => $shiftData->id ?? 0,
                "shift_type_id" => $shiftData->shift_type_id ?? 0,
                "status" => $defaultStatus ?? "A",
                "late_coming" => "---",
                "early_going" => "---",
            ];

            if ($employeeLogs->isNotEmpty()) {

                $logCount = $employeeLogs->count();

                $firstLog = $employeeLogs->first(function ($record) use ($employeeId, $previousShifts) {
                    $prev = $previousShifts->get($employeeId);
                    if ($prev && $prev->shift_type_id == 6) return $prev->out != $record->time;

                    $bIn = $record->schedule->shift->beginning_in ?? false;
                    $bOut = $record->schedule->shift->beginning_out ?? false;

                    return $bIn && $bOut && $record->time >= $bIn && $record->time <= $bOut;
                });

                $lastLog = $employeeLogs->last(
                    fn($record) =>
                    in_array(strtolower($record->log_type), ["out", "auto", null])
                );

                if ($firstLog) {

                    $item["in"] = $firstLog->time;
                    $item["device_id_in"] = $firstLog->DeviceID;
                    $item["status"] = "M";

                    $hasValidCheckout = $lastLog && $logCount > 1 && $firstLog->time !== $lastLog->time;

                    if ($hasValidCheckout) {
                        $item["status"] = "P";
                    }

                    if ($shiftData) {

                        // ✅ Late Coming
                        if (($shiftData->attendanc_rule_late_coming) !== 'No Action') {
                            $item["late_coming"] = $this->calculatedLateComing(
                                $item["in"],
                                $shiftData->on_duty_time,
                                $shiftData->late_time
                            );

                            if ($item["late_coming"] != "---") {
                                // $item["status"] = "LC";
                            }
                        }

                        // ✅ Significant Late
                        if (($shiftData->significant_attendanc_rule_late_coming) !== 'No Action') {
                            if ($item["late_coming"] != "---") {
                                $item["status"] =
                                    ($shiftData->significant_attendanc_rule_late_coming === "Half Day")
                                    ? "HD"
                                    : "A";
                            }
                        }

                        if ($hasValidCheckout) {

                            $item["out"] = $lastLog->time;
                            $item["device_id_out"] = $lastLog->DeviceID;
                            $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);

                            if ($matchedSchedule->isOverTime) {
                                $otTime = $this->calculatedOT(
                                    $item["total_hrs"],
                                    $shiftData->working_hours,
                                    $shiftData->overtime_interval
                                );
                                $item["ot"] = ($otTime == "---") ? "00:00" : $otTime;
                            }

                            $offDuty = $shiftData->off_duty_time;

                            if (($shiftData->halfday ?? '') == $dayOfWeek) {
                                $offDuty = gmdate(
                                    "H:i",
                                    (strtotime($shiftData->halfday_working_hours) - strtotime('00:00'))
                                        + strtotime($shiftData->on_duty_time)
                                        - strtotime('00:00')
                                );
                            }

                            // ✅ Early Going
                            if (($shiftData->attendanc_rule_early_going) !== 'No Action') {
                                $item["early_going"] = $this->calculatedEarlyGoing(
                                    $item["out"],
                                    $offDuty,
                                    $shiftData->early_time
                                );

                                if ($item["early_going"] != "---") {
                                    // $item["status"] = "EG";
                                }
                            }

                            // ✅ Significant Early
                            if (($shiftData->significant_attendanc_rule_early_going) !== 'No Action') {
                                if ($item["early_going"] != "---") {
                                    $item["status"] =
                                        ($shiftData->significant_attendanc_rule_early_going === "Half Day")
                                        ? "HD"
                                        : "A";
                                }
                            }

                            // ✅ Present (ONLY if clean)
                            if (!in_array($item["status"], ["LC", "EG", "HD", "A"])) {
                                $item["status"] = "P";
                            }
                        }
                    }
                }
            }

            $items[] = $item;
        }


        try {
            DB::beginTransaction();
            Attendance::where("company_id", $id)->whereIn("employee_id", $params["UserIds"])
                ->where("date", $date)
                // ->where("shift_type_id", $params["shift_type_id"]) // use the param passed in
                ->delete();
            foreach (array_chunk($items, 200) as $chunk) {
                Attendance::insert($chunk);
            }
            DB::commit();
            $message = "[$date] Render successful. Cached Data Used.";
        } catch (\Throwable $e) {
            DB::rollback();
            $message = "[$date] Error: " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }
}
