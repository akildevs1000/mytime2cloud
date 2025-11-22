<?php

namespace App\Jobs;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenderWeekOffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyId;
    protected $month;
    protected $employeeId;

    public function __construct($companyId, $month, $employeeId)
    {
        $this->companyId = $companyId;
        $this->month = $month;
        $this->employeeId = $employeeId;
    }

    public function handle()
    {
        $this->setInitialStatusToAbsent();
        $this->setWeekOffs();
    }

    protected function setInitialStatusToAbsent()
    {
        $rows = Attendance::where('company_id', $this->companyId)
            ->where('employee_id', $this->employeeId)
            ->whereMonth('date', $this->month)
            ->get(['id', 'status']);

        if ($rows->count() > 0) {
            Attendance::whereIn('id', $rows->pluck('id'))
                ->where('status', '!=', 'P') // Keep present untouched
                ->update(['status' => 'A']);
        }
    }

    protected function setWeekOffs()
    {
        // Count total Present records
        $totalPresent = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', 'P')
            ->count();

        $this->info("Total Present: {$totalPresent}");

        if ($totalPresent === 0) {
            $this->info("No Present records found. Exiting weekoff assignment.");
            return;
        }

        // Number of weekoffs: 1 per 6 presents
        $numWeekOffs = intdiv($totalPresent, 6);
        $this->info("Number of Weekoffs to assign: {$numWeekOffs}");

        if ($numWeekOffs === 0) {
            $this->info("Not enough Present records to assign any weekoff.");
            return;
        }

        // Fetch all month rows ordered by date
        $allRows = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->orderBy('date')
            ->get(['id', 'date', 'status']);

        $presentCounter = 0;
        $weekOffsAssigned = 0;
        $weekOffIds = [];

        foreach ($allRows as $row) {
            $this->info("Row {$row->id} | Date: {$row->date} | Status: {$row->status}");

            if ($row->status === 'P') {
                $presentCounter++;
                $this->info("Present counter incremented: {$presentCounter}");
            }

            // After 6 presents, assign weekoff to the first Absent row after this
            if ($presentCounter === 6 && $weekOffsAssigned < $numWeekOffs) {
                $nextRow = $allRows->firstWhere(fn($r) => $r->id > $row->id && $r->status === 'A');

                if ($nextRow) {
                    $weekOffIds[] = $nextRow->id;
                    $weekOffsAssigned++;
                    $this->info("Weekoff assigned to ID {$nextRow->id} | Date: {$nextRow->date}");
                    $presentCounter = 0; // reset for next batch
                } else {
                    $this->info("No eligible Absent row found for weekoff after row {$row->id}");
                    $presentCounter = 0; // still reset counter
                }
            }
        }

        if (!empty($weekOffIds)) {
            $updated = Attendance::whereIn('id', $weekOffIds)->update(['status' => 'O']);
            $this->info("Total Weekoffs updated: {$updated}");
        } else {
            $this->info("No Weekoffs assigned.");
        }
    }
}
