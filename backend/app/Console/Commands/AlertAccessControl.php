<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\ReportNotification;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AlertAccessControl extends Command
{
    protected $signature = 'alert:access_control {company_id}';

    protected $description = 'Alert users when someone comes between a given time on selected days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // for kernel use
        // $schedule
        // ->command("alert:access_control {$companyId}")
        // ->everyFiveMinutes()
        // ->runInBackground();

        $company_id = $this->argument("company_id");

        // $currentDate = Carbon::now();

        $model = ReportNotification::with(
            [
                "managers" => function ($query) use ($company_id) {
                    $query->where("company_id", $company_id);
                }
            ]
        )->where("type", "access_control")->first();

        if (!$model) {
            $this->info("no data");
        }

        if (!count($model->managers)) {
            return;
        }

        $clientId = Company::where("id", $company_id)->value("company_code") ?? 0;

        $whatsapp_number = $model->managers[0]->whatsapp_number ?? "971554501483";


        $records = AttendanceLog::with(['employee', 'company', 'device'])
            ->with(["employee" => function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            }])
            ->where("LogTime", ">=", date("Y-m-d 00:00:00"))
            ->where("LogTime", "<=", date("Y-m-d 23:59:00"))
            ->where('company_id', $company_id)
            // ->where('channel', "unknown")
            // ->where('checked', false)
            ->limit(5)
            ->orderBy("id", "desc")
            ->get();

        foreach ($records as $key => $record) {

            if ($record->company && $record->employee && $record->device) {

                $time = $record->time;

                if ($time >= $model->from_time && $time <= $model->to_time) {
                    try {
                        $name = ucfirst($record->employee->first_name)   . " " . ucfirst($record->employee->last_name);

                        $date = $record->LogTime;
                        $datetime = new DateTime($date);
                        $formattedDate1 = $datetime->format('jS M Y');
                        $message = "🌟 *Access Control Notification* 🌟\n";
                        $message .= "Dear " . "Admin" . ". " . ", \n\n";

                        $message .= "✅ Your employee ($name) has been accessed the  door from *" . $record->device->name . "* on *" . $formattedDate1 . ".\n\n";

                        $message .= "Thank you!\n";

                        $this->info($message);


                        $response = Http::withoutVerifying()->post(
                            'https://wa.mytime2cloud.com/send-message',
                            [
                                'clientId' =>  $clientId,
                                'recipient' => $whatsapp_number,
                                'text' => $message,
                            ]
                        );

                        // To handle the response
                        if ($response->successful()) {
                            $this->info("Message sent successfully!");
                        } else {
                            $this->info("Failed to send message!");
                        }
                    } catch (\Throwable $e) {
                        $this->info($e);
                    }

                    sleep(5);
                }
            } else {
                $this->info("Record not found");
            }
        }
    }
}
