<?php

namespace App\Http\Controllers\Visitor;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\VisitorAttendance;

class VisitorAttendanceRenderController extends Controller
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
        $visitor_ids = $request->visitor_ids;

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($startDateString);
        $endDate = new \DateTime($endDateString);
        $currentDate = new \DateTime();

        $response = [];

        while ($startDate <= $currentDate && $startDate <= $endDate) {
            $response[] = $this->render($company_id, $startDate->format("Y-m-d"), 1, $visitor_ids, true);
            $startDate->modify('+1 day');
        }

        return $response;
    }

    public function renderRequest(Request $request)
    {
        return $this->render($request->company_id ?? 0, $request->date ?? date("Y-m-d"), $request->shift_type_id, $request->UserIds, true);
    }

    public function render($id, $date, $shift_type_id, $UserIds = [], $custom_render = false)
    {


        $devicesListArray = Device::where("company_id", $id);
        $params = [
            "company_id" => $id,
            "date" => $date,
            "shift_type_id" => $shift_type_id,
            "custom_render" => $custom_render,
            "UserIds" => $UserIds,
        ];

        if (!$custom_render) {
            $params["UserIds"] = (new AttendanceLog)->getVisitorIdsForNewLogsToRender($params);
        }

        $logsEmployees =  (new AttendanceLog)->getLogsForRender($params);

        $items = [];
        $message = "";
        foreach ($logsEmployees as $key => $logs) {

            $logs = $logs->toArray() ?? [];



            $firstLog = collect($logs)->filter(fn ($record) => $record['log_type'] !== "out")->first();
            $lastLog = collect($logs)->filter(fn ($record) => $record['log_type'] !== "in")->last();


            // $schedule = $firstLog["schedule"] ?? false;
            // $shift = $schedule["shift"] ?? false;

            // if (!$schedule) {
            //     $message .= ".  No schedule is mapped with combination  System User Id: $key   and Date : " . $params["date"] . " ";
            //     continue;
            // }
            // if (!$firstLog["schedule"]["shift_type_id"]) {
            //     $message .= "$key : None f=of the  Master shift configured on  date:" . $params["date"];
            //     continue;
            // }

            $device_id = $devicesListArray->clone()->where("device_id", "=", $firstLog["DeviceID"])->pluck('id')[0];

            $item = [

                "total_hrs" => "---",
                "in" => $firstLog["time"] ?? "---",
                "out" =>  null,

                "device_id_in" => $device_id  ?? "---",
                "device_id_out" => "---",
                "date" => $params["date"],
                "date_in" => $params["date"],

                "branch_id" => $logs[0]['visitor']["branch_id"],

                "company_id" => $params["company_id"],
                "visitor_id" => $logs[0]['visitor']['id'],
                "system_user_id" => $key,
                // "shift_id" => $firstLog["schedule"]["shift_id"] ?? 0,
                // "shift_type_id" => $firstLog["schedule"]["shift_type_id"] ?? 0,
                "status" => "M",
            ];



            if (count($logs) > 1) {

                $device_id = $devicesListArray->clone()->where("device_id", "=", $lastLog["DeviceID"])->pluck('id')[0];

                $item["status"] = "P";
                $item["device_id_out"] = $device_id ?? null;
                $item["out"] = $lastLog["time"] ?? null;
                $item["date_out"] = $params["date"] ?? null;
                if ($item["out"] !== null) {
                    $item["total_hrs"] = $this->getTotalHrsMins($item["in"], $item["out"]);
                }

                $visitor_over_stay_inMin = (strtotime($lastLog["time"]) - strtotime($logs[0]['visitor']["time_out"]));


                if ($visitor_over_stay_inMin > 0) {
                    $item["over_stay"] =  gmdate("H:i:s", $visitor_over_stay_inMin);;
                }
            }
            $items[] = $item;
        }

        if (!count($items)) {
            $message = '[' . $date . " " . date("H:i:s") . '] Visitor Attendances: No data found' . $message;
            $this->devLog("visitor-attenadnce-log", $message);
            return $message;
        }



        try {
            $UserIds = array_column($items, "visitor_id");
            $model = VisitorAttendance::query();
            $model->where("company_id", $id);
            $model->whereIn("visitor_id", $UserIds);
            $model->where("date", $date);
            $model->delete();
            $model->insert($items);

            if (!$custom_render) {
                AttendanceLog::where("company_id", $id)->whereIn("UserID", $UserIds)->update(["checked" => true]);
            }
            $message = "[" . $date . " " . date("H:i:s") .  "] Visitor Attendance.  Affected Ids: " . json_encode($UserIds) . " " . $message;
        } catch (\Throwable $e) {
            $message = "[" . $date . " " . date("H:i:s") .  "] Visitor Attendance. " . $e->getMessage();
        }

        $this->devLog("visitor-attenadnce-log", $message);
        return ($message);
    }
}
