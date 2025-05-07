<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttendanceReport;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class pdfGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:generate {company_id} {system_user_id?} {from_date?} {to_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'pdf:generate';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $companyId = $this->argument("company_id");
        $fromDate = $this->argument("from_date") ?? date("Y-m-01");
        $toDate = $this->argument("to_date") ?? date("Y-m-t");
        $system_user_id = $this->argument("system_user_id");

        $requestPayload = [
            'company_id' => $companyId,
            'status' => "-1",
            'status_slug' => (new Controller)->getStatusSlug("-1"),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        $employees = Employee::whereCompanyId($requestPayload["company_id"])
            ->when($system_user_id, fn($q) => $q->where("system_user_id", $system_user_id))
            ->get();

        $company = Company::whereId($requestPayload["company_id"])->with('contact:id,company_id,number')->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);

        foreach ($employees as $employee) {
            GenerateAttendanceReport::dispatch($employee->system_user_id, $company, $employee, $requestPayload, "Template1");
            GenerateAttendanceReport::dispatch($employee->system_user_id, $company, $employee, $requestPayload, "Template2");
        }

        $this->info("Report generating in background for {$this->argument('company_id', 0)}");
    }
}
