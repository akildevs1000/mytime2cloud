<?php

namespace App\Jobs\V1;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateAttendanceReportPDFTemplate4 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $systemUserId;
    public $company;
    public $employee;
    public $requestPayload;

    public function __construct($systemUserId, $company, $employee, $requestPayload)
    {
        $this->systemUserId = $systemUserId;
        $this->company = $company;
        $this->employee = $employee;
        $this->requestPayload = $requestPayload;
    }

    public function handle()
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(0)
                ->post(env('ATTENDANCE_REPORT_URL_TEMPLATE4'), [
                    'company_id'   => $this->company->id,
                    'company_name' => $this->company->name,
                    'from_date'    => $this->requestPayload["from_date"],
                    'to_date'      => $this->requestPayload["to_date"],
                    'employee_id'  => $this->systemUserId,
                    'url'          => env('ATTENDANCE_REPORT_URL_APP_URL'),
                    'id'           => $this->systemUserId,
                ]);

            if ($response->ok()) {
                Log::info("✅ Template 4 PDF Generated: Employee {$this->systemUserId}");
            } else {
                Log::error("❌ Template 4 API Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("❌ Template 4 Exception: " . $e->getMessage());
        }
    }
}
