<?php

namespace App\Console;

use App\Mail\TestMail;
use App\Models\ReportNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->withoutOverlapping()
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:sync_attendance')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->between('7:00', '23:59')
            ->withoutOverlapping()
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:sync_absent')
            ->dailyAt('1:00')
            ->appendOutputTo("scheduler.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


        // PDF
        $schedule
            ->command('task:generate_summary_report')
            ->everyMinute()
            // ->dailyAt('1:00')
            ->appendOutputTo("pdf.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:generate_daily_present_report')
            ->everyMinute()
            // ->dailyAt('1:00')
            ->appendOutputTo("pdf.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:generate_daily_absent_report')
            ->everyMinute()
            // ->dailyAt('1:00')
            ->appendOutputTo("pdf.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:generate_daily_missing_report')
            ->everyMinute()
            // ->dailyAt('1:00')
            ->appendOutputTo("pdf.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:generate_daily_manual_report')
            ->everyMinute()
            // ->dailyAt('1:00')
            ->appendOutputTo("pdf.log")
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            
        // $this->run_custom($schedule);
        // env("APP_ENV") == "production"
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

    public function run_custom($schedule)
    {
        $data["email"] = "aatmaninfotech@gmail.com";
        $data["title"] = "From ItSolutionStuff.com";
        $data["body"] = "This is Demo";

        $pdf = PDF::loadView('emails.myTestMail', $data);

        $models = ReportNotification::get();

        foreach ($models as $model) {
            if (in_array("Email", $model->mediums)) {

                $schedule->call(function () use ($model) {
                    Mail::to($model->tos)
                        ->cc($model->ccs)
                        ->bcc($model->bccs)
                        ->queue(new TestMail($model));
                })->everyMinute();

                // if ($model->frequency == "Daily") {
                //     Mail::to($model->tos)
                //         ->cc($model->ccs)
                //         ->bcc($model->bccs)
                //         ->queue(new TestMail($model));
                // }
            }
            // if (in_array("Whatsapp", $model->mediums)) {
            //     Mail::to($model->tos)->send(new TestMail($model));
            // }
        }
        return "done";
    }
}
