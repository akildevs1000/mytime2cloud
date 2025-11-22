<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class RenderWeekOff extends Command
{
    protected $signature = 'render:weekoff {company_id=13} {month=10} {employee_id=48} {test?}';
    protected $description = 'Set attendance status for testing weekoffs';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        $month = $this->argument('month');
        $employeeId = $this->argument('employee_id');

        // Display all month-based records for info
        $rows = Attendance::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get(['id', 'date', 'status']);

        $this->table(["id", "date", "status"], $rows);

        // Independent simulation for Present
        $this->processSimulatingForPresent($companyId, $month, $employeeId);

        // Independent setting of Absent status
        $this->setInitialStatusToAbsent($companyId, $month, $employeeId);

        // Independent setting of Weekoff status
        $this->setWeekOffs($companyId, $month, $employeeId);

        return Command::SUCCESS;
    }

    public function processSimulatingForPresent($companyId, $month, $employeeId = null)
    {
        $numberOfDays = 7;

        if ($this->argument('test')) {
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

    public function setInitialStatusToAbsent($companyId, $month, $employeeId = null)
    {
        $rows = Attendance::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('date', $month)
            ->get(['id', 'status']);

        if ($rows->count() > 0) {
            $updatedCount = Attendance::whereIn('id', $rows->pluck('id'))
                ->where('status', '!=', 'P') // keep present rows untouched
                ->update(['status' => 'A']); // A = Absent

            $this->info("Total ({$updatedCount}) records set as Absent for month {$month}.");
        }
    }

    public function setWeekOffs($companyId, $month, $employeeId = null)
    {
        // Get the total number of Present records for the month
        $totalPresent = Attendance::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('date', $month)
            ->where('status', 'P')
            ->count();

        if ($totalPresent === 0) {
            $this->info("No Present records found for month {$month}.");
            return;
        }

        $numWeekOffs = intdiv($totalPresent, 6); // 1 weekoff per 6 present
        if ($numWeekOffs === 0) {
            $this->info("Not enough Present records to assign any weekoff.");
            return;
        }

        // Fetch all month-based records ordered by date
        $allRows = Attendance::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get(['id', 'status']);

        $presentCounter = 0;
        $weekOffIds = [];
        $weekOffsAssigned = 0;

        foreach ($allRows as $row) {
            if ($row->status === 'P') {
                $presentCounter++;
            }

            if ($presentCounter === 6 && $weekOffsAssigned < $numWeekOffs) {
                // Find the next non-P, non-O record
                $nextRow = $allRows->firstWhere(fn($r) => $r->id > $row->id && $r->status !== 'P' && $r->status !== 'O');
                if ($nextRow) {
                    $weekOffIds[] = $nextRow->id;
                    $weekOffsAssigned++;
                    $presentCounter = 0; // reset counter for next weekoff
                }
            }
        }

        if (!empty($weekOffIds)) {
            Attendance::whereIn('id', $weekOffIds)
                ->update(['status' => 'O']); // O = Weekoff

            $this->info("Total (" . count($weekOffIds) . ") records set as Weekoff (O) for month {$month}.");
        }
    }
}
