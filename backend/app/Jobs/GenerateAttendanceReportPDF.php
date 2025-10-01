<?php
namespace App\Jobs;

use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAttendanceReportPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(
        public $employeeId,
        public $company,
        public $employee,
        public $requestPayload,
        public $shift_type_id,
        public $template
    ) {}

    public function handle()
    {
        ini_set('memory_limit', '1G'); // or '1G' if needed

        $model = $this->getModel($this->requestPayload);

        $data = $model->get();

        $collection = $model->clone()->get();

        $info = (object) [
            'total_absent'   => $model->clone()->where('status', 'A')->count(),
            'total_present'  => $model->clone()->where('status', 'P')->count(),
            'total_off'      => $model->clone()->where('status', 'O')->count(),
            'total_missing'  => $model->clone()->where('status', 'M')->count(),
            'total_leave'    => $model->clone()->where('status', 'L')->count(),
            'total_holiday'  => $model->clone()->where('status', 'H')->count(),
            'total_late'     => $model->clone()->where('late_coming', '!=', '---')->count(),
            'total_early'    => $model->clone()->where('early_going', '!=', '---')->count(),
            'total_hours'    => getTotalHours(array_column($collection->toArray(), 'total_hrs')),
            'total_ot_hours' => getTotalHours(array_column($collection->toArray(), 'ot')),
            'report_type'    => $this->requestPayload["status_slug"],
        ];

        info(showJson($info));

        $arr = [
            'data'          => $data,
            'company'       => $this->company,
            'info'          => $info,
            "employee"      => $this->employee,
            "shift_type_id" => $this->shift_type_id ?? 0,
            "from_date"     => $this->requestPayload["from_date"],
            "to_date"       => $this->requestPayload["to_date"],
        ];

        $template = $this->template;

        $reportsDirectory = public_path("reports/{$this->requestPayload["company_id"]}/{$template}");

        if (! is_dir($reportsDirectory)) {
            mkdir($reportsDirectory, 0777, true);
        }

        $output   = Pdf::loadView("pdf.attendance_reports.{$template}-new", $arr)->output();
        $filePath = $reportsDirectory . DIRECTORY_SEPARATOR . "Attendance_Report_{$template}_{$this->employeeId}.pdf";

        file_put_contents($filePath, $output);

        echo "\nFile created at {$filePath}\n";

    }

    public function getModel($requestPayload)
    {
        $model = Attendance::query();

        $model->where('company_id', $requestPayload["company_id"]);

        $model->with(['shift_type', 'last_reason', 'branch']);

        if (! empty($requestPayload["status"])) {
            if ($requestPayload["status"] != "-1") {
                $model->where('status', $requestPayload["status"]);
            }

            if ($requestPayload["status"] == "ME") {
                $model->where('is_manual_entry', true);
            }

            if ($requestPayload["status"] == "LC") {
                $model->where('late_coming', "!=", "---");
            }

            if ($requestPayload["status"] == "EG") {
                $model->where('early_going', "!=", "---");
            }

            if ($requestPayload["status"] == "OT") {
                $model->where('ot', "!=", "---");
            }
        }

        $model->whereHas('employee', function ($q) {
            $q->where('company_id', $this->requestPayload["company_id"]);
            $q->where('status', 1);
            $q->select('system_user_id', 'display_name', "department_id", "first_name", "last_name", "profile_picture", "employee_id", "branch_id");
            $q->with(['department', 'branch']);
        });

        $model->with([
            'employee' => function ($q) {
                $q->where('company_id', $this->requestPayload["company_id"]);
                $q->where('status', 1);
                $q->select('system_user_id', 'full_name', 'display_name', "department_id", "first_name", "last_name", "profile_picture", "employee_id", "branch_id");
                $q->with(['department', 'branch']);
            },
        ]);

        $model->with('device_in', function ($q) {
            $q->where('company_id', $this->requestPayload["company_id"]);
        });

        $model->with('device_out', function ($q) {
            $q->where('company_id', $this->requestPayload["company_id"]);
        });

        $model->with('shift', function ($q) {
            $q->where('company_id', $this->requestPayload["company_id"]);
        });

        $model->with('schedule', function ($q) {
            $q->where('company_id', $this->requestPayload["company_id"]);
        });

        $model->whereDoesntHave('device_in', fn($q) => $q->where('device_type', 'Access Control'));

        $model->whereDoesntHave('device_out', fn($q) => $q->where('device_type', 'Access Control'));

        $model->where('employee_id', $this->employeeId);

        $model->whereBetween('date', [$this->requestPayload["from_date"], $this->requestPayload["to_date"]]);

        $model->orderBy('date', 'asc');

        return $model;
    }
}
