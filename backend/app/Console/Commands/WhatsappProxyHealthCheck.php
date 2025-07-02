<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
}
