<?php

namespace App\Console\Commands;

use App\Jobs\RenderWeekOffJob;
use App\Models\Attendance;
use Illuminate\Console\Command;

class RenderWeekOff extends Command
{
    protected $signature = 'render:weekoff {company_id=13} {month=10} {employee_id?}';
    protected $description = 'Set attendance status for testing weekoffs';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        $month = $this->argument('month');
        $employeeId = $this->argument('employee_id'); // optional

        // Determine which employee(s) to process
        $employeeIds = $employeeId
            ? collect([$employeeId]) // single employee
            : Attendance::where('company_id', $companyId)
            ->distinct()
            ->pluck('employee_id'); // all employees

        foreach ($employeeIds as $empId) {
            RenderWeekOffJob::dispatch($companyId, $month, $empId);

            $this->info("RenderWeekOff job dispatched for employee {$empId} of company {$companyId}, month {$month}.");
        }

        return Command::SUCCESS;
    }
}
