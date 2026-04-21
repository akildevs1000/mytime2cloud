<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyExpiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $companyName;
    public $contactName;
    public $expiryDate;
    public $daysRemaining;

    public function __construct($companyName, $contactName, $expiryDate, $daysRemaining)
    {
        $this->companyName   = $companyName;
        $this->contactName   = $contactName;
        $this->expiryDate    = $expiryDate;
        $this->daysRemaining = $daysRemaining;
    }

    public function build()
    {
        $subject = $this->daysRemaining <= 0
            ? "Subscription expires today — {$this->companyName}"
            : "Subscription expires in {$this->daysRemaining} day(s) — {$this->companyName}";

        return $this
            ->subject($subject)
            ->view('emails.company_expiry_notification')
            ->with([
                'companyName'   => $this->companyName,
                'contactName'   => $this->contactName,
                'expiryDate'    => $this->expiryDate,
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
