<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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

    protected function setWeekOffs(): void
    {
        // Define the dedicated channel for weekoff process logs
        $weekoffLog = Log::channel('weekoff');

        $logContext = [
            'company_id' => $this->companyId,
            'employee_id' => $this->employeeId,
            'month' => $this->month,
            'function' => __FUNCTION__,
        ];

        // Log start on the dedicated channel
        $weekoffLog->info('Starting weekoff assignment process.', $logContext);

        // --- 1. COUNT ELIGIBLE PRESENTS ---
        $totalEligiblePresents = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->whereNotIn('status', ['A', 'O'])
            ->count();

        if ($totalEligiblePresents === 0) {
            $weekoffLog->warning('No eligible Present records found. Setting all records to Absent.', $logContext);
            // High-level failure log on default channel
            $weekoffLog->warning("WEEKOFF: Employee {$this->employeeId} had no presents in month {$this->month}. Status reset to A.", $logContext);

            Attendance::where('company_id', $this->companyId)
                ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
                ->whereMonth('date', $this->month)
                ->update(['status' => 'A']);

            echo "No eligible Present records found. Skipping weekoff assignment.\n";
            return;
        }

        // --- 2. CALCULATE WEEKOFFS TO ASSIGN ---
        $numWeekOffsToAssign = intdiv($totalEligiblePresents, 6);

        $weekoffLog->info("Eligible Presents: {$totalEligiblePresents}. Calculated Weekoffs to assign: {$numWeekOffsToAssign}.", $logContext);

        if ($numWeekOffsToAssign === 0) {
            $weekoffLog->info('Not enough eligible presents to assign any weekoff.', $logContext);
            echo "Not enough Eligible Present records ({$totalEligiblePresents}) to assign any weekoff.\n";
            return;
        }

        // --- 3. IDENTIFY AVAILABLE WEEKOFF SLOTS (Optimized) ---
        $availableSlots = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->whereIn('status', ['A', 'O'])
            ->orderBy('date')
            ->limit($numWeekOffsToAssign)
            ->select('id', 'employee_id', 'date')
            ->get();

        $weekoffLog->debug("Fetched {$availableSlots->count()} candidate slots for potential weekoff assignment.", $logContext);

        // --- 4. DETERMINE FINAL WEEKOFFS ---
        $idsToSetWeekOff = [];
        $skippedSlots = [];

        foreach ($availableSlots as $candidateRow) {
            $logsExist = AttendanceLog::where('company_id', $this->companyId)
                ->where('UserID', $candidateRow->employee_id)
                ->whereDate('LogTime', $candidateRow->date)
                ->exists();

            if (!$logsExist) {
                $idsToSetWeekOff[] = $candidateRow->id;
                $weekoffLog->debug("Candidate ID {$candidateRow->id} (Date: {$candidateRow->date}) is eligible for WeekOff ('O').", $logContext);
            } else {
                $skippedSlots[] = $candidateRow->date;
                $weekoffLog->info("Skipping Candidate ID {$candidateRow->id} (Date: {$candidateRow->date}). Logs already exist.", $logContext);
            }
        }

        // --- 5. BATCH UPDATE FINAL WEEKOFFS ---
        $updatedCount = 0;
        if (!empty($idsToSetWeekOff)) {
            $updatedCount = Attendance::whereIn('id', $idsToSetWeekOff)->update(['status' => 'O']);

            // Critical log on both channels
            $logMessage = "Successfully assigned {$updatedCount} Weekoffs ('O').";
            $weekoffLog->notice($logMessage, array_merge($logContext, ['updated_ids' => $idsToSetWeekOff]));
            $weekoffLog->notice("WEEKOFF: Employee {$this->employeeId} assigned {$updatedCount} Weekoffs.", $logContext);

            echo "Successfully assigned {$updatedCount} weekoffs.\n";
        } else {
            $weekoffLog->info('No eligible slots remained after checking for existing logs. No update performed.', $logContext);
            echo "No eligible rows found for weekoff assignment among the candidates.\n";
        }

        $finalAttendanceData = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            // Use single quotes ('') for the string literals 'A' and 'O'
            ->selectRaw("SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as final_absent_count")
            ->selectRaw("SUM(CASE WHEN status = 'O' THEN 1 ELSE 0 END) as final_weekoff_count")
            ->selectRaw("SUM(CASE WHEN status NOT IN ('A', 'O') THEN 1 ELSE 0 END) as final_present_count")
            ->first();

        $summaryLog = [
            'Total_Eligible_Presents' => $totalEligiblePresents,
            'Calculated_Weekoffs' => $numWeekOffsToAssign,
            'Assigned_Weekoffs' => $updatedCount,
            'Skipped_Slots_Count' => count($skippedSlots),
            'Final_Presents' => $finalAttendanceData->final_present_count,
            'Final_Weekoffs' => $finalAttendanceData->final_weekoff_count,
            'Final_Absents' => $finalAttendanceData->final_absent_count,
            'Skipped_Dates' => $skippedSlots,
        ];

        $weekoffLog->info('Weekoff assignment process completed with summary.', array_merge($logContext, $summaryLog));

        echo "Employee {$this->employeeId} | Month {$this->month} | Presents: {$summaryLog['Final_Presents']} | Assigned Weekoffs: {$summaryLog['Assigned_Weekoffs']} | Final Absents: {$summaryLog['Final_Absents']}\n\n";
    }
}
