<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
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
        $logFilePath = 'logs/whatsapp';



        // for kernel use
        // $schedule
        // ->command("alert:access_control {$companyId}")
        // ->everyFiveMinutes()
        // ->runInBackground();

        $company_id = $this->argument("company_id", 0);

        $logFilePath = "$logFilePath/$company_id";

        (new Controller)->logOutPut($logFilePath, "*****Cron started for alert:access_control $company_id *****");


        // Fetch the ReportNotification model with filtered managers
        $model = ReportNotification::with([
            'managers' => function ($query) use ($company_id) {
                $query->where('company_id', $company_id)
                    ->select(['id', 'whatsapp_number', 'email', 'notification_id']); // Ensure foreign and primary keys are included
            }
        ])->where('type', 'access_control')->first();

        // Check if the ReportNotification model exists
        if (!$model) {
            (new Controller)->logOutPut($logFilePath, "No ReportNotification found for the specified type");
            $this->info("No ReportNotification found for the specified type.");
            return;
        }

        $days = $model->days;

        sort($days); // ["0","1","3","4","5","6"]

        $currentDay = date("w"); // day value as number
        if (!in_array($currentDay, $days)) {
            (new Controller)->logOutPut($logFilePath, "Day not found");
            $this->info("Day not found");
            return;
        }

        $from_time = $model->from_time;
        $to_time = $model->to_time;

        // Extract managers or set an empty array if null
        $managers = $model->managers ?? [];

        // Check if there are no managers
        if ($managers->isEmpty()) {
            (new Controller)->logOutPut($logFilePath, "No managers found for the specified company ID.");
            $this->info("No managers found for the specified company ID.");
            return;
        }

        $clientId = Company::where("id", $company_id)->value("company_code") ?? 0;

        $records = AttendanceLog::with(['employee', 'company', 'device'])
            ->with(["employee" => function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            }])
            ->where("LogTime", ">=", date("Y-m-d 00:00:00"))
            ->where("LogTime", "<=", date("Y-m-d 23:59:00"))
            ->where('company_id', $company_id)
            ->where('channel', "unknown")
            ->where('checked', false)
            ->limit(1)
            ->orderBy("id", "desc")
            ->get();

        foreach ($records as $key => $record) {

            $time = $record->time;

            if ($record->company && $record->employee && $record->device) {
                if (
                    ($time >= $from_time && $time <= "23:59") || // Time is on the same day between from_time and midnight
                    ($time >= "00:00" && $time <= $to_time)      // Time is on the next day between midnight and to_time
                ) {
                    try {
                        $name = ucfirst($record->employee->first_name) . " " . ucfirst($record->employee->last_name);
                        $formattedDate = (new DateTime($record->LogTime))->format('jS M Y');
                        $message = $this->generateMessage($name, $record->device->name, $formattedDate);

                        foreach ($managers as $manager) {

                            if (in_array("Whatsapp", $model->mediums)) {
                                $response = Http::withoutVerifying()->post(
                                    'https://wa.mytime2cloud.com/send-message',
                                    [
                                        'clientId' =>  $clientId,
                                        'recipient' => $manager->whatsapp_number,
                                        'text' => $message,
                                    ]
                                );
                            }

                            if (in_array("Email", $model->mediums)) {
                                // process for email
                            }
                            sleep(5);
                        }



                        // To handle the response
                        if ($response->successful()) {
                            (new Controller)->logOutPut($logFilePath, "Message sent successfully");
                            $this->info("Message sent successfully");
                        } else {
                            (new Controller)->logOutPut($logFilePath, "Failed to send message");
                            $this->info("Failed to send message!");
                        }
                    } catch (\Throwable $e) {
                        (new Controller)->logOutPut($logFilePath, "Exception: " . $e->getMessage());
                        $this->info($e);
                    }
                }
            }
        }

        (new Controller)->logOutPut($logFilePath, "*****Cron ended for alert:access_control $company_id *****");
    }

    private function generateMessage($name, $deviceName, $formattedDate)
    {
        return "🌟 *Access Control Notification* 🌟\n" .
            "Dear Admin,\n\n" .
            "✅ Your employee ($name) has accessed the door from *$deviceName* on *$formattedDate*.\n\n" .
            "Thank you!\n";
    }
}
