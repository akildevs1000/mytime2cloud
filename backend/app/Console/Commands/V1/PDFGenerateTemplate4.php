<?php

namespace App\Console\Commands\v1;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PDFGenerateTemplate4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:generate-template4 {company_id} {employee_ids*}'; // accept multiple employees

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Attendance PDF Template4 via Node Puppeteer service';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $company_id = $this->argument('company_id');
        $from_date  = date("Y-m-01");
        $to_date    = date("Y-m-t");

        $company = Company::whereId($company_id)->first(["name", "id"]);

        if (!$company) {
            $this->warn("Company ID $company_id not found. Skipping.");
            return;
        }

        $company_name = $company->name ?? "Company_$company_id";

        $employeeIds = $this->argument('employee_ids'); // array

        $this->info("Generating PDFs for Company: $company_id | Employees: " . implode(',', $employeeIds));

        $storagePath = "reports/{$company_id}/Template4";
        Storage::disk('public')->makeDirectory($storagePath);

        foreach ($employeeIds as $employee_id) {
            try {
                $this->info("👤 Employee: $employee_id ...");

                // Call Node service
                $response = Http::withoutVerifying()->post('http://localhost:5000/generate-pdf', [
                    'company_id' => $company_id,
                    'company_name' => $company_name,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'employee_id' => $employee_id,
                ]);

                if ($response->ok()) {
                    $pdfPath = $response->json()['pdf_path'];
                    $fileName = basename($pdfPath);

                    // Copy PDF to public/reports/company_id/Template4
                    copy($pdfPath, public_path("$storagePath/$fileName"));

                    $this->info("✅ Saved: $storagePath/$fileName");
                } else {
                    $this->error("❌ Failed to generate PDF for $employee_id: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("❌ Error for $employee_id: " . $e->getMessage());
            }
        }

        $this->info("🎉 All PDFs generated!");
        return 0;
    }
}
