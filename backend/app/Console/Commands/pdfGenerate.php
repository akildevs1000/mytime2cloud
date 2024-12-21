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
    protected $signature = 'pdf:generate {company_id}';

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
        ini_set('memory_limit', '512M'); // Adjust to the required value

        set_time_limit(60);

        $requestPayload = [
            'company_id' => $this->argument("company_id"),
            'status' => "-1",
            'date' => date("Y-m-d", strtotime("-1 day")), // Yesterday's date
            "status_slug" => (new Controller)->getStatusSlug("-1")
        ];

        $employees = Employee::whereCompanyId($requestPayload["company_id"])->get();

        $totalEmployees = $employees->count();

        $company = Company::whereId($requestPayload["company_id"])->with('contact:id,company_id,number')->first(["logo", "name", "company_code", "location", "p_o_box_no", "id"]);
        $company['report_type'] = (new Controller)->getStatusText($requestPayload['status']);

        foreach ($employees as $index => $employee) {

            $employeeId = $employee->system_user_id;

            GenerateAttendanceReport::dispatch($index + 1, $employeeId, $company, $employee, $requestPayload, $totalEmployees);
        }

        $this->info("Report generating in background for {$this->argument('company_id', 0)}");
    }
}
