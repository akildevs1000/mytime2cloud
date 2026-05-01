<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailable;

class InvoiceMail extends Mailable
{

    public Invoice $invoice;
    public ?string $customMessage;
    public string $pdfBinary;

    public function __construct(Invoice $invoice, ?string $customMessage, string $pdfBinary)
    {
        $this->invoice       = $invoice;
        $this->customMessage = $customMessage;
        $this->pdfBinary     = $pdfBinary;
    }

    public function build()
    {
        $companyName = optional($this->invoice->company)->name ?? 'Customer';

        return $this
            ->subject("Invoice {$this->invoice->number} — {$companyName}")
            ->view('emails.invoice_sent')
            ->with([
                'invoice'     => $this->invoice,
                'company'     => $this->invoice->company,
                'userMessage' => $this->customMessage,
            ])
            ->attachData($this->pdfBinary, "{$this->invoice->number}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
