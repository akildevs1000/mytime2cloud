<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Mail\CompanyExpiryMail;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringCompanies extends Command
{
    protected $signature = 'company:notify-expiry {--days=30 : Notify when expiry is within this many days}';

    protected $description = 'Send a daily email to companies whose subscription expires within N days (default 30).';

    public function handle()
    {
        $logger      = new Controller;
        $logFilePath = 'logs/cron/company_expiry_notification';
        $window      = (int) $this->option('days');
        $today       = Carbon::today();
        $until       = (clone $today)->addDays($window);

        $logger->logOutPut($logFilePath, "*****Cron started for company:notify-expiry (window={$window}d) *****");

        $companies = Company::with('user', 'contact')
            ->where('account_type', 'company')
            ->whereNotNull('expiry')
            ->whereDate('expiry', '>=', $today->toDateString())
            ->whereDate('expiry', '<=', $until->toDateString())
            ->get();

        if ($companies->isEmpty()) {
            $logger->logOutPut($logFilePath, "No companies expiring in the next {$window} days.");
            $this->info("No companies expiring in the next {$window} days.");
            $logger->logOutPut($logFilePath, "*****Cron ended for company:notify-expiry *****");
            return;
        }

        $adminEmail = env('ADMIN_MAIL_RECEIVERS');

        foreach ($companies as $company) {
            $expiryCarbon  = Carbon::parse($company->getRawOriginal('expiry'));
            $daysRemaining = $today->diffInDays($expiryCarbon, false);

            $recipients = collect([
                // optional($company->user)->email,
                // optional($company->contact)->email ?? null,
                // $adminEmail,
                "francisgill1000@gmail.com"
            ])->filter()->unique()->values()->all();

            if (empty($recipients)) {
                $logger->logOutPut($logFilePath, "Company {$company->id} ({$company->name}) has no recipients, skipping.");
                continue;
            }

            try {
                Mail::to($recipients)->queue(new CompanyExpiryMail(
                    $company->name,
                    optional($company->contact)->name,
                    $expiryCarbon->format('d M Y'),
                    $daysRemaining
                ));

                $msg = "Queued expiry email for {$company->name} (id={$company->id}) | expiry={$expiryCarbon->toDateString()} | days={$daysRemaining} | to=" . implode(',', $recipients);
                $logger->logOutPut($logFilePath, $msg);
                $this->info($msg);
            } catch (\Throwable $e) {
                $err = "Failed to queue for {$company->name} (id={$company->id}): " . $e->getMessage();
                $logger->logOutPut($logFilePath, $err);
                $this->error($err);
            }
        }

        $logger->logOutPut($logFilePath, "*****Cron ended for company:notify-expiry *****");
    }
}
