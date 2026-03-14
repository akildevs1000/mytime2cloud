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
        $dateArgument = $this->argument('date');
        $companyArgument = $this->argument('company_id');

        $date = $dateArgument ? Carbon::parse($dateArgument) : Carbon::yesterday();
        $dateString = $date->toDateString();

        $this->info("--- Initializing Sync: $dateString ---");
        if ($companyArgument) {
            $this->warn("Targeting Company ID: $companyArgument");
        }

        // 1. Fetch Holidays
        $holidayQuery = DB::table('holidays')
            ->whereDate('start_date', '<=', $dateString)
            ->whereDate('end_date', '>=', $dateString);

        if ($companyArgument) {
            $holidayQuery->where('company_id', $companyArgument);
        }

        $holidayCompanyIds = $holidayQuery->pluck('company_id')->flip()->toArray();
        $this->info("Found " . count($holidayCompanyIds) . " companies with active holidays.");

        // 2. Identify employees who already have logs
        $alreadyLoggedIds = Attendance::whereDate('date', $dateString)->pluck('employee_id');
        $this->info(count($alreadyLoggedIds) . " employees already have attendance records. Skipping them...");

        // 3. Process missing employees
        $employeeQuery = Employee::where('is_active', true)
            ->whereNotIn('system_user_id', $alreadyLoggedIds);

        if ($companyArgument) {
            $employeeQuery->where('company_id', $companyArgument);
        }

        $totalEmployees = $employeeQuery->count();
        $this->info("Identified $totalEmployees employees with missing logs.");

        $employeeQuery->chunkById(500, function ($employees) use ($date, $dateString, $holidayCompanyIds) {
            $batch = [];
            $counts = ['H' => 0, 'O' => 0, 'A' => 0];

            foreach ($employees as $employee) {
                if (isset($holidayCompanyIds[$employee->company_id])) {
                    $status = "H";
                } elseif ($date->isWeekend()) {
                    $status = "O";
                } else {
                    $status = "A";
                }

                $counts[$status]++;

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
                // Log exactly what happened in this batch
                $this->line("<fg=cyan>Batch Inserted:</> H: {$counts['H']} | O: {$counts['O']} | A: {$counts['A']}");
            }
        });

        $this->info("--- Sync Completed Successfully ---");
    }
}
