<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Console\Command;
use App\Jobs\V1\GenerateAttendanceReportPDF;
use Opcodes\LogViewer\Facades\Cache;

class pdfGenerate extends Command
{
    /**
     * Updated signature with optional company_id and template.
     * Use {arg?} for optional and {arg=default} for default values.
     */
    protected $signature = 'pdf:generatev1 {company_id?} {template=Template1}';

    protected $description = 'Generate attendance report PDFs for companies or a specific company';

    public function handle()
    {
        $fromDate  = date("Y-m-01");
        $toDate    = date("Y-m-t");
        $companyId = $this->argument("company_id");
        $template  = $this->argument("template");

        // Logic to decide if we process one company or all
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $companyIds = $query->pluck("id");

        if ($companyIds->isEmpty()) {
            $this->error("No companies found.");
            return Command::FAILURE;
        }

        $this->info("Processing " . $companyIds->count() . " company/companies...");

        foreach ($companyIds as $id) {
            $requestPayload = [
                'company_id'  => $id,
                'status'      => "-1",
                'status_slug' => "Summary",
                'from_date'   => $fromDate,
                'to_date'     => $toDate,
            ];

            $this->processReportForCompany($requestPayload, $template);
        }

        $this->info("Job dispatching completed.");
        return Command::SUCCESS;
    }

    private function processReportForCompany($requestPayload, $template)
    {
        $companyId = $requestPayload["company_id"];

        $company = Company::whereId($companyId)
            ->with('contact:id,company_id,number')
            ->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);

        if (!$company) {
            $this->warn("Company ID $companyId not found. Skipping.");
            return;
        }

        $totalEmployees = Employee::where('company_id', $companyId)->count();
        $this->info("Dispatching reports for $company->name ($totalEmployees employees) using $template");

        Cache::put("batch_total", $totalEmployees, 1800);
        Cache::put("batch_done", 0, 1800);
        Cache::put("batch_failed", 0, 1800);

        Employee::with(["schedule" => function ($q) use ($companyId) {
            $q->where("company_id", $companyId)
                ->select("id", "shift_id", "shift_type_id", "company_id", "employee_id")
                ->withOut(["shift", "shift_type"]);
        }])
            ->withOut(["branch", "designation", "sub_department", "user"])
            ->where("company_id", $companyId)
            ->chunk(50, function ($employees) use ($company, $requestPayload, $template) {
                foreach ($employees as $employee) {
                    GenerateAttendanceReportPDF::dispatch(
                        $employee->system_user_id,
                        $company,
                        $employee,
                        $requestPayload,
                        $employee->schedule->shift_type_id ?? 0, // Fallback if no schedule
                        $template
                    );
                }
                gc_collect_cycles();
            });
    }
}
