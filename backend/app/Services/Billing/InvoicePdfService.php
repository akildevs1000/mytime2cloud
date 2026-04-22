<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function render(Invoice $invoice): string
    {
        $invoice->loadMissing(['company', 'payment']);

        return Pdf::loadView('pdf.invoices.default', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'payment' => $invoice->payment,
        ])->setPaper('a4')->output();
    }

    public function stream(Invoice $invoice)
    {
        $invoice->loadMissing(['company', 'payment']);

        return Pdf::loadView('pdf.invoices.default', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'payment' => $invoice->payment,
        ])->setPaper('a4')->download("{$invoice->number}.pdf");
    }
}
