<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WhatsappProxyHealthCheck extends Command
{
    protected $signature = 'whatsapp:proxy-health-check {path=/root/wa}';
    protected $description = 'Check recently updated WhatsApp proxy CSV files (last 2 hours) using shell';

    public function handle()
    {
        $path = $this->argument('path');

        // Sanitize path
        $escapedPath = escapeshellarg($path);

        $command = "find $escapedPath -type f -iname \"*.csv\" -mmin -120";

        $output = shell_exec($command);

        if ($output) {
            $this->info("CSV files modified in the last 2 hours:");
            $this->line($output);
        } else {
            $this->warn("No recent CSV files found or an error occurred.");
        }

        return Command::SUCCESS;
    }
}
