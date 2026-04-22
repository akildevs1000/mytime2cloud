<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Payment\StoreRequest;
use App\Models\Company;
use App\Models\Payment;
use App\Services\Billing\InvoiceMailer;
use App\Services\Billing\InvoiceService;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private InvoiceService $invoices,
        private InvoiceMailer $mailer,
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->per_page ?? 20);
        $search  = trim((string) $request->get('q', ''));
        $driver  = config('database.default');
        $likeOp  = $driver === 'pgsql' ? 'ilike' : 'like';

        $query = Payment::with([
                'company:id,name,currency',
                'invoice:id,payment_id,number,total,currency,sent_at',
            ])
            ->when($request->filled('company_id'), fn ($q) =>
                $q->where('company_id', (int) $request->company_id)
            )
            ->when($search !== '', fn ($q) =>
                $q->whereHas('company', fn ($c) =>
                    $c->where('name', $likeOp, "%{$search}%")
                )
            )
            ->when($request->filled('from'), fn ($q) =>
                $q->whereDate('payment_date', '>=', $request->from)
            )
            ->when($request->filled('to'), fn ($q) =>
                $q->whereDate('payment_date', '<=', $request->to)
            )
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        return $query->paginate($perPage);
    }

    public function show($id)
    {
        $record = Payment::with(['company', 'invoice'])->findOrFail($id);

        return response()->json([
            'record' => $record,
            'status' => true,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $company = Company::findOrFail($data['company_id']);

        $paymentData = [
            'amount'       => $data['amount'],
            'method'       => $data['method'],
            'reference_no' => $data['reference_no'] ?? null,
            'payment_date' => $data['payment_date'],
            'notes'        => $data['notes'] ?? null,
            'created_by'   => optional($request->user())->id,
        ];

        $invoiceData = [
            'description' => $data['description'],
            'tax_percent' => $data['tax_percent'] ?? 0,
            'issue_date'  => $data['payment_date'],
        ];

        $invoice = $this->invoices->createFromPayment($paymentData, $invoiceData, $company);

        $emailError = null;
        if ($request->boolean('email_now')) {
            try {
                $this->mailer->send($invoice, $data['email_message'] ?? null);
                $invoice->refresh();
            } catch (Throwable $e) {
                $emailError = $e->getMessage();
            }
        }

        return response()->json([
            'record'      => $invoice->fresh()->load(['payment', 'company']),
            'status'      => true,
            'email_error' => $emailError,
        ], 201);
    }

    public function companyHistory($companyId, Request $request)
    {
        Company::findOrFail($companyId);

        return Payment::where('company_id', $companyId)
            ->with(['company:id,name,currency', 'invoice:id,payment_id,number,total,currency,sent_at'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate((int) ($request->per_page ?? 20));
    }
}
