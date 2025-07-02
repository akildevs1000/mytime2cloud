<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\WhatsappClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WhatsappProxyHealthCheck extends Command
{
    protected $signature = 'whatsapp:proxy-health-check {minutes=240} {path=/root/wa}';
    protected $description = 'Check recently updated WhatsApp proxy CSV files (last 2 hours) using shell';

    public function handle()
    {
        $path = $this->argument('path');
        $minutes = $this->argument('minutes');

        $escapedPath = escapeshellarg($path);

        $command = "find $escapedPath -type f -iname \"*.csv\" -mmin -$minutes";

        $this->info("Checking for recently updated CSV files in $path");
        $this->info("Running command: $command");

        $output = shell_exec($command);

        $companies = Company::get(["id", "company_code"]);

        if (!count($companies)) {
            $this->info("No company found.");
            return;
        }

        $companyids = array_column($companies->toArray(), "company_code");

        if ($output) {
            $this->info("CSV files modified in the last $minutes minutes:");
            $this->line($output);

            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                if (preg_match('/\/([^\/]+)_logs\.csv$/', $line, $matches)) {
                    $id = explode("_", $matches[1])[0] ?? null; // e.g. AE00042

                    if ($id && in_array($id, $companyids)) {
                        $this->sendEmailsForCsvIds($id, "francisgill1000@gmail.com");
                        $this->info("Id from file: $id, Company IDs: " . implode(", ", $companyids));
                    }
                }
            }
        } else {
            $this->warn("No recent CSV files found or an error occurred.");
        }

        return Command::SUCCESS;
    }

    protected function sendEmailsForCsvIds($to)
    {

        if ($to) {
            Mail::raw("Dear Admin,\n\nYour whatsapp account has been expired. Please update your account.\n\nBest regards,\nMyTime2Cloud", function ($message) use ($to) {
                $message->to($to)
                    ->subject("Mytime2cloud: Whatsapp Account Expired");
            });

            $this->info("Email sent to $to");
        }
    }
}
