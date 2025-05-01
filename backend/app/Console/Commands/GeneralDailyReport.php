<?php

namespace App\Console\Commands;

use App\Http\Controllers\Reports\DailyController;
use App\Jobs\GenerateAttendanceSummaryReport;
use App\Models\Attendance;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GeneralDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate_daily_report {id} {shift_type} {status?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Daily Report';

    public function handle()
    {

        $shift_type = $this->argument("shift_type") ?? "General";
        $status = $this->argument("status");
        $company_id = $this->argument("id");

        GenerateAttendanceSummaryReport::dispatch($shift_type, $status, $company_id);
        echo "Report generation dispatched to queue.\n";
        return 0;

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        $shift_type = $this->argument("shift_type") ?? "General";
        $status = $this->argument("status");
        $company_id = $this->argument("id");

        $from_date =  date("Y-04-01");
        $to_date = date("Y-04-01");
        $heading = "Summary";

        $model = Attendance::query();
        // $model->take(10);
        // $model->whereNotNull("logs");
        // $model->where("employee_id", "234");
        $model->where('company_id', $company_id);
        $model->with(['shift_type', 'last_reason', 'branch']);
        $model->whereBetween("date", [$from_date . " 00:00:00", $to_date . " 23:59:59"]);

        $model->whereHas('employee', function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
            $q->where('status', 1);
            $q->whereHas(
                "schedule",
                function ($q) use ($company_id) {
                    $q->where('company_id', $company_id);
                }
            );
        });

        $model->with([
            'employee' => function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
                $q->where('status', 1);
                $q->select('system_user_id', 'full_name', 'display_name', "department_id", "first_name", "last_name", "profile_picture", "employee_id", "branch_id", "joining_date");
                $q->with(['department', 'branch']);
                $q->with([
                    "schedule" => function ($q) use ($company_id) {
                        $q->where('company_id', $company_id);
                        $q->select("id", "shift_id", "employee_id");
                        $q->withOut("shift_type");
                    },
                    "schedule.shift" => function ($q) use ($company_id) {
                        $q->where('company_id', $company_id);
                        $q->select("id", "name", "on_duty_time", "off_duty_time");
                    }
                ]);
            }
        ]);

        $model->with('device_in', function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        });

        $model->with('device_out', function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        });

        $model->with('shift', function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        });

        $model->with('schedule', function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        });

        if ($status) {
            $model->where("status", $status);
        }

        $attendances = $model->get();

        $company = Company::whereId($company_id)->with('contact:id,company_id,number')->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);
        $company['report_type'] = $heading;
        $company['start'] = $from_date;
        $company['end'] = $to_date;

        $title =  "$heading Report - $from_date to $to_date";

        // return $attendances;

        $arr = ['shift_type' => $shift_type, "title" => $title, 'company' => $company, "attendances" => $attendances];

        $data = Pdf::loadView('pdf.attendance_reports.summary', $arr)->output();

        $file_path = "pdf/$company_id/$title.pdf";

        Storage::disk('local')->put($file_path, $data);

        echo "done";
    }

    public function handle_old()
    {
        $id = $this->argument("id");
        $status = $this->argument("status");
        if ($status == "All") $status = "-1";
        echo (new DailyController)->custom_request_general($id, $status, 1);
    }
}
