<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ReportNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public $model,
        public $manager,
        public $files,
        public $branchId,
        public $reportDate
    ) {}

    public function build()
    {
        $this->subject($this->model->subject);

        $companyId = $this->model->company_id;
        $branchId = $this->branchId;
        $date = $this->reportDate;

        // Attach PDF files if they exist
        foreach ($this->files as $file) {
            $fullPath = storage_path("app/public/pdf/$date/{$companyId}/summary_report_{$branchId}_$file.pdf");

            Log::info("Email Report - Checking file: - task:report_notification_crons company_id: {$companyId}", [
                'branch_id' => $branchId,
                'file_path' => $fullPath,
                'exists'    => file_exists($fullPath) ? 'Yes' : 'No'
            ]);

            if (file_exists($fullPath)) {
                Log::info("File found for branch: {$branchId}", ['path' => $fullPath]);
                $this->attach($fullPath);
            } else {
                Log::error("MISSING FILE for branch: {$branchId}", ['path' => $fullPath]);
            }
        }

        // Build email body
        $managerName = optional($this->manager)->name ?? 'Manager';
        $companyName = optional($this->model->company)->name ?? 'N/A';

        $bodyContent = "Hi {$managerName},<br/>";
        $bodyContent .= "<b>Company: {$companyName}</b><br/><br/>";
        $bodyContent .= "Automated Email Reports.<br/>Thanks.";

        return $this->view('emails.report')->with([
            'body' => $bodyContent
        ]);
    }
}
