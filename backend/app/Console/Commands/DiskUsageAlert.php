<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DiskUsageAlert extends Command
{
    protected $signature = 'monitor:disk';
    protected $description = 'Send email alert if disk usage exceeds a defined threshold';

    public function handle()
    {
        $threshold = (int) env('DISK_USAGE_THRESHOLD', 80); // set in .env file if needed
        $usage = $this->getDiskUsage();

        $from = env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        $to = explode(',', env('ADMIN_MAIL_RECEIVERS', 'francisgill1000@gmail.com'));

        $this->info("📤 Sender: {$from}");
        $this->info("📥 Receivers: " . implode(', ', $to));
        $this->info("📊 Current disk usage: {$usage}%");

        $subject = "Disk Usage Alert: {$usage}% used";
        $emailMessage = $this->buildMessage($usage, $threshold);

        Mail::raw($emailMessage, function ($message) use ($to, $subject, $from) {
            $message->to($to)
                ->from($from)
                ->subject($subject);
        });

        $this->info("✅ Email sent successfully.");

        return 0;
    }

    protected function getDiskUsage(): int
    {
        $output = shell_exec("df / | grep / | awk '{ print $5 }'");
        return (int) trim(str_replace('%', '', $output));
    }

    protected function buildMessage(int $usage, int $threshold): string
    {
        $statusMessage = $usage > $threshold
            ? "🚨 Disk usage exceeded the threshold ({$threshold}%).\nCurrent usage: {$usage}%."
            : "✅ Disk usage is within safe limits.\nCurrent usage: {$usage}%.";

        $statusMessage .= "\n\n🔧 Tip: Run the following command on the server to investigate disk usage:\n";
        $statusMessage .= "sudo du -ahx / | sort -rh | head -n 20";

        return $statusMessage;
    }
}
