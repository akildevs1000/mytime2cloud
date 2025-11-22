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

        // Number of weekoffs: 1 weekoff per 6 present
        $numWeekOffs = intdiv($totalPresent, 6);
        if ($numWeekOffs === 0) return;

        // Get all rows NOT present, ordered by date
        $availableRows = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', '!=', 'P') // Only non-P records
            ->orderBy('date')
            ->get(['id', 'date']);

        // Assign weekoffs to the first N non-P rows
        $weekOffIds = $availableRows->take($numWeekOffs)->pluck('id')->toArray();

        if (!empty($weekOffIds)) {
            Attendance::whereIn('id', $weekOffIds)->update(['status' => 'O']);
        }
    }
}
