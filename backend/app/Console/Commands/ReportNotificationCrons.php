<?php

namespace App\Console\Commands;

use App\Http\Controllers\WhatsappController;
use App\Jobs\SendWhatsappMessageJob;
use App\Mail\ReportNotificationMail;
use App\Models\report_notification_logs;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use App\Models\WhatsappClient;
use Illuminate\Support\Facades\Mail;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;


class ReportNotificationCrons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:report_notification_crons {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report Notification Crons';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $company_id = $this->argument("company_id");


        $script_name = "ReportNotificationCrons";

        $date = date("Y-m-d H:i:s");
        $yesterday = date("Y-m-d", strtotime("-1 day"));

        $accounts = WhatsappClient::where("company_id", $company_id)->value("accounts");




        try {

            $models = ReportNotification::where("type", "attendance")
                ->with(["managers", "company.company_mail_content"])
                ->with("managers", function ($query) use ($company_id) {
                    $query->where("company_id", $company_id);
                })->where("company_id", $company_id)->get();

            foreach ($models as $model) {

                $company_id = $model->company->id;
                $branchId = $model->branch_id;

                $link = asset("storage/pdf/$yesterday/{$company_id}/summary_report_{$branchId}.pdf");

                $whatsappMessage = "Your Summary attendance report is ready. You can download it from the link below:\n$link";

                $this->info($whatsappMessage);

                foreach ($model->managers as $key => $manager) {

                    if ($manager->branch_id == $model->branch_id) {

                        if (in_array("Email", $model->mediums ?? [])) {
                            Mail::to($manager->email = "francisgill1000@gmail.com")
                                ->queue(new ReportNotificationMail($model, $manager));
                        }

                        if (in_array("Whatsapp", $model->mediums ?? [])) {
                            if (file_exists(storage_path($link))) {
                                if (!$accounts || !is_array($accounts) || empty($accounts[0]['clientId'])) {
                                    $this->info("No Whatsapp Client found.");
                                } else {
                                    $clientId = $accounts[0]['clientId'];
                                    SendWhatsappMessageJob::dispatch(
                                        $manager->whatsapp_number,
                                        $whatsappMessage,
                                        0,
                                        $clientId,
                                        "file"
                                    );
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {

            echo $th;
            echo "[" . $date . "] Cron: $script_name. Error occured while inserting logs.\n";
            //Logger::channel("custom")->error("Cron: $script_name. Error Details: $th");
            return;
        }
    }
}
