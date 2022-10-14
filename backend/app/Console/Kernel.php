<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('task:sync_attendance_logs')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->between('7:00', '23:59')
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:update_company_ids')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->between('7:00', '23:59')
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:sync_attendance')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->between('7:00', '23:59')
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:sync_absent')
            ->dailyAt('13:00')
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
