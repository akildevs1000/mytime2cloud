<?php

namespace App\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAccessControlReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunk;
    protected $companyId;
    protected $params;
    protected $batchKey;
    protected $company;
    protected $totalPages;

    public function __construct($chunk, $companyId, $params,  $company, $batchKey, $totalPages)
    {
        $this->chunk = $chunk;
        $this->companyId = $companyId;
        $this->params = $params;
        $this->batchKey = $batchKey;
        $this->company = $company;
        $this->totalPages = $totalPages;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $company_id = $this->companyId;
        $filesPath = public_path("access_control_reports/companies/$company_id");

        // Ensure the directory exists
        if (!file_exists($filesPath)) {
            mkdir($filesPath, 0777, true);
        }

        // Generate the PDF
        $output = Pdf::loadView('pdf.access_control_reports.report', [
            "chunk" => $this->chunk,
            "company" => $this->company,
            "params" => $this->params,
            "currentPage" => $this->batchKey,
            "totalPages" => $this->totalPages,
        ])->output();

        $file_name = $this->batchKey . '.pdf';
        file_put_contents($filesPath . '/' . $file_name, $output);
    }
}
