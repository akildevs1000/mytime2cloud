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

        $command = "find $escapedPath -type f -iname \"*.csv\" -mmin +$minutes";

        $this->info("Checking for recently updated CSV files in $path");
        $this->info("Running command: $command");

        $output = shell_exec($command);

        $companies = Company::with('user')->get(['id', 'company_code', 'user_id']);

        if ($companies->isEmpty()) {
            $this->info("No company found.");
            return;
        }

        // Key by company_code for easy access
        $companyEmails = $companies->keyBy('company_code')->map(function ($company) {
            return $company->user?->email; // use null safe operator
        })->toArray();

        if (!count($companies)) {
            $this->info("No company found.");
            return;
        }

        if ($output) {
            $this->info("CSV files modified in the last $minutes minutes:");
            $this->line($output);

            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                if (preg_match('/\/([^\/]+)_logs\.csv$/', $line, $matches)) {
                    $id = explode("_", $matches[1])[0] ?? null; // e.g. AE00042

                    if ($id && isset($companyEmails[$id])) {
                        $companyEmail = $companyEmails[$id];

                        $this->sendEmailsForCsvIds($companyEmail);

                        $this->info("Email sent for $id to $companyEmail (bcc to Francis)");
                    }
                }
            }
        } else {
            $this->warn("No recent CSV files found or an error occurred.");
        }

        return Command::SUCCESS;
    }

    protected function sendEmailsForCsvIds($to = 'akildevs1000@gmail.com')
    {
        if ($to) {
            Mail::raw("Dear Admin,\n\nYour WhatsApp account has expired. Please update your account.\n\nBest regards,\nMyTime2Cloud", function ($message) use ($to) {
                $message->to($to)
                    ->bcc('akildevs1000@gmail.com')
                    ->subject("MyTime2Cloud: WhatsApp Account Expired");
            });

            $this->info("Email sent to $to with BCC to akildevs1000@gmail.com");
        }
    }
}
