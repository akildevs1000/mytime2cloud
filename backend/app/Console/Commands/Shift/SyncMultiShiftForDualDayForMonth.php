<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SyncMultiShiftDualDayForMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_multi_shift_dual_day_for_month {employee_id} {year_month} {force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run sync_multi_shift_dual_day command for each day of the specified month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employeeId = $this->argument('employee_id');
        $yearMonth = $this->argument('year_month');
        $force = $this->argument('force');

        // Parse the year and month from the input
        $date = Carbon::createFromFormat('Y-m', $yearMonth);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Loop through each day of the month
        while ($startOfMonth->lte($endOfMonth)) {
            $dateString = $startOfMonth->toDateString();

            // Call the child command
            Artisan::call('task:sync_multi_shift_dual_day', [
                'employee_id' => $employeeId,
                'date' => $dateString,
                'force' => $force,
            ]);

            // Output the result
            $this->info("Processed date: {$dateString}");

            // Move to the next day
            $startOfMonth->addDay();
        }

        $this->info('All dates processed for the month.');
    }
}