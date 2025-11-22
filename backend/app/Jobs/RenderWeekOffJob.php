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
        // Count total present records
        $totalPresent = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', 'P')
            ->count();

        if ($totalPresent === 0) return;

        // Number of weekoffs: 1 weekoff per 6 presents
        $numWeekOffs = intdiv($totalPresent, 6);
        if ($numWeekOffs === 0) return;

        // Fetch all month rows ordered by date
        $allRows = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->orderBy('date')
            ->get(['id', 'status']);

        $presentCounter = 0;
        $weekOffsAssigned = 0;
        $weekOffIds = [];

        foreach ($allRows as $row) {
            if ($row->status === 'P') {
                $presentCounter++;
            }

            // After 6 presents, assign a weekoff to the first non-P, non-O row
            if ($presentCounter === 6 && $weekOffsAssigned < $numWeekOffs) {
                // Find the next eligible row
                $nextRow = $allRows->firstWhere(fn($r) => $r->id > $row->id && !in_array($r->status, ['P']));
                if ($nextRow) {
                    $weekOffIds[] = $nextRow->id;
                    $weekOffsAssigned++;
                    $presentCounter = 0; // reset counter for next weekoff
                }
            }
        }

        if (!empty($weekOffIds)) {
            Attendance::whereIn('id', $weekOffIds)->update(['status' => 'O']);
        }
    }
}
