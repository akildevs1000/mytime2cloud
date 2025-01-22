<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\ReportNotification;
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
        $logger = new Controller;

        $logFilePath = 'logs/whatsapp';

        $company_id = $this->argument("company_id", 0);

        $logFilePath = "$logFilePath/$company_id";

        $logger->logOutPut($logFilePath, "*****Cron started for alert:access_control $company_id *****");

        $clientId = Company::where("id", $company_id)->value("company_code") ?? 0;

        $models = ReportNotification::with("managers")
            ->where('type', 'access_control')
            ->orderBy("id", "desc")
            ->get();

        if ($models->isEmpty()) {
            $logger->logOutPut($logFilePath, "No Report Notification found.");
            $this->info("No ReportNotification found.");
            $logger->logOutPut($logFilePath, "*****Cron ended for alert:access_control $company_id *****");
            return;
        }

        $records = AttendanceLog::with(['employee', 'company', 'device'])
            ->with(["employee" => function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            }])
            ->where("LogTime", ">=", date("Y-m-d 00:00:00"))
            ->where("LogTime", "<=", date("Y-m-d 23:59:00"))
            ->where('company_id', $company_id)
            ->where('channel', "unknown")
            ->where('checked', false)
            ->limit(10)
            ->orderBy("id", "desc")
            ->get();

        if (!count($records->toArray())) {
            $logger->logOutPut($logFilePath, "Record count " . count($records->toArray()));
            $logger->logOutPut($logFilePath, "*****Cron ended for alert:access_control $company_id *****");
        }

        foreach ($models as $model) {

            $days = $model->days;
            $from_time = $model->from_time;
            $to_time = $model->to_time;
            $managers = $model->managers ?? [];

            $currentDay = date("w"); // day value as number
            if (!in_array($currentDay, $days) || !count($days)) {
                $logger->logOutPut($logFilePath, "Day not found");
                $logger->logOutPut($logFilePath, "*****Cron ended for alert:access_control $company_id *****");
                $this->info("Day not found");
                return;
            }

            // Check if there are no managers
            if ($managers->isEmpty()) {
                $logger->logOutPut($logFilePath, "No managers found for the specified company ID.");
                $this->info("No managers found for the specified company ID.");
                $logger->logOutPut($logFilePath, "*****Cron ended for alert:access_control $company_id *****");
                return;
            }

            foreach ($records as $key => $record) {

                $time = $record->time;

                if ($record->company && $record->employee && $record->device) {
                    if (
                        ($time >= $from_time && $time <= "23:59") || // Time is on the same day between from_time and midnight
                        ($time >= "00:00" && $time <= $to_time)      // Time is on the next day between midnight and to_time
                    ) {
                        try {
                            $name = ucfirst($record->employee->first_name) . " " . ucfirst($record->employee->last_name);
                            $formattedDate = (new DateTime($record->LogTime))->format('jS M Y H:i');
                            $message = $this->generateMessage($name, $record->device->name, $formattedDate);

                            foreach ($managers as $manager) {

                                if ($manager->branch_id == $record->employee->branch_id) {
                                    if (in_array("Whatsapp", $model->mediums)) {
                                        $response = Http::withoutVerifying()->post(
                                            'https://wa.mytime2cloud.com/send-message',
                                            [
                                                'clientId' =>  $clientId,
                                                'recipient' => $manager->whatsapp_number,
                                                'text' => $message,
                                            ]
                                        );

                                        // To handle the response
                                        if ($response->successful()) {
                                            $logger->logOutPut($logFilePath, "Message sent successfully");
                                            $this->info("Message sent successfully");
                                        } else {
                                            $logger->logOutPut($logFilePath, "Failed to send message");
                                            $this->info("Failed to send message!");
                                        }
                                    }

                                    if (in_array("Email", $model->mediums)) {
                                        // process for email
                                    }
                                    sleep(5);
                                }
                            }
                        } catch (\Throwable $e) {
                            $this->info($e);
                            $logger->logOutPut($logFilePath, "Exception: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        $logger->logOutPut($logFilePath, "*****Cron ended for alert:access_control $company_id *****");
    }

    private function generateMessage($name, $deviceName, $formattedDate)
    {
        return "🌟 *Access Control Notification* 🌟\n" .
            "Dear Admin,\n\n" .
            "✅ Your employee ($name) has accessed the door from *$deviceName* on *$formattedDate*.\n\n" .
            "Thank you!\n";
    }
}
