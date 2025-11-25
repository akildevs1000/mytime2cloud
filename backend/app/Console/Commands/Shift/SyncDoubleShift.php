<?php

namespace App\Console\Commands\Shift;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shift\SplitShiftController;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Validation\Rules\Unique;

class SyncDoubleShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_double_shift {company_id} {date} {checked?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument("company_id", 1);

        $formattedDate = (new DateTime())->format('d M Y \a\t H:i:s');

        $message = "Attendance Log Processing Alert !\n\n";

        $message .= "Dear Admin\n";

        $message .= "Attendance Logs Processed for Company id $id at $formattedDate\n\n";


        $date = $this->argument("date", date("Y-m-d"));

        // date_default_timezone_set('UTC');

        $found = Shift::where("company_id", $id)->where("shift_type_id", 5)->count();

        if ($found == 0) {
            return;
        }

        $UserIds = DB::table('schedule_employees as se')
            ->join('attendance_logs as al', 'se.employee_id', '=', 'al.UserID')
            ->join('shifts as sh', 'sh.id', '=', 'se.shift_id')
            ->select('al.UserID')
            ->where('sh.shift_type_id', "=", 5) // this condition not workin
            ->where('al.checked', $this->argument("checked", false) ? true : false)
            // ->where('al.UserID', 619)
            ->where('se.company_id', $id)
            ->where('al.company_id', $id)
            ->whereDate('al.log_date', $date)
            ->orderBy("al.LogTime")
            // ->take(50)
            ->pluck("al.UserID")
            ->unique()
            ->toArray();


        if (!$UserIds || count($UserIds) == 0) {
            $this->info("No data");
            return;
        }

        $this->info(json_encode($UserIds));

        $this->info((new SplitShiftController)->render($id, $date, 5, $UserIds, false, "kernel"));

        return 0;
    }
}
