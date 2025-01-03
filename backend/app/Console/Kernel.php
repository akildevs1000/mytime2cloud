<?php

namespace App\Console;

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SDKController;
use App\Http\Controllers\ThemeController;
use App\Models\AccessControlTimeSlot;
use App\Models\Company;
use App\Models\DeviceActivesettings;
use App\Models\PayrollSetting;
use App\Models\ReportNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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


        // $file_name_raw = "test.txt";
        // Storage::append($file_name_raw,  date("d-m-Y H:i:s") . ' - Devices test listed');

        // $schedule->call(function () {
        //     $file_name_raw = "test.txt";
        //     Storage::append($file_name_raw,  date("d-m-Y H:i:s") . ' - Devices listed');
        // })->everyMinute()->appendOutputTo(storage_path("test.txt"));
        //-------------------------------------------------------------------------------------------------------------------------
        if (env('APP_ENV') != 'desktop') {
            $schedule->call(function () {
                exec('pm2 reload 4');
                info("MyTime2Cloud SDK Production");
            })->dailyAt('05:15');
        }

        $monthYear = date("M-Y");


        $schedule
            ->command('task:sync_attendance_logs')
            ->everyMinute()
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/" . date("d-M-y") . "-sync_attendance_logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:sync_attendance_camera_logs')
            ->everyMinute()
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/" . date("d-M-y") . "-attendance-camera2-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        $schedule
            ->command('task:sync_alarm_logs')
            ->everyMinute()
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/alarm/" . date("d-M-y") . "-alarm-logs-laravel.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));




        //Schedule Device Access Control 
        if (env('APP_ENV') != 'desktop') {
            $schedule->call(function () {
                exec('pm2 reload 3');
                //info("Camera Log listener restarted");
            })
                ->everyMinute()
                ->appendOutputTo(storage_path("kernal_logs/camera_log_listner/" . date("d-M-y") . "-camera-log-listner.log"));
        }
        (new DeviceController())->deviceAccessControllAllwaysOpen($schedule);



        // $schedule->call(function () {
        //     exec('pm2 reload 3');
        //     info("Camera Log listener restart");
        // })->dailyAt('00:00');


        // $schedule->call(function () {
        //     exec('pm2 reload 11');
        //     info("Log listener backup restart");
        // })->monthlyOn(1, "00:00");


        // $schedule
        //     ->command('task:sync_attendance_logs')
        //     ->everyMinute()

        //     //->withoutOverlapping()
        //     //->appendOutputTo(storage_path("kernal_logs/" . date("d-M-y") . "-attendance-logs.log"))
        // ; //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



        $schedule
            ->command('task:update_company_ids')
            // ->everyThirtyMinutes()
            ->everyMinute()
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/$monthYear-update_company_ids.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        // $schedule
        //     ->command('task:alarm_update_company_ids')
        //     // ->everyThirtyMinutes()
        //     ->everyMinute()
        //     //->withoutOverlapping()
        //     ->appendOutputTo(storage_path("kernal_logs/alarm-$monthYear-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


        $companyIds = Company::pluck("id");
        //step 1 ;


        foreach ($companyIds as $companyId) {

            $schedule->command("pdf:generate $companyId")->dailyAt('03:35')->runInBackground();

            $schedule->command("pdf:access-control-report-generate {$companyId} " . date("Y-m-d", strtotime("yesterday")))
                ->dailyAt('04:35')->runInBackground();

            $company_log = date("Y-m-d") . "-C" . $companyId;
            $schedule
                ->command("task:sync_attendance_missing_shift_ids {$companyId} " . date("Y-m-d") . "  ")

                ->everyThirtyMinutes()

                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/shifts/auto/$company_log.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $schedule
                ->command("task:sync_auto_shift {$companyId} " . date("Y-m-d") . " false")
                ->everyFourMinutes()
                //->everyFiveMinutes()

                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/shifts/auto/$company_log.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_auto_shift {$companyId} " . date("Y-m-d") . " true")
                ->everyThirtyMinutes()
                //->everyFiveMinutes()

                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/shifts/auto/$company_log.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            //if ($companyId == 1) 
            {
                $schedule
                    ->command("send_notificatin_for_offline_devices {$companyId}")
                    //  ->dailyAt('09:00')
                    ->everySixHours()
                    // ->everyThirtyMinutes()
                    // ->everyMinute()
                    // ->runInBackground()
                    //->withoutOverlapping()
                    ->appendOutputTo(storage_path("kernal_logs/$company_log-send-notification-for-offline-devices.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
            }

            // $schedule
            //     ->command("task:sync_multi_shift_night {$companyId} " . date("Y-m-d", strtotime("yesterday")))
            //     ->hourly()
            //     // ->between('00:00', '05:59')
            //     ->runInBackground()
            //     //->withoutOverlapping()
            //     ->appendOutputTo(storage_path("kernal_logs/shifts/multi_night/$company_log.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("render:night_shift {$companyId} " . date("Y-m-d", strtotime("yesterday")))
                ->everyTenMinutes();

            $schedule
                ->command("render:multi_shift {$companyId} " . date("Y-m-d", strtotime("yesterday")))
                ->everyTenMinutes();

            // $schedule
            //     ->command("task:sync_multi_shift {$companyId} " . date("Y-m-d"))
            //     ->everyMinute()
            //     ->between('06:00', '23:59')
            //     ->runInBackground()
            //     ->withoutOverlapping()
            //     ->appendOutputTo(storage_path("kernal_logs/shifts/multi/$monthYear.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            // $schedule
            //     ->command("task:sync_filo_shift {$companyId} " . date("Y-m-d"))
            //     // ->hourly()
            //     ->everyMinute()
            //     ->runInBackground()
            //     ->withoutOverlapping()
            //     ->appendOutputTo(storage_path("kernal_logs/shifts/filo/$monthYear.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:sync_night_shift {$companyId} " . date("Y-m-d"))
            //     // ->hourly()
            //     ->everyMinute()
            //     ->runInBackground()
            //     ->withoutOverlapping()
            //     ->appendOutputTo(storage_path("kernal_logs/shifts/night/$monthYear.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            // $schedule
            //     ->command("task:sync_single_shift {$companyId} " . date("Y-m-d"))
            //     // ->hourly()
            //     ->everyMinute()
            //     ->runInBackground()
            //     ->withoutOverlapping()
            //     ->appendOutputTo(storage_path("kernal_logs/shifts/single/$monthYear.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:sync_split_shift {$companyId} " . date("Y-m-d"))
            //     ->everyMinute()
            //     // ->dailyAt('09:00')
            //     ->runInBackground()
            //      ->withoutOverlapping()
            //     ->appendOutputTo(storage_path("kernal_logs/$monthYear-sync-split-logs-by-log-type.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_visitor_attendance {$companyId} " . date("Y-m-d"))
                ->everyFiveMinutes()
                // ->dailyAt('09:00')
                //->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path("kernal_logs/$monthYear-sync-visitor-logs-by-log-type.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $schedule
                ->command("default_attendance_seeder {$companyId}")
                ->monthlyOn(1, "00:00")
                ->runInBackground()
                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/$company_log-default-attendance-seeder.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            //whatsapp reports 
            // $array = ['All', "P", "A", "M", "ME"];
            $array = ['All'];

            foreach ($array as $status) {

                $schedule
                    ->command("task:generate_daily_report {$companyId}  {$status}")
                    ->dailyAt('03:45')
                    ->appendOutputTo(storage_path("kernal_logs/$company_log-generate_daily_report.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

                // $schedule
                //     ->command("task:generate_weekly_report {$companyId} {$status}")

                //     ->dailyAt('04:00')
                //     // ->runInBackground()
                //     //->withoutOverlapping()
                //     ->appendOutputTo(storage_path("kernal_logs/$company_log-generate_weekly_report.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            }

            $schedule
                ->command("task:send_whatsapp_notification {$companyId}")

                ->dailyAt('09:00')
                ->runInBackground()
                ->appendOutputTo(storage_path("kernal_logs/$company_log-send-whatsapp-notification.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_leaves $companyId")

                ->dailyAt('01:00')
                // ->runInBackground()
                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/$company_log-leaves.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_holidays $companyId")

                ->dailyAt('01:30')
                // ->runInBackground()
                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/$company_log-holidays.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            $schedule
                ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")

                ->dailyAt('02:00')
                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/$company_log-monthly-flexible-holidays.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $schedule
                ->command("task:sync_off $companyId")

                ->dailyAt('02:00')
                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/$company_log-sync-off-by-day.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $schedule
                ->command("task:sync_visitor_set_expire_dates $companyId")
                ->everyFiveMinutes()
                //->withoutOverlapping()
                ->appendOutputTo(storage_path("kernal_logs/$monthYear-visitor-set-expire-date.log"))
                ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

            // $schedule
            //     ->command("task:sync_visitor_delete_expired_dates $companyId")

            //     ->dailyAt('08:00')
            //     ->appendOutputTo(storage_path("kernal_logs/$monthYear-visitor-delete-expired-dates-$companyId.log"))
            //     ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            // /*------------------------ */
            $schedule->call(function () use ($companyId) {
                $requestArray = array(
                    'company_id' => $companyId,
                );
                $renderRequest = Request::create('/testingggggggggg', 'get', $requestArray);

                return (new ThemeController)->whatsappTodayStats($renderRequest);
            })->everySixHours()

                ->appendOutputTo(storage_path("kernal_logs/" . date("d-M-Y") . "-whatsapp-notifications-desktop.log"));

            // /*------------------------ */
        }

        $schedule
            ->command("task:files-delete-old-log-files")

            ->dailyAt('23:30')
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/$monthYear-delete-old-logs.log"))
            ->runInBackground(); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


        $schedule->call(function () {
            $count = Company::where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
            info($count . "companies has been updated");
        })->dailyAt('00:00');
        //->withoutOverlapping();
        $schedule->call(function () {
            exec('chown -R www-data:www-data /var/www/mytime2cloud/backend');
            // Artisan::call('cache:clear');
            // info("Cache cleared successfully at " . date("d-M-y H:i:s"));
        })->hourly();
        if (env('APP_ENV') != 'desktop') {
            $schedule->call(function () {
                exec('pm2 reload 1');
                // Artisan::call('cache:clear');
                // info("Cache cleared successfully at " . date("d-M-y H:i:s"));
            })->everyThreeHours();
        }
        // $schedule->call(function () {

        //     exec('php artisan cache:clear');
        //     //Artisan::call('cache:clear');
        //     info("Cache cleared successfully at " . date("d-M-y H:i:s"));
        // })->everyFifteenMinutes();


        $schedule
            ->command('task:check_device_health')
            ->hourly()
            ->between('7:00', '23:59')
            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/$monthYear-devices-health.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



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

            $schedule
                ->command("multi:daily_report " . $model->company_id . " " . $model->branch_id)
                ->dailyAt('3:45')
                ->appendOutputTo(storage_path("kernal_logs/$company_log-multi:daily_report.log"));

            //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));


            $command_name = "task:report_notification_crons";

            if ($model->type == "alert") {
                $command_name = "alert:absents";
            }

            $scheduleCommand = $schedule->command("$command_name " . $model->id . ' ' . $model->company_id)
                ->runInBackground();

            if ($model->frequency == "Daily") {
                $scheduleCommand->dailyAt($model->time);
            }
            // elseif ($model->frequency == "Weekly") {
            //     $scheduleCommand->weeklyOn($model->day, $model->time);
            // } elseif ($model->frequency == "Monthly") {
            //     $scheduleCommand->monthlyOn($model->day, $model->time);
            // }
        }

        // $date = date("M-Y");

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

        $schedule
            ->command('task:render_missing')
            ->dailyAt('02:15')

            //->withoutOverlapping()
            ->appendOutputTo(storage_path("kernal_logs/$monthYear-render-missing-logs.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));

        if (env("APP_ENV") == "production") {
            $schedule
                ->command('task:db_backup')
                ->dailyAt('6:00')
                ->appendOutputTo(storage_path("kernal_logs/db_backup.log"))
                ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));



            $schedule
                ->command('restart_sdk')


                ->dailyAt('4:00')
                //->hourly()
                ->appendOutputTo(storage_path("kernal_logs/restart_sdk.log")); //->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        }


        // $date = date('Y-m-d');
        // $devices =  DeviceActivesettings::where(function ($q) {
        //     $q->orWhere('date_from', ">=", date("Y-m-d"));
        //     $q->orWhere('date_to', "<=", date("Y-m-d"));
        // })->get();

        // $weekDays = [0 => "Mon", 1 => "Tue", 2 => "Wed", 3 => "Thu", 4 => "Fri", 5 => "Sat", 6 => "Sun"];

        // foreach ($devices as $key => $device) {

        //     $openJson =  $device['open_json'];

        //     $openJsonArray = json_decode($openJson);

        //     foreach ($openJsonArray as  $key => $time) {

        //         if ($weekDays[$key] == date("D")) {
        //             $schedule
        //                 ->command("task:AccessControlTimeSlots {$device->device_id} HoldDoor")
        //                 // ->everyThirtyMinutes()
        //                 ->everyMinute()
        //                 ->dailyAt($time)
        //                 ->withoutOverlapping()
        //                 ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
        //                 ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        //         }
        //     }
        //     //

        //     $closeJson =  $device['close_json'];

        //     $closeJsonArray = json_decode($closeJson);

        //     foreach ($closeJsonArray as  $key => $time) {

        //         if ($weekDays[$key] == date("D")) {
        //             $schedule
        //                 ->command("task:AccessControlTimeSlots {$device->device_id} CloseDoor")
        //                 // ->everyThirtyMinutes()
        //                 ->everyMinute()
        //                 ->dailyAt($time)
        //                 ->withoutOverlapping()
        //                 ->appendOutputTo(storage_path("logs/$date-access-control-time-slot-logs.log"))
        //                 ->emailOutputOnFailure(env("ADMIN_MAIL_RECEIVERS"));
        //         }
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
