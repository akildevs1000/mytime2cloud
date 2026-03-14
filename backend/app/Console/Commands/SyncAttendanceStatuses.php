<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncAttendanceStatuses extends Command
{
    /**
     * Usage: 
     * php artisan attendance:sync {date?} {company_id?}
     */
    protected $signature = 'attendance:sync {date?} {company_id?}';
    protected $description = 'Syncs attendance records with O(1) holiday lookup and optional company filtering.';

    public function handle()
    {
        // 1. Parse Arguments
        $dateArgument = $this->argument('date');
        $companyArgument = $this->argument('company_id');
        
        $date = $dateArgument ? Carbon::parse($dateArgument) : Carbon::yesterday();
        $dateString = $date->toDateString();

        $this->info("--- Starting Sync for Date: $dateString" . ($companyArgument ? " | Company ID: $companyArgument" : "") . " ---");

        // 2. Fetch Holidays ONCE (Filtered by Company if argument exists)
        $holidayQuery = DB::table('holidays')
            ->whereDate('start_date', '<=', $dateString)
            ->whereDate('end_date', '>=', $dateString);
            
        if ($companyArgument) {
            $holidayQuery->where('company_id', $companyArgument);
        }

        $holidayCompanyIds = $holidayQuery->pluck('company_id')
            ->flip() 
            ->toArray();

        // 3. Get IDs of employees who already have logs (to avoid overwriting)
        $alreadyLoggedIds = Attendance::whereDate('date', $dateString)
            ->pluck('employee_id');

        // 4. Chunk employees
        $employeeQuery = Employee::whereNotIn('system_user_id', $alreadyLoggedIds);

        // Filter by company if requested
        if ($companyArgument) {
            $employeeQuery->where('company_id', $companyArgument);
        }

        $employeeQuery->chunkById(500, function ($employees) use ($date, $dateString, $holidayCompanyIds) {
            $batch = [];

            foreach ($employees as $employee) {
                // Logic Priority: Holiday > Weekend (Off) > Absent
                if (isset($holidayCompanyIds[$employee->company_id])) {
                    $status = "H";
                } elseif ($date->isWeekend()) {
                    $status = "O";
                } else {
                    $status = "A";
                }

                $batch[] = [
                    'employee_id'   => $employee->system_user_id,
                    'company_id'    => $employee->company_id,
                    'branch_id'     => $employee->branch_id,
                    'date'          => $dateString,
                    'status'        => $status,
                    'roster_id'     => 0,
                    'total_hrs'     => '---',
                    'in'            => '---',
                    'out'           => '---',
                    'ot'            => '---',
                    'device_id_in'  => '---',
                    'device_id_out' => '---',
                    'shift_id'      => 0,
                    'shift_type_id' => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (!empty($batch)) {
                Attendance::insert($batch);
                $this->comment("Synced a batch of " . count($batch) . " records.");
            }
        });

        $this->info("--- Sync Completed Successfully ---");
    }
}