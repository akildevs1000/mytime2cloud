<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DiskUsageAlert extends Command
{
    protected $signature = 'monitor:disk';
    protected $description = 'Send email alert if disk usage exceeds threshold';

    public function handle()
    {
        $output = shell_exec("df / | grep / | awk '{ print $5 }'");
        $usage = (int) trim(str_replace('%', '', $output));

        $this->info("SENDER MAIL: " . env("MAIL_FROM_ADDRESS", "francisgill1000@gmail.com"));

        $this->info("RECEIVERS MAIL: " . env("ADMIN_MAIL_RECEIVERS", "francisgill1000@gmail.com"));

        if ($usage > 80) {
            $to = 'your@email.com';
            Mail::raw("⚠️ Disk usage on / has reached {$usage}%", function ($message) use ($to, $usage) {
                $message->to($to)
                    ->subject("Disk Alert: {$usage}% used");
            });

            $this->info("Alert email sent. Usage: {$usage}%");
        } else {
            $this->info("Disk usage is fine: {$usage}%");
        }

        return 0;
    }
}
