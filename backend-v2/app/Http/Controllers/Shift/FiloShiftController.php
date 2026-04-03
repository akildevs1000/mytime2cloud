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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FiloShiftController extends Controller
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
            //$response[] = $this->render($company_id, $startDate->format("Y-m-d"), 1, $employee_ids, true);


            // Determine which method to call
            $method = 'render';

            Log::info("Caling method: $method for company_id: $company_id on date: ");

            // Call the method dynamically
            $response[] = $this->$method(
                $company_id,
                $startDate->format("Y-m-d"),
                1,
                $employee_ids,
                !$request->filled("auto_render"), // Simplified boolean logic
                $request->channel ?? "unknown"
            );

            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"), $request->shift_type_id, $request->UserIds, true, $request->channel ?? "unknown");
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false, $channel = "unknown")
    {
        $params = [
            "company_id"             => $id,
            "date"                   => $date,
            "shift_type_id"          => $shift_type_id,
            "custom_render"          => $custom_render,
            "UserIds"                => $UserIds,
            "exclude_shift_type_ids" => [6, 4], // Single and Night
        ];

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getEmployeeIdsForNewLogsToRender($params);
        }

        $logsEmployees = (new AttendanceLog)->getLogsForRender($params);

        $items   = [];
        $message = "";

        // Fetch all schedules for the relevant employees in one go
        $schedules = ScheduleEmployee::with('shift')
            ->whereIn("employee_id", $params["UserIds"])
            ->where("company_id", $id)
            ->where("shift_type_id", 1)
            ->get()
            ->keyBy('employee_id');

        foreach ($params["UserIds"] as $key) {

            $isData   = $logsEmployees[$key] ?? null;
            $schedule = $schedules[$key] ?? null;
            $shift  = $schedule->shift ?? null;

            $defaultItem = [
                "employee_id"   => $key,
                "date"          => $date,
                "company_id"    => $id,
                "status"        => Attendance::determineStatus($id, $key, $date, $shift, []),
                "roster_id"     => 0,
                "total_hrs"     => "---",
                "in"            => "---",
                "out"           => "---",
                "ot"            => "---",
                "device_id_in"  => "---",
                "device_id_out" => "---",
                "shift_id"      => $shift->id ?? 0,
                "shift_type_id" => $shift->shift_type_id ?? 1,
                "late_coming"   => "---",
                "early_going"   => "---",
            ];

            // No schedule, no shift, or no logs — add default and move on
            if (!$schedule || !$shift || !$isData) {
                $items[] = $defaultItem;
                continue;
            }

            // Define shift range (handle overnight shifts)
            $onDutyStr  = $date . ' ' . $shift["on_duty_time"] . ":00";
            $offDutyStr = $date . ' ' . $shift["off_duty_time"] . ":00";

            if (strtotime($shift["off_duty_time"]) < strtotime($shift["on_duty_time"])) {
                $offDutyStr = date("Y-m-d H:i:s", strtotime($offDutyStr . " +1 day"));
            }

            $filteredLogs = collect($isData)->filter(function ($record) use ($onDutyStr, $offDutyStr) {
                return $record['LogTime'] >= $onDutyStr && $record['LogTime'] <= $offDutyStr;
            });

            // Logs exist but none fall within shift range — add default and move on
            if ($filteredLogs->isEmpty()) {
                $items[] = $defaultItem;
                continue;
            }

            $firstLog = $filteredLogs->first(fn($r) => !in_array(strtolower(trim($r['log_type'])), ['out']));
            $lastLog  = $filteredLogs->last(fn($r) => !in_array(strtolower(trim($r['log_type'])), ['in']));

            $item                 = $defaultItem;
            $item["in"]           = $firstLog["time"] ?? "---";
            $item["device_id_in"] = $firstLog["DeviceID"] ?? "---";

            if ($filteredLogs->count() == 1) {
                $item["status"] = "M";
            }

            if ($lastLog && $filteredLogs->count() > 1) {
                $item["status"]        = "P";
                $item["device_id_out"] = $lastLog["DeviceID"] ?? "---";
                $item["out"]           = $lastLog["time"] ?? "---";

                if ($item["out"] !== "---") {
                    if (strtotime($shift["on_duty_time"]) > strtotime($shift["off_duty_time"])) {
                        $diffInSeconds     = strtotime($lastLog["LogTime"]) - strtotime($firstLog["LogTime"]);
                        $totalMinutes      = round($diffInSeconds / 60);
                        $item["total_hrs"] = sprintf("%02d:%02d", floor($totalMinutes / 60), ($totalMinutes % 60));
                    } else {
                        $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                    }
                }

                if (($schedule->isOverTime ?? false) && isset($shift["working_hours"])) {
                    $item["ot"] = $this->calculatedOT($item["total_hrs"], $shift["working_hours"], $shift["overtime_interval"]);
                }
            }

            $items[] = $item;
        }

        // 4. Save to Database
        if (!count($items)) {
            $message = '[' . $date . " " . date("H:i:s") . '] No valid logs within shift range. ' . $message;
            $this->devLog("render-manual-log", $message);
            return $message;
        }

        try {
            Attendance::where("company_id", $id)
                ->whereIn("employee_id", array_column($items, "employee_id"))
                ->where("date", $date)
                ->delete();

            Attendance::insert($items);
            $message = "[" . $date . " " . date("H:i:s") . "] Filo Shift. Affected Ids: " . json_encode($params["UserIds"]) . " " . $message;
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") . "] Filo Shift. " . $e->getMessage();
        }

        $this->devLog("render-manual-log", $message);
        return $message;
    }
}
