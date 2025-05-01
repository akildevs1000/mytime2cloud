<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Bus\Dispatchable; // <== this is missing!


class GenerateAttendanceSummaryReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $shift_type;
    protected $status;
    protected $company_id;

    public function __construct($shift_type, $status, $company_id)
    {
        $this->shift_type = $shift_type ?? 'General';
        $this->status = $status;
        $this->company_id = $company_id;
    }

    public function handle()
    {
        ini_set('memory_limit', '512M');
        // ini_set('max_execution_time', 300);

        $from_date = date("Y-04-01");
        $to_date = date("Y-04-01");
        $heading = "Summary";

        $model = Attendance::query();
        $model->where('company_id', $this->company_id);
        $model->whereBetween("date", [$from_date . " 00:00:00", $to_date . " 23:59:59"]);
        $model->with(['shift_type', 'last_reason', 'branch']);

        $model->whereHas('employee', function ($q) {
            $q->where('company_id', $this->company_id)
                ->where('status', 1)
                ->whereHas("schedule", function ($q) {
                    $q->where('company_id', $this->company_id);
                });
        });

        $model->with([
            'employee' => function ($q) {
                $q->where('company_id', $this->company_id)
                    ->where('status', 1)
                    ->select('system_user_id', 'full_name', 'display_name', "department_id", "first_name", "last_name", "profile_picture", "employee_id", "branch_id", "joining_date")
                    ->with(['department', 'branch'])
                    ->with([
                        "schedule" => function ($q) {
                            $q->where('company_id', $this->company_id)
                                ->select("id", "shift_id", "employee_id")
                                ->withOut("shift_type");
                        },
                        "schedule.shift" => function ($q) {
                            $q->where('company_id', $this->company_id)
                                ->select("id", "name", "on_duty_time", "off_duty_time");
                        }
                    ]);
            },
            'device_in' => fn($q) => $q->where('company_id', $this->company_id),
            'device_out' => fn($q) => $q->where('company_id', $this->company_id),
            'shift' => fn($q) => $q->where('company_id', $this->company_id),
            'schedule' => fn($q) => $q->where('company_id', $this->company_id),
        ]);

        if ($this->status) {
            $model->where("status", $this->status);
        }

        $attendances = $model->get();

        $company = Company::whereId($this->company_id)
            ->with('contact:id,company_id,number')
            ->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);

        $company['report_type'] = $heading;
        $company['start'] = $from_date;
        $company['end'] = $to_date;

        $title = "$heading Report - $from_date to $to_date";

        $arr = [
            'shift_type' => $this->shift_type,
            'title' => $title,
            'company' => $company,
            'attendances' => $attendances
        ];

        $data = Pdf::loadView('pdf.attendance_reports.summary', $arr)->output();
        $file_path = "pdf/{$this->company_id}/summary_report.pdf";
        Storage::disk('local')->put($file_path, $data);
    }
}
