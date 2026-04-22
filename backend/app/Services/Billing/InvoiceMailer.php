<?php

namespace App\Services\Billing;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class InvoiceMailer
{
    public function __construct(private InvoicePdfService $pdf)
    {
    }

    public function send(Invoice $invoice, ?string $customMessage = null): void
    {
        $invoice->loadMissing(['company.user']);

        $email = optional(optional($invoice->company)->user)->email;

        if (! $email) {
            throw new RuntimeException(
                "Invoice {$invoice->number}: company has no linked user email to send to."
            );
        }

        $pdfBinary = $this->pdf->render($invoice);

        Mail::to($email)->send(new InvoiceMail($invoice, $customMessage, $pdfBinary));

        $invoice->update(['sent_at' => now()]);
    }
}
