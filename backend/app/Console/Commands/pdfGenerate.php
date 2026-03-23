<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Console\Command;
use App\Jobs\V1\GenerateAttendanceReportPDF;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class pdfGenerate extends Command
{
    /**
     * Updated signature:
     * Added {--employees=*} to accept multiple IDs: --employees=1 --employees=2
     */
    protected $signature = 'pdf:generatev1 
                            {company_id?} 
                            {template=Template1} 
                            {from?} 
                            {to?} 
                            {--employees=*}';

    protected $description = 'Generate attendance report PDFs for companies with optional date range and employee filter';

    public function handle()
    {
        // 1. Determine Dates
        $fromDate = $this->argument("from") ?: Carbon::now()->startOfMonth()->toDateString();
        $toDate   = $this->argument("to")   ?: Carbon::now()->endOfMonth()->toDateString();
        
        $companyId   = $this->argument("company_id");
        $template    = $this->argument("template");
        $employeeIds = $this->option("employees"); // Retrieve the array

        $this->info("Period: $fromDate to $toDate");

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

            $this->processReportForCompany($requestPayload, $template, $employeeIds);
        }

        $this->info("Job dispatching completed.");
        return Command::SUCCESS;
    }

    private function processReportForCompany($requestPayload, $template, array $employeeIds = [])
    {
        $companyId = $requestPayload["company_id"];

        $company = Company::whereId($companyId)
            ->with('contact:id,company_id,number')
            ->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);

        if (!$company) {
            $this->warn("Company ID $companyId not found. Skipping.");
            return;
        }

        // Initialize Employee Query
        $employeeQuery = Employee::where("company_id", $companyId);

        // Filter by IDs if provided (assuming system_user_id is the reference used in your Job)
        if (!empty($employeeIds)) {
            $employeeQuery->whereIn('system_user_id', $employeeIds);
        }

        $totalEmployees = $employeeQuery->count();
        
        if ($totalEmployees === 0) {
            $this->warn("No matching employees found for Company $company->name.");
            return;
        }

        $this->info("Dispatching reports for $company->name ($totalEmployees employees) using $template");

        // Set Cache for progress tracking
        Cache::put("batch_total", $totalEmployees, 1800);
        Cache::put("batch_done", 0, 1800);
        Cache::put("batch_failed", 0, 1800);

        $employeeQuery->with(["schedule" => function ($q) use ($companyId) {
            $q->where("company_id", $companyId)
                ->select("id", "shift_id", "shift_type_id", "company_id", "employee_id")
                ->withOut(["shift", "shift_type"]);
        }])
        ->withOut(["branch", "designation", "sub_department", "user"])
        ->chunk(50, function ($employees) use ($company, $requestPayload, $template) {
            foreach ($employees as $employee) {
                GenerateAttendanceReportPDF::dispatch(
                    $employee->system_user_id,
                    $company,
                    $employee,
                    $requestPayload,
                    $employee->schedule->shift_type_id ?? 0,
                    $template
                );
            }
            gc_collect_cycles();
        });
    }
}