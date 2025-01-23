<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

// use Illuminate\Support\Facades\Log as Logger;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\NotifyIfLogsDoesNotGenerate;

class AlertOfflineDevice extends Command
{
    protected $signature = 'alert:offline_device {company_id}';

    protected $description = 'Alert to notfiy the user about offline devices';

    public function handle()
    {
        $company_id = $this->argument("company_id", 1);

        $logger = new Controller;

        $logFilePath = 'logs/whatsapp/device';

        $logFilePath = "$logFilePath/$company_id";

        $currentTime = new \DateTime();

        $dateTime = $currentTime->format('F j, Y \a\t h:i:s A');

        $logger->logOutPut($logFilePath, "*****Cron started for alert:offline_device *****");

        $reportNotifications = DeviceNotification::with("managers")
            ->where("company_id", $company_id)
            ->orderByDesc("id")
            ->get();

        if ($reportNotifications->isEmpty()) {
            $logger->logOutPut($logFilePath, "No Alert Found");
            $logger->logOutPut($logFilePath, "*****Cron ended for alert:offline_device *****");

            $this->info("No Alert Found");
            return;
        }

        $company = Company::where("id", $company_id)->get(["company_code", "name"]) ?? 0;

        if (!$company) {
            $logger->logOutPut($logFilePath, "No Company Found");
            $logger->logOutPut($logFilePath, "*****Cron ended for alert:offline_device *****");

            $this->info("No Company Found");
            return;
        }

        $devices = Device::where('status_id', 2)
            ->excludeOtherDevices()
            ->select("id", "company_id", "branch_id", "status_id", "name")
            ->with("branch")
            ->where('company_id', $company_id)
            ->get(); //for testing only

        if ($devices->isEmpty()) {
            $logger->logOutPut($logFilePath, "No Device Found");
            $logger->logOutPut($logFilePath, "*****Cron ended for alert:offline_device *****");

            $this->info("No Device Found");
            return;
        }

        foreach ($reportNotifications as $reportNotification) {

            foreach ($reportNotification->managers as $manager) {

                if (!$reportNotification->managers->isEmpty()) {
                    $logger->logOutPut($logFilePath, "No Manager Found");
                    $logger->logOutPut($logFilePath, "*****Cron ended for alert:offline_device *****");

                    $this->info("No Manager Found");
                    continue;
                }

                foreach ($devices as $device) {

                    $deviceName = $reportNotification->name;

                    if ($manager->branch_id == $device->branch_id) {
                        $name = $device->branch->branch_name;

                        if (!$name) {
                            $name = $company->name;
                        }

                        $message = "Device Offline Alert !\n" .
                            "\n" .
                            "Dear Admin,\n\n" .
                            "*$deviceName* is offline at  *$name* since *$dateTime*.\n" .
                            "Thank you!\n";

                        $this->info($message);

                        $endpoint = 'https://wa.mytime2cloud.com/send-message';

                        $payload = [
                            'clientId' =>  $deviceName->company->company_code ?? "_1",
                            'recipient' => "971554501483",
                            'text' => $message,
                        ];

                        $res = Http::withoutVerifying()->post($endpoint, $payload);

                        if ($res->successful()) {
                            $logger->logOutPut($logFilePath, $message);
                            $logger->logOutPut($logFilePath, "Message sent successfully");
                            $this->info($message);
                            $this->info("Message sent successfully");
                        } else {
                            $logger->logOutPut($logFilePath, "Failed to send message");
                            $this->info("Failed to send message!");
                        }

                        sleep(5);
                    }
                }
            }
        }

        $logger->logOutPut($logFilePath, "*****Cron ended for alert:offline_device *****");
    }
}
