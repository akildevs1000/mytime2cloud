<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ReportNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DailyController extends Controller
{
    public function generateDailyReport()
    {
        $company_ids = Company::orderBy("id","asc")->pluck("id");

        foreach ($company_ids as $company_id) {

            $info = (object)[
                'total_employee' => Employee::whereCompanyId($company_id)->count(),
                'total_early' => 0,
                'total_late' => 0,
                'total_leave' => 0,
                'department' => 'All',
                "daily_date" => date("Y-m-d"),
                "report_type" => 'Present'
            ];

            $db = DB::table('attendances')
                ->where('company_id', $company_id)
                ->whereDate('date', date("Y-m-d"))
                ->select('status', DB::raw('count(status) as count'))
                ->groupBy('status')
                ->get();

            foreach ($db as $db) {
                if ($db->status == "P") {
                    $info->total_present = $db->count;
                } else if ($db->status == "A") {
                    $info->total_absent = $db->count;
                } else if ($db->status == "---") {
                    $info->total_missing = $db->count;
                }
            }

            $payload = [
                "company" => Company::whereId($company_id)->with('contact')->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]),
                "info" => $info
            ];

            $this->report($company_id, $payload); //115
            $this->report($company_id, $payload, "P"); // 18
            $this->report($company_id, $payload, "A"); //0
            $this->report($company_id, $payload, "---"); // 97
        }
    }

    public function report($company_id, $payload, $status  = null)
    {
        $model = Attendance::query();
        $model->whereDate('date', date("Y-m-d"));
        $model->where('company_id', $company_id);

        $model->with([
            "employee:id,system_user_id,first_name,employee_id,department_id,profile_picture",
            "device_in:id,name,short_name,device_id,location",
            "device_out:id,name,short_name,device_id,location",
            "schedule.shift:id,name,working_hours,overtime_interval,on_duty_time,off_duty_time,late_time,early_time,beginning_in,ending_in,beginning_out,ending_out,absent_min_in,absent_min_out,days",
            "schedule.shift_type:id,name",
        ]);

        if ($status !== null) {
            $model->where('status', $status);
        }

        $company = $payload["company"];
        $info = $payload["info"];

        $data = [$company_id, $model->count()];

        $pdf = Pdf::loadView('pdf.daily', compact("company", "info", "data"));

        Storage::disk('local')->put($company_id . '/' . "daily_summary.pdf", $pdf->output());
    }
}
