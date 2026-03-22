<?php

namespace App\Console\Commands\v1;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PDFGenerateTemplate4 extends Command
{
    protected $signature = 'pdf:generate-template4 {company_id}';

    protected $description = 'Generate Attendance PDF Template4 via Node Puppeteer service';

    public function handle()
    {
        $company_id = $this->argument('company_id');
        $from_date  = date("Y-m-01");
        $to_date    = date("Y-m-t");

        $company = Company::whereId($company_id)->first(["name", "id"]);

        if (!$company) {
            $this->warn("Company ID $company_id not found. Skipping.");
            return 0;
        }

        $company_name = $company->name ?? "Company_$company_id";

        // ✅ Get employees
        $employees = DB::table('employees')
            ->where('company_id', $company_id)
            ->select('id', 'system_user_id')
            ->get();

        if ($employees->isEmpty()) {
            $this->warn("No employees found for company ID $company_id. Exiting.");
            return 0;
        }

        $this->info("Generating PDFs for Company: $company_id | Employees: " . $employees->pluck('id')->implode(','));

        // ✅ Create directory in public
        $reportsDirectory = public_path("reports/{$company_id}/Template4");

        if (!is_dir($reportsDirectory)) {
            mkdir($reportsDirectory, 0777, true);
        }

        foreach ($employees as $employee) {
            try {
                $this->info("👤 Employee ID: {$employee->id} (system_user_id: {$employee->system_user_id})...");

                // ✅ Call Node API
                $response = Http::withoutVerifying()->post(env('ATTENDANCE_REPORT_URL_TEMPLATE4'), [
                    'company_id'   => $company_id,
                    'company_name' => $company_name,
                    'from_date'    => $from_date,
                    'to_date'      => $to_date,
                    'employee_id'  => $employee->system_user_id,
                    'url'          => env('ATTENDANCE_REPORT_URL_APP_URL'),
                    'id'           => $employee->id,
                ]);

                if (!$response->ok()) {
                    $this->error("❌ API failed for employee {$employee->id}: " . $response->body());
                    continue;
                }

                $this->info("❌ PDF URL missing: " . json_encode($response->json()));
            } catch (\Exception $e) {
                $this->error("❌ Error for employee {$employee->id}: " . $e->getMessage());
            }
        }

        $this->info("🎉 All PDFs generated!");
        return 0;
    }
}
