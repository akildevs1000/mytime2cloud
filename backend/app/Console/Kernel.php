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
use Illuminate\Support\Facades\Artisan;

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
        $monthYear = date("M-Y");

        $schedule
            ->command('task:sync_attendance_logs')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/" . date("d-M-y") . "-attendance-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        $schedule
            ->command('task:sync_attendance_camera_logs')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/" . date("d-M-y") . "-attendance-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:update_company_ids')
            // ->everyThirtyMinutes()
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$monthYear-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


        $companyIds = Company::pluck("id");

        foreach ($companyIds as $companyId) {

            $schedule
                ->command("task:sync_auto_shift {$companyId} " . date("Y-m-d"))
                ->everyMinute()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path("logs/shifts/auto/$monthYear-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            //if ($companyId == 1) 
            {
                $schedule
                    ->command("send_notificatin_for_offline_devices {$companyId}")
                    //  ->dailyAt('09:00')
                    ->everySixHours()
                    // ->everyThirtyMinutes()
                    // ->everyMinute()
                    ->runInBackground()
                    ->appendOutputTo(storage_path("logs/$monthYear-send-notification-for-offline-devices-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }


            $schedule
                ->command("task:sync_multi_shift {$companyId} " . date("Y-m-d"))
                ->everyMinute()
                ->between('06:00', '23:59')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/shifts/multi/$monthYear-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_multi_shift {$companyId} " . date("Y-m-d", strtotime("yesterday")))
                ->hourly()
                ->between('00:00', '05:59')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/shifts/multi/$monthYear-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_filo_shift {$companyId} " . date("Y-m-d"))
                // ->hourly()
                ->everyMinute()
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/shifts/filo/$monthYear-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_night_shift {$companyId} " . date("Y-m-d"))
                // ->hourly()
                ->everyMinute()
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/shifts/night/$monthYear-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_single_shift {$companyId} " . date("Y-m-d"))
                // ->hourly()
                ->everyMinute()
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/shifts/single/$monthYear-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_split_shift {$companyId} " . date("Y-m-d"))
                ->everyMinute()
                // ->dailyAt('09:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$monthYear-sync-split-logs-by-log-type-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            $schedule
                ->command("task:sync_visitor_attendance {$companyId} " . date("Y-m-d"))
                ->everyMinute()
                // ->dailyAt('09:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$monthYear-sync-visitor-logs-by-log-type-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $schedule
                ->command("default_attendance_seeder {$companyId}")
                ->monthlyOn(1, "00:00")
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$monthYear-default-attendance-seeder-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            //whatsapp reports 
            $array = ['All', "P", "A", "M", "ME"];
            foreach ($array as $status) {

                $schedule
                    ->command("task:generate_daily_report {$companyId}  {$status}")
                    // ->everyMinute()
                    ->dailyAt('03:45')
                    ->runInBackground()
                    ->appendOutputTo(storage_path("logs/$monthYear-generate_daily_report-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule
                    ->command("task:generate_weeky_report {$companyId} {$status}")
                    // ->everyMinute()
                    ->dailyAt('04:00')
                    ->runInBackground()
                    ->appendOutputTo(storage_path("logs/$monthYear-generate_weeky_report-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                $schedule
                    ->command("task:generate_monthly_report {$companyId} {$status}")
                    // ->everyMinute()
                    ->monthlyOn(1, "04:30")
                    ->runInBackground()
                    ->appendOutputTo(storage_path("logs/$monthYear-generate_monthly_report-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }

            $schedule
                ->command("task:send_whatsapp_notification {$companyId}")
                // ->everyMinute()
                ->dailyAt('09:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$monthYear-send-whatsapp-notification-{$companyId}.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_leaves $companyId")
                //->everyFiveMinutes()
                ->dailyAt('01:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$monthYear-leaves-$companyId.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_holidays $companyId")
                //->everyTenMinutes()
                ->dailyAt('01:30')
                ->runInBackground()
                ->appendOutputTo(storage_path("logs/$monthYear-holidays-$companyId.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$monthYear-monthly-flexible-holidays-$companyId.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_off $companyId")
                // ->everyMinute()
                ->dailyAt('02:00')
                ->appendOutputTo(storage_path("logs/$monthYear-sync-off-by-day-$companyId.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_visitor_set_expire_dates $companyId")
                ->everyMinute()

                ->appendOutputTo(storage_path("logs/$monthYear-visitor-set-expire-date-$companyId.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_visitor_delete_expired_dates $companyId")

                ->dailyAt('08:00')
                ->appendOutputTo(storage_path("logs/$monthYear-visitor-delete-expired-dates-$companyId.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            // $schedule
            //     ->command("task:sync_off_by_day_week1 $companyId")
            //     // ->everyMinute()
            //     ->dailyAt('02:00')
            //     ->appendOutputTo(storage_path("logs/$monthYear-sync-off-by-day-week-1-$companyId.log"))
            //     ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:sync_off_by_day_week2 $companyId")
            //     // ->everyMinute()
            //     ->dailyAt('02:00')
            //     ->appendOutputTo(storage_path("logs/$monthYear-sync-off-by-day-week-2-$companyId.log"))
            //     ->runInBackground(); // ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            // $schedule
            //     ->command("task:sync_flexible_offs_week1 $companyId")
            //     // ->everyMinute()
            //     ->dailyAt('02:00')
            //     ->appendOutputTo(storage_path("logs/$monthYear-sync-flexible-offs-week-1-$companyId.log"))
            //     ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:sync_flexible_offs_week2 $companyId")
            //     // ->everyMinute()
            //     ->dailyAt('02:00')
            //     ->appendOutputTo(storage_path("logs/$monthYear-sync-flexible-offs-week-2-$companyId.log"))
            //     ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }

        $schedule->call(function () {
            exec('pm2 reload 20');
            info("Log listener restart");
        })->dailyAt('00:00');

        $schedule->call(function () {
            exec('pm2 reload 21');
            info("MyTime2Cloud SDK restarted");
        })->dailyAt('05:15');

        $schedule->call(function () {
            $count = Company::where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
            info($count . "companies has been updated");
        })->dailyAt('00:00');


        $schedule->call(function () {
            exec('chown -R www-data:www-data /var/www/mytime2cloud/backend');
            // Artisan::call('cache:clear');
            info("Cache cleared successfully at " . date("d-M-y H:i:s"));
        })->daily();

        // $schedule
        //     ->command('task:sync_multiinout')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path("logs/$monthYear-logs.log"))
        //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        // $schedule
        //     ->command('task:sync_split_shift')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path("logs/$monthYear-logs.log"))
        //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        // $schedule
        //     ->command('task:update_visitor_company_ids')
        //     // ->everyThirtyMinutes()
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path("logs/$monthYear-logs.log"))
        //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        // $schedule
        //     ->command('task:sync_visitors')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path("logs/$monthYear-visitor-logs.log"))
        //     ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:check_device_health')
            ->hourly()
            ->between('7:00', '23:59')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$monthYear-devices-health.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



        $payroll_settings = PayrollSetting::get(["id", "date", "company_id"]);

        foreach ($payroll_settings as $payroll_setting) {

            $payroll_date = (int) (new \DateTime($payroll_setting->date))->modify('-24 hours')->format('d');

            $schedule
                ->command("task:payslip_generation $payroll_setting->company_id")
                ->monthlyOn((int) $payroll_date, "00:00")
                ->appendOutputTo(storage_path("$monthYear-payslip-generate-$payroll_setting->company_id.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }
        //whatsapp and email notifications
        $models = ReportNotification::get();

        foreach ($models as $model) {
            $scheduleCommand = $schedule->command('task:report_notification_crons ' . $model->id)
                ->runInBackground()
                ->appendOutputTo("custom_cron.log");
            //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            if ($model->frequency == "Daily") {
                $scheduleCommand->dailyAt($model->time);
            } elseif ($model->frequency == "Weekly") {
                $scheduleCommand->weeklyOn($model->day, $model->time);
            } elseif ($model->frequency == "Monthly") {
                $scheduleCommand->monthlyOn($model->day, $model->time);
            }
        }

        $schedule
            ->command('task:render_missing')
            ->dailyAt('02:15')
            // ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path("logs/$monthYear-render-missing-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

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
                ->appendOutputTo(storage_path("logs/restart_sdk.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }

        // $devices = DeviceActivesettings::with('devices')->get();

        // $monthYear = date('Y-m-d');

        // foreach ($devices as $key => $device) {
        //     # code...

        //     // Define the date range from January 1, 2023, to February 28, 2023
        //     $startDate = Carbon::createFromFormat('Y-m-d', $device->date_from);
        //     $endDate = Carbon::createFromFormat('Y-m-d', $device->date_to);

        //     // Define an array of times



        //     $open_json = $device->open_json;
        //     $open_settings_array = json_decode($open_json);


        //     foreach ($open_settings_array as $key => $value) {

        //         $schedule
        //             ->command("task:AccessControlTimeSlots {$device->devices->device_id} HoldDoor")
        //             ->days([$key])
        //             ->between($startDate, $endDate)
        //             ->times([$value])
        //             ->withoutOverlapping()
        //             ->appendOutputTo(storage_path("logs/$monthYear-device_access-control-time-slot-logs.log"))
        //             ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        //     }

        //     $close_json = $device->close_json;
        //     $close_settings_array = json_decode($close_json);


        //     foreach ($close_settings_array as $key => $value) {

        //         $schedule
        //             ->command("task:AccessControlTimeSlots {$device->device->device_id} CloseDoor")
        //             ->days([$key])
        //             ->between($startDate, $endDate)
        //             ->times([$value])
        //             ->withoutOverlapping()
        //             ->appendOutputTo(storage_path("logs/$monthYear-device_access-control-time-slot-logs.log"))
        //             ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        //     }
        // }




        // $devices = AccessControlTimeSlot::get();

        // foreach ($devices as $device) {
        //     foreach ($device->json as $slot) {

        //         $schedule
        //             ->command("task:AccessControlTimeSlots {$device->device_id} HoldDoor")
        //             // ->everyThirtyMinutes()
        //             ->everyMinute()
        //             ->dailyAt($slot["startTimeOpen"])
        //             ->withoutOverlapping()
        //             ->appendOutputTo(storage_path("logs/$monthYear-access-control-time-slot-logs.log"))
        //             ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        //         $schedule
        //             ->command("task:AccessControlTimeSlots {$device->device_id} CloseDoor")
        //             // ->everyThirtyMinutes()
        //             ->everyMinute()
        //             ->dailyAt($slot["endTimeOpen"])
        //             ->withoutOverlapping()
        //             ->appendOutputTo(storage_path("logs/$monthYear-access-control-time-slot-logs.log"))
        //             ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        //     }
        // }
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
