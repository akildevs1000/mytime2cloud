<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Invoice\EmailRequest;
use App\Models\Invoice;
use App\Services\Billing\InvoiceMailer;
use App\Services\Billing\InvoicePdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoicePdfService $pdf,
        private InvoiceMailer $mailer,
    ) {
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->per_page ?? 20);
        $search  = trim((string) $request->get('q', ''));
        $driver  = config('database.default');
        $likeOp  = $driver === 'pgsql' ? 'ilike' : 'like';

        $query = Invoice::with([
                'company:id,name,currency',
                'payment:id,payment_date,method,reference_no',
            ])
            ->when($request->filled('company_id'), fn ($q) =>
                $q->where('company_id', (int) $request->company_id)
            )
            ->when($search !== '', fn ($q) =>
                $q->where(function ($inner) use ($search, $likeOp) {
                    $inner->where('number', $likeOp, "%{$search}%")
                        ->orWhereHas('company', fn ($c) =>
                            $c->where('name', $likeOp, "%{$search}%")
                        );
                })
            )
            ->when($request->filled('from'), fn ($q) =>
                $q->whereDate('issue_date', '>=', $request->from)
            )
            ->when($request->filled('to'), fn ($q) =>
                $q->whereDate('issue_date', '<=', $request->to)
            )
            ->orderByDesc('issue_date')
            ->orderByDesc('id');

        return $query->paginate($perPage);
    }

    public function show($id)
    {
        $record = Invoice::with(['company', 'payment'])->findOrFail($id);

        return response()->json([
            'record' => $record,
            'status' => true,
        ]);
    }

    public function pdf($id)
    {
        $invoice = Invoice::with(['company', 'payment'])->findOrFail($id);

        return $this->pdf->stream($invoice);
    }

    public function email($id, EmailRequest $request)
    {
        $invoice = Invoice::with(['company.user', 'payment'])->findOrFail($id);

        $this->mailer->send($invoice, $request->validated()['email_message'] ?? null);

        return response()->json([
            'status'  => true,
            'sent_at' => $invoice->fresh()->sent_at,
        ]);
    }
}
