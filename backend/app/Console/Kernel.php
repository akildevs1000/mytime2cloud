<?php

namespace App\Console;

use App\Models\AccessControlTimeSlot;
use App\Models\Company;
use App\Models\DeviceActivesettings;
use App\Models\PayrollSetting;
use App\Models\ReportNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;

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
        $date = date("M-Y");

        // $devices = AccessControlTimeSlot::get();

        // foreach ($devices as $device) {
        //     foreach ($device->json as $slot) {

        //         $schedule
        //             ->command("task:AccessControlTimeSlots {$device->device_id} HoldDoor")
        //             // ->everyThirtyMinutes()
        //             ->everyMinute()
        //             ->dailyAt($slot["startTimeOpen"])
        //             ->withoutOverlapping()
        //             ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
        //             ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        //         $schedule
        //             ->command("task:AccessControlTimeSlots {$device->device_id} CloseDoor")
        //             // ->everyThirtyMinutes()
        //             ->everyMinute()
        //             ->dailyAt($slot["endTimeOpen"])
        //             ->withoutOverlapping()
        //             ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
        //             ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        //     }
        // }

        $devices = DeviceActivesettings::with('devices')->get();

        $date = date('Y-m-d');

        foreach ($devices as $key => $device) {
            # code...

            // Define the date range from January 1, 2023, to February 28, 2023
            $startDate = Carbon::createFromFormat('Y-m-d', $device->date_from);
            $endDate = Carbon::createFromFormat('Y-m-d', $device->date_to);

            // Define an array of times



            $open_json = $device->open_json;
            $open_settings_array = json_decode($open_json);


            foreach ($open_settings_array as $key => $value) {

                $schedule
                    ->command("task:AccessControlTimeSlots {$device->devices->device_id} HoldDoor")
                    ->days([$key])
                    ->between($startDate, $endDate)
                    ->times([$value])
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path("logs/$date-device_access-control-time-slot-logs.log"))
                    ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }

            $close_json = $device->close_json;
            $close_settings_array = json_decode($close_json);


            foreach ($close_settings_array as $key => $value) {

                $schedule
                    ->command("task:AccessControlTimeSlots {$device->device->device_id} CloseDoor")
                    ->days([$key])
                    ->between($startDate, $endDate)
                    ->times([$value])
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path("logs/$date-device_access-control-time-slot-logs.log"))
                    ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }
        }

        // $schedule
        //     ->command('task:sync_attendance_logs')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path("logs/" . date("d-M-y") . "-attendance-logs.log"))
        //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:update_company_ids')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->withoutOverlapping()
            // ->between('7:00', '23:59')
            ->appendOutputTo(storage_path("logs/$date-logs.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


        $companyIds = Company::pluck("id");

        foreach ($companyIds as $companyId) {

            if ($companyId != 1) {
                $schedule
                    ->command("task:sync_all_shifts {$companyId} " . date("Y-m-d"))
                    ->everyMinute()
                    // ->dailyAt('09:00')
                    ->runInBackground()
                    ->appendOutputTo(storage_path("logs/$date-sync-all-shifts-{$companyId}.log"))
                    ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }

            // $schedule
            //     ->command("task:generate_daily_report {$companyId} All")
            //     // ->everyMinute()
            //     ->dailyAt('03:45')
            //     ->runInBackground()
            //     ->appendOutputTo(storage_path("logs/$date-send-whatsapp-notification-{$companyId}.log"))
            //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:generate_daily_report {$companyId} P")
            //     // ->everyMinute()
            //     ->dailyAt('03:45')
            //     ->runInBackground()
            //     ->appendOutputTo(storage_path("logs/$date-send-whatsapp-notification-{$companyId}.log"))
            //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:generate_daily_report {$companyId} A")
            //     // ->everyMinute()
            //     ->dailyAt('03:45')
            //     ->runInBackground()
            //     ->appendOutputTo(storage_path("logs/$date-send-whatsapp-notification-{$companyId}.log"))
            //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:generate_daily_report {$companyId} M")
            //     // ->everyMinute()
            //     ->dailyAt('03:45')
            //     ->runInBackground()
            //     ->appendOutputTo(storage_path("logs/$date-send-whatsapp-notification-{$companyId}.log"))
            //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:send_whatsapp_notification {$companyId}")
                // ->everyMinute()
                ->dailyAt('09:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$date-send-whatsapp-notification-{$companyId}.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_absent $companyId")
                // ->everyMinute()
                ->dailyAt('00:30')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$date-absents-$companyId.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_leaves $companyId")
                //->everyFiveMinutes()
                ->dailyAt('01:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$date-leaves-$companyId.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_holidays $companyId")
                //->everyTenMinutes()
                ->dailyAt('01:30')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$date-holidays-$companyId.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$date-monthly-flexible-holidays-$companyId.log"))
                ->runInBackground()
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_off_by_day_week1 $companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$date-sync-off-by-day-week-1-$companyId.log"))
                ->runInBackground()
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_off_by_day_week2 $companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$date-sync-off-by-day-week-2-$companyId.log"))
                ->runInBackground()
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_flexible_offs_week1 $companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$date-sync-flexible-offs-week-1-$companyId.log"))
                ->runInBackground()
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_flexible_offs_week2 $companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$date-sync-flexible-offs-week-2-$companyId.log"))
                ->runInBackground()
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }

        $schedule->call(function () {
            exec('pm2 reload 18');
            info("Log listener restart");
        })->dailyAt('00:00');

        $schedule
            ->command('task:sync_single_shift')
            // ->dailyAt('4:00')
            // ->hourly()
            ->everyMinute()
            ->between('7:00', '23:59')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$date-logs.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


        $schedule
            ->command('task:sync_multiinout')
            // ->dailyAt('4:00')
            // ->hourly()
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$date-logs.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:sync_split_shift')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$date-logs.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:update_visitor_company_ids')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$date-logs.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:sync_visitors')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$date-visitor-logs.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:check_device_health')
            ->everyThirtyMinutes()
            ->between('7:00', '23:59')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$date-devices-health.log"))
            ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        

       




        $payroll_settings = PayrollSetting::get(["id", "date", "company_id"]);

        foreach ($payroll_settings as $payroll_setting) {

            $payroll_date = (int) (new \DateTime($payroll_setting->date))->modify('-24 hours')->format('d');

            $schedule
                ->command("task:payslip_generation $payroll_setting->company_id")
                ->monthlyOn((int) $payroll_date, "00:00")
                ->appendOutputTo(storage_path("$date-payslip-generate-$payroll_setting->company_id.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }

        $models = ReportNotification::get();

        foreach ($models as $model) {
            $scheduleCommand = $schedule->command('task:report_notification_crons')
                ->runInBackground()
                ->appendOutputTo("custom_cron.log")
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            if ($model->frequency == "Daily") {
                $scheduleCommand->dailyAt($model->time);
            } elseif ($model->frequency == "Weekly") {
                $scheduleCommand->weeklyOn($model->day, $model->time);
            } elseif ($model->frequency == "Monthly") {
                $scheduleCommand->monthlyOn($model->day, $model->time);
            }
        }

        if (env("APP_ENV") == "production") {
            $schedule
                ->command('task:db_backup')
                ->dailyAt('6:00')
                ->appendOutputTo(storage_path("logs/db_backup.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command('restart_sdk')
                // ->everyMinute()
                // ->everyThirtyMinutes()
                ->dailyAt('4:00')
                //->hourly()
                ->appendOutputTo(storage_path("logs/restart_sdk.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }
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
