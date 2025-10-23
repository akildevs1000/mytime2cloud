<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class ProcessManualAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * You can run it using:
     * php artisan attendance:manual-check
     */
    protected $signature = 'attendance:manual-check';

    /**
     * The console command description.
     */
    protected $description = 'Process employees who have attendance logs with mode=Manual for the current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Fetch employees having attendance logs with today's date and mode = 'Manual'
        $employees = Employee::where('special_access', true)->whereHas('attendance_logs', function ($query) use ($today) {
            $query->whereDate('created_at', $today)
                  ->where('mode', 'Manual');
        })->get();

        if ($employees->isEmpty()) {
            $this->info("No employees found with manual attendance logs for {$today}.");
            return 0;
        }

        $this->info("Found {$employees->count()} employee(s) with manual attendance logs for {$today}.");

        // Example: process or display
        foreach ($employees as $employee) {
            $this->line("Employee ID: {$employee->id}, Name: {$employee->name}");

            foreach ($employee->attendanceLogs()
                ->whereDate('created_at', $today)
                ->where('mode', 'Manual')
                ->get() as $log) {

                $this->line("  ➤ Log ID: {$log->id}, Time: {$log->created_at}");
            }
        }

        return 0;
    }
}
