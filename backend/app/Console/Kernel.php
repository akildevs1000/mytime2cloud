<?php
namespace App\Console;

use App\Models\Company;
use App\Models\PayrollSetting;
use App\Models\ReportNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule)
    {

        $schedule
            ->command("check:mytime2cloud-health")
            ->everyThirtyMinutes()
            ->runInBackground();

        // ----------------------------------- Background Jobs for pdf generation access ------------------------------- //
        $schedule->command("pdf:access-control-report-generate " . date("Y-m-d", strtotime("yesterday")))->dailyAt('04:35')->runInBackground();
        // ----------------------------------- Background Jobs for pdf generation access ------------------------------- //

        $schedule->command('whatsapp:proxy-health-check')
            ->hourly()
            ->withoutOverlapping();

        $schedule->command('monitor:system')->dailyAt('08:00');

        $schedule->command('birthday:wish')->dailyAt('00:00');

        $schedule->command('delete_old_records')->monthlyOn(1, '00:00');

        $schedule
            ->command('sync_datetime_to_device')
            ->dailyAt('02:45')
            ->runInBackground();

        $schedule
            ->command('task:sync_attendance_logs')
            ->everyFifteenMinutes()->runInBackground();

        $schedule
            ->command('task:sync_attendance_ox900_logs') //OX900
            ->everyFifteenMinutes()->runInBackground();

        $schedule
            ->command('task:sync_alarm_logs')
            ->everyFifteenMinutes()->runInBackground();

        // --------------------Daily Report Generation for automation-------------------- //
        $schedule
            ->command("task:generate_daily_report")
            ->dailyAt('09:00')
            ->runInBackground();
        // --------------------Daily Report Generation for automation-------------------- //

        // (new DeviceController())->deviceAccessControllAllwaysOpen($schedule);

        $schedule
            ->command('task:update_company_ids')
            ->everyMinute()
            ->runInBackground();

        // $schedule
        //     ->command('pm2:stopped-ae-processes')
        //     ->everyFourHours();

        $companyIds = Company::pluck("id");

        foreach ($companyIds as $companyId) {

            $schedule
                ->command("alert:offline_device $companyId")
                ->hourly()
                ->runInBackground();

            // ----------------------------------- Background Jobs for pdf generation ------------------------------- //

            $schedule->command("pdf:generate $companyId")->monthlyOn(1, '03:35')->runInBackground();

            $schedule->command("pdf:generate $companyId")
                ->dailyAt('03:35')
                ->when(fn() => now()->day == now()->endOfMonth()->day)
                ->runInBackground();

            // ----------------------------------- Background Jobs for pdf generation ------------------------------- //

            $schedule
                ->command("task:sync_attendance_missing_shift_ids {$companyId} " . date("Y-m-d") . "  ")
                ->everyThirtyMinutes()
                ->runInBackground();

            $schedule
                ->command("task:sync_auto_shift $companyId " . date("Y-m-d"))
                ->everyThirtyMinutes()
                ->runInBackground();

            $schedule
                ->command("task:sync_auto_shift $companyId " . date("Y-m-d", strtotime("yesterday")))
                ->hourly()
                ->between('03:00', '10:00')
                ->runInBackground();

            $schedule
                ->command("task:sync_except_auto_shift $companyId " . date("Y-m-d"))
                ->everyThirtyMinutes()
                ->runInBackground();

            $schedule->command("task:sync_multi_shift {$companyId} " . date("Y-m-d"))
                ->everyThirtyMinutes()
                ->between('5:00', '23:59')
                ->runInBackground();

            $schedule->command("task:sync_multi_shift_dual_day {$companyId} " . date("Y-m-d", strtotime("yesterday")) . " true")
            // ->everyThirtyMinutes()
                ->dailyAt('5:20')
                ->runInBackground();

            // $schedule->command("task:sync_multishift_includes_two_datesonly")
            //     ->everySixHours()
            //     ->runInBackground();

            // $schedule
            //     ->command("task:sync_multi_shift {$companyId} " . date("Y-m-d", strtotime("yesterday")))
            //     ->dailyAt('3:50')
            //     ->runInBackground();

            $schedule
                ->command("task:sync_visitor_attendance {$companyId} " . date("Y-m-d"))
                ->everyFiveMinutes()
                ->runInBackground();

            $schedule
                ->command("default_attendance_seeder {$companyId}")
                ->monthlyOn(1, "00:00")
                ->runInBackground();

            $schedule
                ->command("alert:access_control {$companyId}")
                ->everyFifteenMinutes()
                ->runInBackground();

            $schedule
                ->command("alert:attendance {$companyId}")
                ->everyFifteenMinutes()
                ->runInBackground();

            $schedule
                ->command("task:sync_leaves $companyId")
                ->dailyAt('01:00');

            $schedule
                ->command("task:sync_holidays $companyId")
                ->dailyAt('01:30');

            $schedule
                ->command("task:sync_monthly_flexible_holidays --company_id=$companyId")
                ->dailyAt('02:00')
                ->runInBackground();

            $schedule
                ->command("task:sync_off $companyId")
                ->dailyAt('02:00')
                ->runInBackground();

            $schedule
                ->command("task:sync_visitor_set_expire_dates $companyId")
                ->everyFiveMinutes()
                ->runInBackground();
        }

        $schedule
            ->command("task:files-delete-old-log-files")
            ->dailyAt('23:30')
            ->runInBackground();

        // $schedule->call(function () {
        //     $count = Company::where("is_offline_device_notificaiton_sent", true)->update(["is_offline_device_notificaiton_sent" => false, "offline_notification_last_sent_at" => date('Y-m-d H:i:s')]);
        // })->dailyAt('05:00');
        //->withoutOverlapping();
        $schedule->call(function () {
            exec('chown -R www-data:www-data /var/www/mytime2cloud/backend');
            // Artisan::call('cache:clear');
            // info("Cache cleared successfully at " . date("d-M-y H:i:s"));
        })->hourly();

        $schedule
            ->command('task:render_missing')
            ->dailyAt('02:15');

        $payroll_settings = PayrollSetting::get(["id", "date", "company_id"]);

        foreach ($payroll_settings as $payroll_setting) {

            $payroll_date = (int) (new \DateTime($payroll_setting->date))->modify('-24 hours')->format('d');

            $schedule
                ->command("task:payslip_generation $payroll_setting->company_id")
                ->monthlyOn((int) $payroll_date, "00:00");
        }

        //whatsapp and email notifications
        $models = ReportNotification::where("type", "attendance")
        // ->orWhere("type", "automation")
            ->get();

        foreach ($models as $model) {

            $companyId = $model->company_id;

            $schedule
                ->command("task:report_notification_crons $companyId")
                ->dailyAt($model->time)
                ->runInBackground();
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
