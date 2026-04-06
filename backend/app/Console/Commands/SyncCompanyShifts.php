<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

class SyncCompanyShifts extends Command
{
    protected $signature = 'company:sync-shifts';
    protected $description = 'Sync all shift types for all companies and log as a table';

    public function handle()
    {
        $today = date("Y-m-d");
        $hour = (int)date('H');
        
        $companyIds = Company::whereHas('attendance_logs', function ($query) use ($today) {
            $query->whereDate('log_date', $today);
        })->pluck('id');

        $rows = [];
        $startTime = microtime(true);

        foreach ($companyIds as $id) {
            $this->info("Processing Company ID: $id...");

            // Execute commands
            Artisan::call("task:sync_attendance_missing_shift_ids $id $today");
            Artisan::call("task:sync_auto_shift $id $today");
            Artisan::call("task:sync_except_auto_shift $id $today");

            $multiShift = 'Skipped';
            if ($hour >= 5) {
                Artisan::call("task:sync_multi_shift_v1 $id $today");
                Artisan::call("task:sync_split_shift $id $today");
                $multiShift = 'Synced';
            }

            // Collect data for the table
            $rows[] = [
                'ID' => $id,
                'Date' => $today,
                'Basic Shifts' => 'Synced',
                'Multi/Split' => $multiShift,
                'Timestamp' => date('H:i:s')
            ];
        }

        $this->logTable($rows);
        
        $this->info('All company shifts synchronized successfully.');
    }

    /**
     * Formats the collection into a table string and writes to laravel.log
     */
    protected function logTable(array $rows)
    {
        if (empty($rows)) {
            info("SyncCompanyShifts: No companies processed today.");
            return;
        }

        // Use BufferedOutput to capture the table as a string
        $buffer = new BufferedOutput();
        $table = new Table($buffer);

        $table->setHeaders(['Company ID', 'Log Date', 'Standard Sync', 'Multi/Split', 'Finished At'])
              ->setRows($rows);
        
        $table->render();

        $tableString = $buffer->fetch();

        // Write to Laravel Log
        info("\n--- Company Shift Sync Report ---\n" . $tableString);
        
        // Also show in console
        $this->line($tableString);
    }
}