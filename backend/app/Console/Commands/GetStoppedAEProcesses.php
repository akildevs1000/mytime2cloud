<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class GetStoppedAEProcesses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pm2:stopped-ae-processes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get stopped AE processes from PM2';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ignoredProcesses = ['pm2-client-connector', 'unused-whatsapp-client'];

        // Run the PM2 command to get the process list in JSON format
        $process = new Process(['pm2', 'jlist']);
        $process->run();

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            $this->error('Error: ' . $process->getErrorOutput());
            return 1;
        }

        try {
            // Decode the JSON output from PM2
            $output = $process->getOutput();
            $processes = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON output from PM2');
                return 1;
            }

            // Filter the processes that are stopped and match AE condition
            $stoppedAEProcesses = collect($processes)
                ->filter(function ($proc) use ($ignoredProcesses) {
                    return $proc['pm2_env']['status'] === 'stopped' &&
                        str_starts_with($proc['name'], 'AE') &&
                        !in_array($proc['name'], $ignoredProcesses);
                })
                ->map(function ($proc) {
                    return explode('_', $proc['name'])[0]; // Extract AE00012 from AE00012_xxxxx
                })
                ->map(function ($proc) {
                    return (int) explode('AE', $proc)[1]; // Extract AE00012 from AE00012_xxxxx
                })
                ->unique()
                ->values()
                ->all();

            $companies = Company::with("contact:company_id,whatsapp")
                ->whereIn("company_code", $stoppedAEProcesses)
                ->get(["id", "company_code"]);

            if (count($companies) > 0) {
                foreach ($companies as $company) {

                    $whatsapp =  $company->contact->whatsapp;

                    $whatsapp = env("ADMIN_WHATSAPP_NUMBER");

                    Artisan::call('send_whatsapp_expiry_notification', [
                        'client_whatsapp_number' => $whatsapp,
                    ]);

                    $this->info("📣 Alert sent to $whatsapp");

                }
            } else {
                $this->info('No stopped AE processes found.');
            }

            return 0; // Success
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            return 1; // Error
        }
    }
}
