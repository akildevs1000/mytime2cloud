<?php

namespace App\Console\Commands;

use App\Jobs\RenderWeekOffJob;
use App\Models\Attendance;
use Illuminate\Console\Command;

class RenderWeekOff extends Command
{
    protected $signature = 'render:weekoff {company_id=13} {month=10} {employee_id?} {test?}';
    protected $description = 'Set attendance status for testing weekoffs';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        $month = $this->argument('month');
        $employeeId = $this->argument('employee_id'); // optional
        $test = $this->argument('test');

        // Determine which employee(s) to process
        $employeeIds = $employeeId
            ? collect([$employeeId]) // single employee
            : Attendance::where('company_id', $companyId)
            ->distinct()
            ->pluck('employee_id'); // all employees

        foreach ($employeeIds as $empId) {
            // Simulate Present if test is enabled
            if ($test) {
                $this->processSimulatingForPresent($companyId, $month, $empId);
            }

            // Dispatch job per employee
            RenderWeekOffJob::dispatch($companyId, $month, $empId);

            $this->info("RenderWeekOff job dispatched for employee {$empId} of company {$companyId}, month {$month}.");
        }

        return Command::SUCCESS;
    }


    public function processSimulatingForPresent($companyId, $month, $employeeId = null)
    {
        $numberOfDays = 7;

        $rows = Attendance::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->take($numberOfDays)
            ->get(['id']);

        if ($rows->count() > 0) {
            $presentCount = Attendance::whereIn('id', $rows->pluck('id'))
                ->update(['status' => 'P']);

            $this->info("Total ({$presentCount}) records set as Present for month {$month} (simulation).");
        }
    }
}
