<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WhatsappProxyHealthCheck extends Command
{
    protected $signature = 'whatsapp:proxy-health-check {minutes=240} {path=/root/wa}';
    protected $description = 'Check recently updated WhatsApp proxy CSV files (last 2 hours) using shell';

    public function handle()
    {
        return $this->sendEmailsForCsvIds($ids = [
            'AE00042_1751446185853',
            'AE00012_1751126594985',
        ]);

        $path = $this->argument('path');
        $minutes = $this->argument('minutes');

        $escapedPath = escapeshellarg($path);

        $command = "find $escapedPath -type f -iname \"*.csv\" -mmin +$minutes";

        $this->info("Checking for recently updated CSV files in $path");
        $this->info("Running command: $command");

        $output = shell_exec($command);

        if ($output) {
            $this->info("CSV files modified in the last $minutes minutes:");
            $this->line($output);

            $lines = explode("\n", trim($output));
            $ids = [];

            foreach ($lines as $line) {
                if (preg_match('/\/([^\/]+)_logs\.csv$/', $line, $matches)) {
                    $ids[] = $matches[1]; // e.g. AE00042_1751446185853
                }
            }

            if (!empty($ids)) {
                $this->info("Extracted IDs:");
                $this->line(json_encode($ids, JSON_PRETTY_PRINT));
            } else {
                $this->warn("No valid CSV filenames matched the pattern.");
            }
        } else {
            $this->warn("No recent CSV files found or an error occurred.");
        }

        return Command::SUCCESS;
    }

    protected function sendEmailsForCsvIds(array $ids)
    {
        $recipients = [
            'AE00042_1751446185853' => 'francisgill1000@gmail.com',
            'AE00012_1751126594985' => 'francisgill1000@gmail.com',
        ];

        foreach ($ids as $id) {
            $email = $recipients[$id] ?? null;

            if ($email) {
                Mail::raw("Dear Admin,\n\n Your whatsapp account has been expired. Please update your account.\n\nBest regards,\n MyTime2Cloud", function ($message) use ($email, $id) {
                    $message->to($email)
                        ->subject("Mytime2cloud: Whatsapp Account Expired");
                });

                $this->info("Email sent for ID: $id to $email");
            } else {
                $this->warn("No recipient defined for ID: $id");
            }
        }
    }
}
