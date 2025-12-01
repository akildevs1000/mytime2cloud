<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AttendanceLog;
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
        $this->setWeekOffs();
    }

    // ... other uses

    protected function setWeekOffs(): void
    {
        // --- 1. COUNT ELIGIBLE PRESENTS ---
        $totalEligiblePresents = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->whereNotIn('status', ['A', 'O'])
            ->count();

        if ($totalEligiblePresents === 0) {
            // ... (existing logic to set all to 'A' and return)
            echo "No eligible Present records found. Setting all records to Absent.\n";

            Attendance::where('company_id', $this->companyId)
                ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
                ->whereMonth('date', $this->month)
                ->update(['status' => 'A']);

            return;
        }

        // --- 2. CALCULATE WEEKOFFS TO ASSIGN ---
        $numWeekOffsToAssign = intdiv($totalEligiblePresents, 6);

        if ($numWeekOffsToAssign === 0) {
            echo "Not enough Eligible Present records ({$totalEligiblePresents}) to assign any weekoff.\n";
            return;
        }

        // --- 3. IDENTIFY AVAILABLE WEEKOFF SLOTS (Highly Optimized) ---
        // Select only necessary columns: 'id', 'employee_id', 'date'.
        $availableSlots = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->whereIn('status', ['A', 'O'])
            ->orderBy('date')
            ->limit($numWeekOffsToAssign) // Limit the rows fetched
            ->select('id', 'employee_id', 'date') // Select specific columns
            ->get();

        // --- 4. DETERMINE FINAL WEEKOFFS ---
        $idsToSetWeekOff = [];

        // Filter candidates: only set 'O' if no corresponding log entry exists.
        foreach ($availableSlots as $candidateRow) {
            // Since we only fetched limited columns, ensure the usage matches the selection.
            $logsExist = AttendanceLog::where('company_id', $this->companyId)
                ->where('UserID', $candidateRow->employee_id)
                ->whereDate('LogTime', $candidateRow->date)
                ->exists();

            // If no logs found, this slot is eligible for 'O' (WeekOff).
            if (!$logsExist) {
                $idsToSetWeekOff[] = $candidateRow->id;
            }
        }

        // --- 5. BATCH UPDATE FINAL WEEKOFFS ---
        if (!empty($idsToSetWeekOff)) {
            Attendance::whereIn('id', $idsToSetWeekOff)->update(['status' => 'O']);
            echo "Successfully assigned " . count($idsToSetWeekOff) . " weekoffs.\n";
        } else {
            echo "No eligible rows found for weekoff assignment among the candidates.\n";
        }

        // --- 6. DISPLAY SUMMARY ---
        // ... (rest of the summary code remains the same)
        $finalAbsentCount = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', 'A')
            ->count();

        $finalWeekOffCount = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', 'O')
            ->count();

        echo "Employee {$this->employeeId} | Month {$this->month} | Eligible Presents: {$totalEligiblePresents} | Final Weekoffs: {$finalWeekOffCount} | Final Absents: {$finalAbsentCount}\n\n";
    }
}
