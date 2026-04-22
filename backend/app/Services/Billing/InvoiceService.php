<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private InvoiceNumberService $numbers)
    {
    }

    public function createFromPayment(array $paymentData, array $invoiceData, Company $company): Invoice
    {
        return DB::transaction(function () use ($paymentData, $invoiceData, $company) {
            $currency = $company->currency ?? 'AED';

            $payment = Payment::create([
                'company_id'   => $company->id,
                'amount'       => round((float) $paymentData['amount'], 2),
                'currency'     => $currency,
                'method'       => $paymentData['method'],
                'reference_no' => $paymentData['reference_no'] ?? null,
                'payment_date' => $paymentData['payment_date'],
                'notes'        => $paymentData['notes'] ?? null,
                'created_by'   => $paymentData['created_by'] ?? null,
            ]);

            $subtotal   = round((float) $paymentData['amount'], 2);
            $taxPercent = round((float) ($invoiceData['tax_percent'] ?? 0), 2);
            $taxAmount  = round($subtotal * $taxPercent / 100, 2);
            $total      = round($subtotal + $taxAmount, 2);

            $invoice = Invoice::create([
                'payment_id'  => $payment->id,
                'company_id'  => $company->id,
                'number'      => $this->numbers->next(),
                'issue_date'  => $invoiceData['issue_date'] ?? $paymentData['payment_date'],
                'description' => $invoiceData['description'],
                'subtotal'    => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount'  => $taxAmount,
                'total'       => $total,
                'currency'    => $currency,
            ]);

            return $invoice->load(['payment', 'company']);
        });
    }
}
