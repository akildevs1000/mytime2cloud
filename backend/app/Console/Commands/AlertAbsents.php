<?php

namespace App\Console\Commands;

use App\Http\Controllers\WhatsappController;
use App\Mail\ReportNotificationMail;
use App\Models\Employee;
use App\Models\ReportNotification;
use App\Models\ReportNotificationLogs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Mail;

class AlertAbsents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:absents {id} {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $todayDate = date("D, F j, Y");

        $id = $this->argument("id");
        $company_id = $this->argument("company_id");

        $absentEmployees = Employee::where("company_id", $company_id)->whereHas("today_absent")->get(["id", "first_name", "whatsapp_number"]);

        $absentEmployeeCount = $absentEmployees->count();

        $script_name = "Alert Absents";

        $date = date("Y-m-d H:i:s");

        try {

            $model = ReportNotification::where("type", "alert")->with(["managers", "company.company_mail_content"])->where("id", $id)

                ->with("managers", function ($query) use ($company_id) {
                    $query->where("company_id", $company_id);
                })->first();


            // if (in_array("Email", $model->mediums ?? [])) {

            //     // if ($model->frequency == "Daily") {

            //     foreach ($model->managers as $key => $value) {


            //         Mail::to($value->email)
            //             ->send(new ReportNotificationMail($model, $value));


            //         $data = ["company_id" => $value->company_id, "branch_id" => $value->branch_id, "notification_id" => $value->notification_id, "notification_manager_id" => $value->id, "email" => $value->email];



            //         ReportNotificationLogs::create($data);
            //     }
            // } else {
            //     echo "[" . $date . "] Cron: $script_name. No emails are configured";
            // }

            //wahtsapp with attachments
            if (in_array("Whatsapp", $model->mediums ?? [])) {

                foreach ($model->managers as $manager) {

                    $adminName = "Admin";
                    $systemName = "MyTime2@Cloud";

                    $message = "Subject: System Notification: Absent Employees Update\n\n";
                    $message .= "Dear $adminName,\n\n";
                    $message .= "This is an automated message to inform you that a total of $absentEmployeeCount employees were absent today ($todayDate).\n\n";

                    $message .= "Employee List:\n\n";


                    foreach ($absentEmployees as $key => $absentEmployee) {

                        $employeeName =  $absentEmployee->first_name . "\n";

                        $message .= ++$key . ". " . $employeeName;

                        $whatsappResponse = (new WhatsappController)->sendMessage($message, $absentEmployee->whatsapp_number);

                        $this->info($whatsappResponse);
                    }

                    $message .= "\n";

                    $message .= "For any further information or action required, please let us know.\n\n";
                    $message .= "Thank you,\n$systemName";

                    $whatsappResponse = (new WhatsappController)->sendMessage($message, $manager->whatsapp_number);

                    $this->info($whatsappResponse);
                }


                foreach ($absentEmployees as $absentEmployee) {

                    $employeeName =  $absentEmployee->first_name;

                    $yourName = "Admin";

                    $message = "Hi " . $employeeName . ",\n\n";
                    $message .= "We noticed that you were absent today ($todayDate). If there's a valid reason for your absence, please let us know.\n\n";
                    $message .= "Thank you,\n" . $yourName;


                    $whatsappResponse = (new WhatsappController)->sendMessage($message, $absentEmployee->whatsapp_number);

                    $this->info($whatsappResponse);
                }
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
