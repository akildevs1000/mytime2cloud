<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:checker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the attendance report from the backend API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $company_id = 22;
        $date = date("Y-m-d");

        $model = Attendance::query();
        $model->where('company_id', $company_id);
        $model->where('date', $date);

        // Include related attendance logs (the child model)
        $model->with(['attendanceLogs' => function ($query) use ($company_id, $date) {
            $query->where('company_id', $company_id)
                ->whereBetween('log_date', [date("$date 00:00:00"), date("$date 03:59:59")])
                ->select('id', 'LogTime', 'UserID', 'company_id', 'log_date');
        }]);

        $model->take(10);

        $result = $model->get(['id', 'date', 'employee_id', 'company_id',"logs"]);  // Adjust to select the parent fields you need
        ld($result);
    }
}
