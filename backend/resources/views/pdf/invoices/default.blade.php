<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @page { margin: 14mm 14mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            color: #1f2937;
            font-size: 11.5px;
            line-height: 1.5;
        }
        .page { padding: 0; position: relative; }

        /* ---------- Header ---------- */
        .header { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; padding: 0; }
        .header .left  { width: 55%; }
        .header .right { width: 45%; text-align: right; }

        .logo-row { white-space: nowrap; }
        .logo-mark {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #7c3aed;
            color: #ffffff;
            text-align: center;
            line-height: 30px;
            font-size: 18px;
            border-radius: 6px;
            vertical-align: middle;
            font-weight: bold;
        }
        .logo-name {
            display: inline-block;
            margin-left: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            vertical-align: middle;
            letter-spacing: -.2px;
        }
        .pill-paid {
            display: inline-block;
            margin-top: 12px;
            padding: 4px 12px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .5px;
        }
        .pill-paid .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 5px;
            margin-bottom: 1px;
        }

        .meta-card {
            display: inline-block;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 12px 16px;
            text-align: left;
            min-width: 220px;
        }
        .meta-card table { width: 100%; border-collapse: collapse; }
        .meta-card td { padding: 0; vertical-align: top; }
        .meta-card .label {
            color: #6b7280;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
            padding-bottom: 4px;
        }
        .meta-card .value {
            color: #111827;
            font-size: 12.5px;
            font-weight: bold;
        }

        /* ---------- Parties ---------- */
        .parties { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .parties td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .parties td.first { padding-right: 8px; }
        .parties td.last  { padding-left: 8px; }
        .party-block {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .party-block .label {
            color: #6b7280;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .party-block .name {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }
        .party-block .line {
            color: #4b5563;
            font-size: 11.5px;
        }

        /* ---------- Items table (rounded) ---------- */
        .items {
            width: 100%;
            margin-top: 28px;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .items thead th {
            background: #f3f4f6;
            color: #4b5563;
            font-size: 10px;
            letter-spacing: .5px;
            text-transform: uppercase;
            text-align: left;
            padding: 10px 14px;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
        }
        .items thead th:first-child { border-top-left-radius: 7px; }
        .items thead th:last-child  { border-top-right-radius: 7px; }
        .items tbody tr:last-child td:first-child { border-bottom-left-radius: 7px; }
        .items tbody tr:last-child td:last-child  { border-bottom-right-radius: 7px; }

        .items th.qty,  .items td.qty  { text-align: right; width: 70px; }
        .items th.rate, .items td.rate { text-align: right; width: 110px; }
        .items th.amt,  .items td.amt  { text-align: right; width: 120px; }

        .items tbody td {
            padding: 16px 14px;
            font-size: 12px;
            vertical-align: top;
        }
        .items .desc-title {
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }
        .items .desc-sub {
            color: #7c3aed;
            font-size: 11px;
        }

        /* ---------- Bottom split: notes (left) + totals (right) ---------- */
        .summary { width: 100%; margin-top: 22px; border-collapse: collapse; }
        .summary > tbody > tr > td { vertical-align: top; padding: 0; }
        .summary .left-col  { width: 50%; padding-right: 24px; }
        .summary .right-col { width: 50%; }

        .notes-block .title {
            color: #6b7280;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .notes-block .body {
            color: #4b5563;
            font-size: 11px;
            line-height: 1.65;
        }
        .signoff {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px dashed #e5e7eb;
            color: #6b7280;
            font-size: 11px;
        }
        .signoff strong { color: #111827; }

        /* ---------- Totals ---------- */
        .totals {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 0;
            font-size: 12px;
        }
        .totals td.l {
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-size: 10.5px;
            font-weight: bold;
        }
        .totals td.r {
            text-align: right;
            color: #111827;
            font-weight: bold;
            font-size: 12.5px;
        }
        .totals tr.divider td { border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .totals tr.total-amount td.l { color: #111827; }
        .totals tr.total-amount td.r { font-size: 14px; }
        .totals tr.paid td.r { color: #047857; }

        .balance-bar {
            margin-top: 12px;
            background: #7c3aed;
            color: #ffffff;
            border-radius: 8px;
            padding: 14px 18px;
        }
        .balance-bar table { width: 100%; border-collapse: collapse; }
        .balance-bar td { padding: 0; }
        .balance-bar .l {
            color: #ddd6fe;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-size: 11px;
            font-weight: bold;
        }
        .balance-bar .r {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }

        /* ---------- Footer ---------- */
        .footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 10.5px;
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="header">
            <tr>
                <td class="left">
                    <div class="logo-row">
                        <span class="logo-mark">M</span>
                        <span class="logo-name">{{ config('app.name') }}</span>
                    </div>
                    @if ($invoice->sent_at || $payment)
                        <div class="pill-paid"><span class="dot"></span>PAID</div>
                    @endif
                </td>
                <td class="right">
                    <div class="meta-card">
                        <table>
                            <tr>
                                <td class="label">Invoice No.</td>
                                <td class="label">Date</td>
                            </tr>
                            <tr>
                                <td class="value">{{ $invoice->number }}</td>
                                <td class="value">{{ \Illuminate\Support\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="parties">
            <tr>
                <td class="first">
                    <div class="party-block">
                        <div class="label">From</div>
                        <div class="name">{{ config('app.name') }}</div>
                        <div class="line">support@mytime2cloud.com</div>
                        <div class="line">mytime2cloud.com</div>
                    </div>
                </td>
                <td class="last">
                    <div class="party-block">
                        <div class="label">Billed To</div>
                        <div class="name">{{ $company->name ?? '—' }}</div>
                        @if (!empty($company->location))
                            <div class="line">{{ $company->location }}</div>
                        @endif
                        @if (!empty($company->p_o_box_no))
                            <div class="line">P.O. Box {{ $company->p_o_box_no }}</div>
                        @endif
                        @if (!empty(optional($company->user)->email))
                            <div class="line">{{ $company->user->email }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="qty">Qty</th>
                    <th class="rate">Rate</th>
                    <th class="amt">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="desc-title">{{ $invoice->description }}</div>
                        <div class="desc-sub">
                            Payment received on {{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                            via {{ ucfirst($payment->method) }}@if ($payment->reference_no) · Ref {{ $payment->reference_no }}@endif
                        </div>
                    </td>
                    <td class="qty">1</td>
                    <td class="rate">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                    <td class="amt">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td class="left-col">
                    <div class="notes-block">
                        <div class="title">Notes &amp; Terms</div>
                        <div class="body">
                            Thank you for your business. This invoice confirms that payment has been received in full. Please retain this document for your records. For any clarifications, reach out to our support team at support@mytime2cloud.com.
                        </div>
                    </div>

                    <div class="signoff">
                        <strong>{{ config('app.name') }}</strong><br>
                        Issued {{ \Illuminate\Support\Carbon::parse($invoice->issue_date)->format('M d, Y') }} ·
                        Invoice {{ $invoice->number }}
                    </div>
                </td>
                <td class="right-col">
                    <table class="totals">
                        <tr>
                            <td class="l">Subtotal</td>
                            <td class="r">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                        </tr>
                        @if ((float) $invoice->tax_percent > 0)
                            <tr>
                                <td class="l">VAT ({{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%)</td>
                                <td class="r">{{ $invoice->currency }} {{ number_format((float) $invoice->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="divider total-amount">
                            <td class="l">Total Amount</td>
                            <td class="r">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td>
                        </tr>
                        <tr class="paid">
                            <td class="l">Amount Paid</td>
                            <td class="r">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td>
                        </tr>
                    </table>

                    <div class="balance-bar">
                        <table>
                            <tr>
                                <td class="l">Balance Due</td>
                                <td class="r">{{ $invoice->currency }} 0.00</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            &copy; {{ \Illuminate\Support\Carbon::parse($invoice->issue_date)->format('Y') }} {{ config('app.name') }} · This is a computer-generated invoice and does not require a physical signature.
        </div>
    </div>
</body>
</html>
