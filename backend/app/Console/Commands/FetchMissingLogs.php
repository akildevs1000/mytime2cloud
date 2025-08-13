<?php
namespace App\Console\Commands;

use App\Jobs\FetchMissingLogsJob;
use Illuminate\Console\Command;

class FetchMissingLogs extends Command
{
    protected $signature   = 'logs:fetch-missing {device_id?} {date?}';
    protected $description = 'Fetch missing logs from a device for the last 24 hours with offset loop';

    public function handle()
    {
        $deviceId = $this->argument('device_id');
        $dateArg  = $this->argument('date');

        if (! $deviceId) {
            $deviceId = $this->ask('Please enter the Device Id', "M014200892110002790");
        }

        if (! $dateArg) {
            $defaultDate = now()->format('Y-m-d');
            $dateArg     = $this->ask('Please enter the date (YYYY-MM-DD)', $defaultDate);
        }

        FetchMissingLogsJob::dispatch($deviceId, $dateArg); // optional: send to specific queue

        $this->info("📌 Job dispatched for device {$deviceId} and date {$dateArg}. Check queue worker logs for progress.");
    }
}
