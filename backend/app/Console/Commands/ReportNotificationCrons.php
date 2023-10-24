<?php

namespace App\Console\Commands;

use App\Mail\ReportNotificationMail;
use App\Models\report_notification_logs;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
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
    protected $signature = 'task:report_notification_crons {id}';

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
        $id = $this->argument("id");

        $script_name = "ReportNotificationCrons";

        $date = date("Y-m-d H:i:s");

        try {

            $model = ReportNotification::with(["managers", "company.company_mail_content"])->where("id", $id)->first();



            // foreach ($models as $model)
            {


                if (in_array("Email", $model->mediums)) {

                    // if ($model->frequency == "Daily") {

                    foreach ($model->managers as $key => $value) {


                        Mail::to($value->email)
                            ->send(new ReportNotificationMail($model));


                        $data = ["company_id" => $value->company_id, "branch_id" => $value->branch_id, "notification_id" => $value->notification_id, "notification_manager_id" => $value->id, "email" => $value->email];



                        ReportNotificationLogs::create($data);

                        // $response = Http::withoutVerifying()->get('https://ezwhat.com/api/send.php', [
                        //     'number' => $value->whatsapp_number,
                        //     'type' => 'text',
                        //     'message' => $message,
                        //     'instance_id' => '64DB354A9EBCC',
                        //     'access_token' => 'a27e1f9ca2347bb766f332b8863ebe9f',
                        // ]);



                    }
                } else {
                    echo "[" . $date . "] Cron: $script_name. No emails are configured";
                }
                // if (in_array("Whatsapp", $model->mediums)) {
                //     Mail::to($model->tos)->send(new TestMail($model));
                // }
            }



            echo "[" . $date . "] Cron: $script_name. Report Notification Crons has been sent.\n";
            return;
        } catch (\Throwable $th) {

            echo $th;
            echo "[" . $date . "] Cron: $script_name. Error occured while inserting logs.\n";
            Logger::channel("custom")->error("Cron: $script_name. Error Details: $th");
            return;
        }
    }
}
