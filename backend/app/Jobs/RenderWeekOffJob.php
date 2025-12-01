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
        $this->setWeekOffs();
    }

    protected function setWeekOffs()
    {
        // Count total present records
        $totalPresent = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            //->where('status', 'P')
            ->whereNotIn('status', ['A', 'O'])   // A = Absent, O = WeekOff (adjust codes as needed)
            ->count();

        if ($totalPresent === 0) {
            echo "No Present records found. Skipping weekoff assignment.\n";

            Attendance::where('company_id', $this->companyId)
                ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
                ->whereMonth('date', $this->month)
                ->update(['status' => 'A']); // Set all to Absent if no presents found
            return;
        }

        // Number of weekoffs: 1 per 6 presents
        $numWeekOffs = intdiv($totalPresent, 6);
        // echo "Employee {$this->employeeId} | Weekoffs to assign: {$numWeekOffs}\n";

        if ($numWeekOffs === 0) {
            echo "Not enough Present records to assign any weekoff.\n";
            return;
        }

        $availableRows = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->whereIn('status', ['A', 'O'])   // A = Absent, O = WeekOff (adjust codes as needed)
            ->orderBy('date')
            ->get();

        Attendance::whereIn('id', $availableRows->pluck('id')->toArray())->update(['status' => 'A']);

        $weekOffRows = $availableRows->take($numWeekOffs);

        $idsToUpdate = [];

        foreach ($weekOffRows as $row) {

            // Check if logs exist for this employee on this date
            $logsExist = \App\Models\AttendanceLog::where('company_id', $this->companyId)
                ->where('UserID', $row->employee_id)          // use $this->employeeId
                ->whereDate('LogTime', $row->date)           // use LogTime or edit_date column
                ->exists();                                  // check existence

            if ($logsExist) {
                echo "Employee {$this->employeeId} | Weekoff assigned | ID: {$row->id} | Date: {$row->date} | Logs exist\n";
            } else {
                echo "Employee {$this->employeeId} | Weekoff assigned | ID: {$row->id} | Date: {$row->date} | No logs found\n";

                // Collect IDs to update
                $idsToUpdate[] = $row->id;
            }
        }

        if (!empty($idsToUpdate)) {
            Attendance::whereIn('id', $idsToUpdate)->update(['status' => 'O']);
        } else {
            echo "No eligible rows found for weekoff assignment.\n";
        }

        // Display summary
        $totalAbsent = Attendance::where('company_id', $this->companyId)
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->whereMonth('date', $this->month)
            ->where('status', 'A')
            ->count();

        echo "Employee {$this->employeeId} | Month {$this->month} | Presents: {$totalPresent} | Assignable Weekoffs: {$numWeekOffs} | Absents: {$totalAbsent}\n\n";
    }
}
