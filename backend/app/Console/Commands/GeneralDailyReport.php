<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAttendanceSummaryReport;
use App\Models\Company;
use App\Models\CompanyBranch;
use Illuminate\Console\Command;

class GeneralDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate_daily_report {id} {shift_type} {status?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Daily Report';

    public function handle()
    {

        $shift_type = $this->argument("shift_type") ?? "General";
        $company_id = $this->argument("id");

        $from_date = date("Y-m-d", strtotime("-1 day"));
        $to_date = date("Y-m-d", strtotime("-1 day"));
        
        $heading = "Summary";

        $companyPayload = Company::whereId($company_id)
            ->with('contact:id,company_id,number')
            ->first(["logo", "name", "company_code", "location", "p_o_box_no", "id", "user_id"]);

        $company = [
            "logo" => $companyPayload->logo,
            "name" => $companyPayload->name,
            "email" => $companyPayload->user->email ?? 'mail not found',
            "location" => $companyPayload->location,
            "contact" => $companyPayload->contact->number ?? 'contact not found',
            "report_type" => $heading,
            "from_date" => $from_date,
            "to_date" => $to_date,
        ];

        $branchIds = CompanyBranch::where("company_id", $company_id)->pluck("id");

        // GenerateAttendanceSummaryReport::dispatch($shift_type, $company_id, 49, $company);

        foreach ($branchIds as $branchId) {
            GenerateAttendanceSummaryReport::dispatch($shift_type, $company_id, $branchId, $company);
        }
    }
}
