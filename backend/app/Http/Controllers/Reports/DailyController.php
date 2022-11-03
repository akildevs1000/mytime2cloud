<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DailyController extends Controller
{
    public function generateSummaryReport()
    {
        $company_ids = Company::orderBy("id", "asc")->pluck("id");

        foreach ($company_ids as $company_id) {
            $this->report($company_id, "Summary", "daily_summary.pdf");
        }
    }

    public function generatePresentReport()
    {
        $company_ids = Company::orderBy("id", "asc")->pluck("id");

        foreach ($company_ids as $company_id) {
            $this->report($company_id, "Present", "daily_present.pdf", "P");
        }
    }

    public function generateAbsentReport()
    {
        $company_ids = Company::orderBy("id", "asc")->pluck("id");

        foreach ($company_ids as $company_id) {
            $this->report($company_id, "Absent", "daily_absent.pdf", "A");
        }
    }

    public function generateMissingReport()
    {
        $company_ids = Company::orderBy("id", "asc")->pluck("id");

        foreach ($company_ids as $company_id) {
            $this->report($company_id, "Missing", "daily_missing.pdf", "---");
        }
    }

    public function generateManualReport()
    {
        $company_ids = Company::orderBy("id", "asc")->pluck("id");

        foreach ($company_ids as $company_id) {
            $this->report($company_id, "Manual Entery", "daily_manual_entery.pdf", "ME");
        }
    }

    public function report($company_id, $report_type, $file_name, $status  = null)
    {
        $date = date("Y-m-d", strtotime("yesterday"));

        $info = (object)[
            'total_employee' => Employee::whereCompanyId($company_id)->count(),
            'total_present' => $this->getCountByStatus($company_id, "P", $date),
            'total_absent' => $this->getCountByStatus($company_id, "A", $date),
            'total_missing' => $this->getCountByStatus($company_id, "---", $date),
            'total_early' => 0,
            'total_late' => 0,
            'total_leave' => 0,
            'department' => 'All',
            "daily_date" => $date,
            'report_type' => $report_type
        ];


        $model = $this->getModel($company_id,$date);

        if ($status !== null) {
            $model->where('status', $status);
        }

        $company = Company::whereId($company_id)->with('contact')->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);

        $data = $model->get();

        $pdf = Pdf::loadView('pdf.daily', compact("company", "info", "data"));

        Storage::disk('local')->put($company_id . '/' . $file_name, $pdf->output());

        return "Daily report generated.";
    }

    public function getModel($company_id,$date)
    {
        $model = Attendance::query();
        $model->where('company_id', $company_id);
        $model->whereDate('date', $date);


        $model->with([
            "employee:id,system_user_id,first_name,employee_id,department_id,profile_picture",
            "device_in:id,name,short_name,device_id,location",
            "device_out:id,name,short_name,device_id,location",
            "schedule.shift:id,name,working_hours,overtime_interval,on_duty_time,off_duty_time,late_time,early_time,beginning_in,ending_in,beginning_out,ending_out,absent_min_in,absent_min_out,days",
            "schedule.shift_type:id,name",
        ]);

        return $model;
    }

    public function getCountByStatus($company_id, $status, $date)
    {
        return DB::table("attendances")->where("company_id", $company_id)->whereDate('date', $date)->where('status', $status)->count();
    }
}
