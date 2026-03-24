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

        // 1. Fetch UserIds if not provided
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
        }

        if (empty($params["UserIds"])) {
            return "[" . $date . "] No employees found to render.";
        }

        // --- CACHING STRATEGY START ---

        // Cache Holiday Check for 1 hour (Holidays rarely change mid-day)
        $isHoliday = Cache::remember("holiday_{$id}_{$date}", 3600, function () use ($id, $date) {
            return DB::table('holidays')
                ->where('company_id', $id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
        });

        // Cache Schedules for 10 minutes (Balance between speed and admin updates)
        // We cache by company_id so it serves all render requests for any user in that company.
        $allSchedules = Cache::remember("schedules_company_{$id}", 600, function () use ($id) {
            return ScheduleEmployee::with('shift')
                ->where("company_id", $id)
                ->get()
                ->groupBy("employee_id");
        });

        // --- CACHING STRATEGY END ---

        $days = ($params['shift_type_id'] == 4) ? 2 : 1;
        $logEndDate = date("Y-m-d", strtotime($params["date"] . " +" . $days . " day"));

        // Logs are NOT cached because they are highly dynamic
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
            $key = $employeeId;
            $employeeLogs = $logsEmployees->get($key, collect([]));
            $empSchedules = $allSchedules->get($key, collect([]));

            $matchedSchedule = $empSchedules->first(function ($sch) use ($dayOfWeek) {
                $days = $sch->shift->days ?? [];
                return is_array($days) && in_array($dayOfWeek, $days);
            }) ?: $empSchedules->first();

            $shiftData = $matchedSchedule->shift ?? null;

            // Default Status Logic
            if ($isHoliday) {
                $defaultStatus = "H";
            } else {
                $defaultStatus = Attendance::processWeekOffFunc(
                    $currentDayKey,
                    $shiftData['weekoff_rules'] ?? "A",
                    $id,
                    $date,
                    $key,
                    null
                );
            }

            $item = [
                "roster_id" => 0,
                "total_hrs" => "---",
                "in" => "---",
                "out" => "---",
                "ot" => "---",
                "device_id_in" => "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "company_id" => $params["company_id"],
                "employee_id" => $key,
                "shift_id" => $shiftData->id ?? 0,
                "shift_type_id" => $shiftData->shift_type_id ?? 0,
                "status" => $defaultStatus ?? "A",
                "late_coming" => "---",
                "early_going" => "---",
            ];

            if ($employeeLogs->isNotEmpty()) {
                $firstLog = $employeeLogs->first(function ($record) use ($key, $previousShifts) {
                    $prev = $previousShifts->get($key);
                    if ($prev && $prev->shift_type_id == 6) return $prev->out != $record->time;
                    $bIn = $record->schedule->shift->beginning_in ?? false;
                    $bOut = $record->schedule->shift->beginning_out ?? false;
                    return $bIn && $bOut && $record->time >= $bIn && $record->time <= $bOut;
                });

                $lastLog = $employeeLogs->last(fn($record) => in_array(strtolower($record->log_type), ["out", "auto", null]));

                if ($firstLog) {
                    $item["status"] = "P";
                    $item["in"] = $firstLog->time;
                    $item["device_id_in"] = $firstLog->DeviceID;

                    if ($shiftData) {
                        if ($item["shift_type_id"] == 6) {
                            $item["late_coming"] = $this->calculatedLateComing($item["in"], $shiftData->on_duty_time, $shiftData->late_time);
                            if ($item["late_coming"] != "---") $item["status"] = "LC";
                        }

                        if ($lastLog && $employeeLogs->count() > 1 && $firstLog->time !== $lastLog->time) {
                            $item["out"] = $lastLog->time;
                            $item["device_id_out"] = $lastLog->DeviceID;
                            $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);

                            if ($matchedSchedule->isOverTime) {
                                $otTime = $this->calculatedOT($item["total_hrs"], $shiftData->working_hours, $shiftData->overtime_interval);
                                $item["ot"] = ($otTime == "---") ? "00:00" : $otTime;
                            }

                            if ($item["shift_type_id"] == 6) {
                                $offDuty = $shiftData->off_duty_time;
                                if (($shiftData->halfday ?? '') == $dayOfWeek) {
                                    $offDuty = gmdate("H:i", (strtotime($shiftData->halfday_working_hours) - strtotime('00:00')) + strtotime($shiftData->on_duty_time) - strtotime('00:00'));
                                }
                                $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $offDuty, $shiftData->early_time);
                                if ($item["early_going"] != "---" && $item["status"] != "LC") $item["status"] = "EG";
                            }
                        }
                    }
                }
            }
            $items[] = $item;
        }

        try {
            DB::beginTransaction();
            Attendance::where("company_id", $id)->whereIn("employee_id", $params["UserIds"])->where("date", $date)->delete();
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
        }

        if (empty($params["UserIds"])) return "[" . $date . "] No employees found.";

        $isHoliday = Cache::remember(
            "holiday_{$id}_{$date}",
            3600,
            fn() =>
            DB::table('holidays')
                ->where('company_id', $id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists()
        );

        $allSchedules = Cache::remember(
            "schedules_company_{$id}",
            600,
            fn() =>
            ScheduleEmployee::with('shift')
                ->where("company_id", $id)
                ->get()
                ->groupBy("employee_id")
        );

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

                    $hasValidCheckout = $lastLog && $logCount > 1 && $firstLog->time !== $lastLog->time;

                    // ✅ Missing
                    if (!$hasValidCheckout) {
                        $item["status"] = "M";
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
                                $item["status"] = "LC";
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
                                    $item["status"] = "EG";
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

            Attendance::where("company_id", $id)
                ->whereIn("employee_id", $params["UserIds"])
                ->where("date", $date)
                ->delete();

            foreach (array_chunk($items, 200) as $chunk) {
                Attendance::insert($chunk);
            }

            DB::commit();
            $message = "[$date] Regenerate successful.";
        } catch (\Throwable $e) {
            DB::rollback();
            $message = "[$date] Error: " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);

        return $message;
    }
}
