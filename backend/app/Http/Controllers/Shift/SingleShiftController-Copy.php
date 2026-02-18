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
use Illuminate\Support\Facades\DB;

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

        $items = [];

        // Mapping for Day Keys
        $dayMap = [
            'Mon' => 'M',
            'Tue' => 'T',
            'Wed' => 'W',
            'Thu' => 'Th',
            'Fri' => 'F',
            'Sat' => 'S',
            'Sun' => 'Su'
        ];
        $dayOfWeekThreeLetter = date('D', strtotime($date));
        $currentDayKey = $dayMap[$dayOfWeekThreeLetter] ?? '';

        // LOOP THROUGH ALL USERS (This ensures Week-offs and Absentees are processed)
        foreach ($params["UserIds"] as $employeeId) {

            // Get logs for this specific employee from the collection
            $logs = isset($logsEmployees[$employeeId]) ? $logsEmployees[$employeeId]->toArray() : [];

            // 1. Identify first/last logs
            $firstLog = collect($logs)->first(function ($record) use ($employeeId, $previousShifts) {
                $previousShift = $previousShifts->get($employeeId);
                if ($previousShift && $previousShift->shift_type_id == 6) {
                    return $previousShift->out != $record["time"];
                }
                $beginning_in = $record["schedule"]["shift"]["beginning_in"] ?? false;
                $beginning_out = $record["schedule"]["shift"]["beginning_out"] ?? false;
                return $beginning_in && $beginning_out && $record["time"] >= $beginning_in && $record["time"] <= $beginning_out;
            });

            $lastLog = collect($logs)->last(function ($record) {
                return in_array($record["log_type"], ["Out", "out", "Auto", "auto", null], true);
            });

            // 2. Resolve Schedule and Shift (even if no logs exist)
            $schedule = $firstLog["schedule"] ?? ScheduleEmployee::where("company_id", $id)
                ->where("employee_id", $employeeId)
                ->with('shift')
                ->first();

            $shift = $schedule["shift"] ?? false;

            if (!$shift) continue;

            $shift = Attendance::processHalfDay($currentDayKey, $shift['halfday_rules'] ?? null, $shift);

            $status = Attendance::processWeekOffFunc($currentDayKey, $shift['weekoff_rules'] ?? "A", $id, $date, $employeeId, $firstLog);

            // If the function didn't return "O", decide if they are Absent or Present
            if (!$status) {
                $status = $firstLog ? "M" : "A";
            }

            // 4. Initialize Item
            $item = [
                "roster_id" => 0,
                "total_hrs" => "---",
                "in" => $firstLog["time"] ?? "---",
                "out" => "---",
                "ot" => "---",
                "device_id_in" => $firstLog["DeviceID"] ?? "---",
                "device_id_out" => "---",
                "date" => $date,
                "company_id" => $id,
                "employee_id" => $employeeId,
                "shift_id" => $shift["id"] ?? 0,
                "shift_type_id" => $shift["shift_type_id"] ?? 0,
                "status" => $status,
                "late_coming" => "---",
                "early_going" => "---",
            ];

            // 5. Process Logs if they exist
            if ($firstLog) {
                $item["status"] = "M"; // Missing out by default

                if ($item["shift_type_id"] == 6) {
                    $item["late_coming"] = $this->calculatedLateComing($item["in"], $shift["on_duty_time"], $shift["late_time"]);
                    if ($item["late_coming"] != "---") {
                        $item["status"] = "LC";
                    }
                }

                // Check for valid Out log
                if ($lastLog && count($logs) > 1 && $firstLog["time"] !== $lastLog["time"]) {
                    $item["status"] = "P";
                    $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                    $item["out"] = $lastLog["time"] ?? "---";

                    if ($item["out"] !== "---") {
                        $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                    }

                    // OT Calculation
                    if ($schedule["isOverTime"] ?? false) {
                        $otTime = $this->calculatedOT($item["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                        if ($otTime == "---") $otTime = "00:00";

                        [$otHours, $otMinutes] = explode(':', $otTime);
                        $totalOtMinutes = ($otHours * 60) + $otMinutes;

                        $inTime = new \DateTime($item["in"]);
                        $onDutyTime = new \DateTime($shift["on_duty_time"]);
                        $outTime = new \DateTime($item["out"]);
                        $offDutyTime = isset($shift["off_duty_time"]) ? new \DateTime($shift["off_duty_time"]) : null;

                        if ($shift["overtime_type"] === "After") {
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

                        $otH = floor($totalOtMinutes / 60);
                        $otM = $totalOtMinutes % 60;
                        $item["ot"] = str_pad($otH, 2, "0", STR_PAD_LEFT) . ":" . str_pad($otM, 2, "0", STR_PAD_LEFT);
                    }

                    // Early Going Check
                    if ($item["shift_type_id"] == 6) {
                        $item["early_going"] = $this->calculatedEarlyGoing($item["out"], $shift["off_duty_time"], $shift["early_time"]);
                        if ($item["early_going"] != "---") {
                            $item["status"] = "EG";
                        }
                    }
                }
            }

            $items[] = $item;
        }

        // Database Operations
        if (!count($items)) return "No items to process";

        try {
            DB::beginTransaction();
            Attendance::where("company_id", $id)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->where("date", $date)
                ->delete();

            Attendance::insert($items);
            DB::commit();
            $message = "[$date] Success. Affected Ids: " . json_encode($params["UserIds"]);
        } catch (\Throwable $e) {
            DB::rollback();
            $message = "[$date] Error: " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }
}
