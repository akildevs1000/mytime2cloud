<?php

namespace App\Jobs\Shift;

use App\Http\Controllers\Shift\FiloShiftController;
use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logger;

class SyncFlexibleShiftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companyId;
    protected $date;

    public $timeout = 1200; // 20 minutes

    public function __construct($companyId, $date)
    {
        $this->companyId = $companyId;
        $this->date = $date;
    }

    public function handle()
    {
        $id = $this->companyId;
        $date = $this->date;

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        $employeeIds = Employee::where("company_id", $id)
            ->whereHas("schedule", function ($q) use ($id, $date) {
                $q->where("company_id", $id);
                $q->where("isAutoShift", false);
                $q->whereIn("shift_type_id", [1]);
                $q->whereHas("shift", function ($shiftQuery) use ($date) {
                    $shiftQuery->whereJsonContains("days", Carbon::parse($date)->format("D"));
                });
            })
            ->whereHas("attendance_logs", function ($q) use ($start, $end) {
                $q->whereBetween("LogTime", [$start, $end])
                    ->where("checked", false);
            })
            ->pluck("system_user_id");

        Logger::channel('shift')->info('Queue: SyncFlexibleShiftJob Started', [
            'company_id' => $id,
            'date'       => $date,
        ]);

        $employeeIds->chunk(10)->each(function ($chunk) use ($id, $date, $start, $end) {

            $params = [
                'date'         => '',
                'UserID'       => '',
                'updated_by'   => 26,
                'company_ids'  => [$id],
                'manual_entry' => true,
                'reason'       => '',
                'employee_ids' => $chunk->toArray(),
                'dates'        => [$date, $date],
                'shift_type_id' => 0,
                'company_id'   => $id,
                'channel'      => "queue",
            ];

            $result = (new FiloShiftController())->render($id, $date, 1, $chunk->toArray());

            // Mark logs as checked so this chunk is skipped on next run
            $updated = AttendanceLog::whereIn("UserID", $chunk->toArray())
                ->whereBetween("LogTime", [$start, $end])
                ->where("checked", false)
                ->update(["checked" => true]);

            Logger::channel('shift')->info('Queue request chunk', [
                'chunk'           => $chunk->toArray(),
                'params'          => $params,
                'result'          => $result,
                'logs_checked'    => $updated,  // how many rows were marked
            ]);
        });

        Logger::channel('shift')->info('Queue: SyncFlexibleShiftJob Completed Successfully');
    }
}
