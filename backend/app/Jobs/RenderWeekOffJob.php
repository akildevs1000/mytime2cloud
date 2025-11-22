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

        if ($totalPresent === 0) {
            echo "No Present records found. Skipping weekoff assignment.\n";
            return;
        }

        // Number of weekoffs: 1 per 6 presents
        $numWeekOffs = intdiv($totalPresent, 6);
        echo "Employee {$this->employeeId} | Weekoffs to assign: {$numWeekOffs}\n";

        if ($numWeekOffs === 0) {
            echo "Not enough Present records to assign any weekoff.\n";
            return;
        }

        // Get all non-P rows ordered by date
        $availableRows = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', '!=', 'P')
            ->orderBy('date')
            ->get(['id', 'date', 'status']);

        $weekOffRows = $availableRows->take($numWeekOffs);

        $weekOffIds = $weekOffRows->pluck('id')->toArray();

        if (!empty($weekOffIds)) {
            Attendance::whereIn('id', $weekOffIds)->update(['status' => 'O']);

            // Display assigned weekoffs
            foreach ($weekOffRows as $row) {
                echo "Employee {$this->employeeId} | Weekoff assigned | ID: {$row->id} | Date: {$row->date}\n";
            }
        } else {
            echo "No eligible rows found for weekoff assignment.\n";
        }

        // Display summary
        $totalAbsent = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', 'A')
            ->count();

        echo "Employee {$this->employeeId} | Presents: {$totalPresent} | Weekoffs: {$numWeekOffs} | Absents: {$totalAbsent}\n\n";
    }
}
