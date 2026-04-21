<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Mail\CompanyExpiryMail;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringCompanies extends Command
{
    protected $signature = 'company:notify-expiry {--days=30 : Notify when expiry is within this many days}';

    protected $description = 'Send a daily email to companies whose subscription expires within N days (default 30).';

    public function handle()
    {
        $window = (int) $this->option('days');
        $today  = Carbon::today();
        $until  = (clone $today)->addDays($window);

        Log::channel('company_expiry')->info("***** Cron started (window={$window}d) *****");

        $companies = Company::with('user', 'contact')
            ->where('account_type', 'company')
            ->whereNotNull('expiry')
            ->whereDate('expiry', '>=', $today->toDateString())
            ->whereDate('expiry', '<=', $until->toDateString())
            ->get();

        if ($companies->isEmpty()) {
            Log::channel('company_expiry')->info("No companies expiring in the next {$window} days.");
            $this->info("No companies expiring in the next {$window} days.");

            Log::channel('company_expiry')->info("***** Cron ended *****");
            return;
        }

        foreach ($companies as $company) {
            $expiryCarbon  = Carbon::parse($company->getRawOriginal('expiry'));
            $daysRemaining = $today->diffInDays($expiryCarbon, false);

            $recipients = collect([
                optional($company->user)->email,
                
            ])->filter()->unique()->values()->all();

            if (empty($recipients)) {
                Log::channel('company_expiry')->warning(
                    "Company {$company->id} ({$company->name}) has no recipients"
                );
                continue;
            }

            try {
                Mail::to($recipients)
                    ->bcc("francisgill1000@gmail.com")
                    ->queue(new CompanyExpiryMail(
                        $company->name,
                        optional($company->contact)->name,
                        $expiryCarbon->format('d M Y'),
                        $daysRemaining
                    ));

                Log::channel('company_expiry')->info("Email queued", [
                    'company_id' => $company->id,
                    'company' => $company->name,
                    'expiry' => $expiryCarbon->toDateString(),
                    'days_remaining' => $daysRemaining,
                    'recipients' => $recipients,
                ]);

                $this->info("Queued email for {$company->name}");
            } catch (\Throwable $e) {
                Log::channel('company_expiry')->error("Queue failed", [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Failed for {$company->name}");
            }
        }

        Log::channel('company_expiry')->info("***** Cron ended *****");
    }
}
