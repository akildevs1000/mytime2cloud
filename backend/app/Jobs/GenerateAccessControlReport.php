<?php

namespace App\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAccessControlReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunk;
    protected $companyId;
    protected $date;
    protected $params;
    protected $batchKey;
    protected $company;
    protected $totalPages;
    protected $totalRecord;

    public function __construct($chunk, $companyId, $date, $params,  $company, $batchKey, $totalPages, $totalRecord)
    {
        $this->chunk = $chunk;
        $this->companyId = $companyId;
        $this->date = $date;

        $this->params = $params;
        $this->batchKey = $batchKey;
        $this->company = $company;
        $this->totalPages = $totalPages;
        $this->totalRecord = $totalRecord;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '512M'); // Adjust to the required value

        set_time_limit(120);

        $company_id = $this->companyId;

        $date = $this->date;

        $filesPath = public_path("access_control_reports/companies/$company_id");

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


        if ($this->batchKey >= $this->totalPages) {

            echo $this->batchKey .  "-" . $this->totalPages . "\n";

            $pdfFiles = glob($filesPath . '/*.pdf');

            // Initialize FPDI
            $pdf = new \setasign\Fpdi\Fpdi();

            // Loop through each PDF file
            foreach ($pdfFiles as $file) {
                $pageCount = $pdf->setSourceFile($file);

                // Add each page from the source PDF to the final output
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tplId = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tplId);  // Get the page size of the imported PDF

                    // Adjust orientation based on the original page's width and height
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';  // Auto-detect orientation

                    // Add a new page with the detected orientation
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);

                    $pdf->useTemplate($tplId);
                }
            }


            $outputFilePath = $filesPath . "/$date.pdf";

            $pdf->Output($outputFilePath, 'F');

            echo $outputFilePath;

            foreach ($pdfFiles as $file) {
                if (basename($file) !== "$date.pdf") { // Check if the file is not report.pdf
                    unlink($file); // Delete the file
                }
            }
        }
    }
}
