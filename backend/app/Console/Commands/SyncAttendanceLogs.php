<?php

namespace App\Console\Commands;

use App\Helpers\BenchmarkHelper;
use App\Http\Controllers\AttendanceLogController;
use Illuminate\Console\Command;

class SyncAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:sync_attendance_logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Attendance Logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("========== START: task:sync_attendance_logs ==========");

        try {
            $benchmark = BenchmarkHelper::measure(function () {
                return json_encode((new AttendanceLogController)->store());
            });

            $this->info("✔ Execution Successful");
            $this->info("▶ Result: {$benchmark['result']}");
            $this->info("⏳ Execution Time: {$benchmark['execution_time']} sec");
            $this->info("💾 Memory Used: {$benchmark['memory_used']} MB");
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("========== END: task:sync_attendance_logs ==========");
        return Command::SUCCESS;
    }
}
