<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 11.5px;
            line-height: 1.45;
        }
        .page { padding: 0; position: relative; }

        .row { width: 100%; }
        .row:after { content: ""; display: block; clear: both; }
        .col-left  { float: left;  width: 60%; }
        .col-right { float: right; width: 40%; text-align: right; }

        .invoice-title {
            text-align: center;
            color: #7c3aed;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 18px 0;
            line-height: 1;
        }
        .brand .biz {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .brand .tag {
            color: #6b7280;
            font-size: 11px;
        }

        .meta-card {
            display: inline-block;
            text-align: left;
            border: 1px solid #ede9fe;
            background: #faf5ff;
            border-radius: 8px;
            padding: 10px 14px;
            min-width: 180px;
        }
        .meta-card .row-line {
            display: block;
            margin-bottom: 6px;
        }
        .meta-card .row-line:last-child { margin-bottom: 0; }
        .meta-card .label {
            color: #6b7280;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0;
            text-transform: none;
        }
        .meta-card .value {
            font-size: 13px;
            color: #111827;
            font-weight: 700;
            letter-spacing: 0;
        }

        .badge-paid {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 14px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .divider {
            margin: 18px 0 12px;
            border-top: 1px solid #e5e7eb;
        }

        .label {
            color: #6b7280;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .value { font-size: 12.5px; color: #111827; }
        .muted { color: #6b7280; }

        .parties { width: 100%; border-collapse: collapse; }
        .parties td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .party-block {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .party-block .name {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .party-block .line {
            color: #4b5563;
            font-size: 11.5px;
        }
        .gap-cell { width: 14px; padding: 0; }

        .items {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        .items thead th {
            background: #7c3aed;
            color: #ffffff;
            font-size: 10.5px;
            letter-spacing: .8px;
            text-transform: uppercase;
            text-align: left;
            padding: 10px 12px;
            font-weight: 700;
        }
        .items thead th.num   { text-align: center; width: 32px; }
        .items thead th.qty   { text-align: right; width: 60px; }
        .items thead th.rate  { text-align: right; width: 110px; }
        .items thead th.amt   { text-align: right; width: 120px; }
        .items thead th.right { text-align: right; }
        .items tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            vertical-align: top;
        }
        .items tbody tr:last-child td { border-bottom: none; }
        .items td.num   { text-align: center; color: #6b7280; }
        .items td.right { text-align: right; }
        .items .desc-title { font-weight: 600; color: #111827; }

        .summary-row { width: 100%; margin-top: 14px; }
        .summary-row:after { content: ""; display: block; clear: both; }

        .left-pane {
            float: left;
            width: 55%;
            padding-right: 16px;
            min-height: 180px;
            position: relative;
        }
        .stamp-block {
            margin-top: 22px;
            text-align: center;
        }
        .stamp-block .seal {
            display: inline-block;
            transform: rotate(-10deg);
            border: 4px solid #047857;
            color: #047857;
            padding: 10px 30px;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 8px;
            border-radius: 10px;
            background: rgba(255,255,255,0);
        }
        .stamp-block .seal-meta {
            margin-top: 14px;
            color: #6b7280;
            font-size: 11px;
            line-height: 1.6;
        }
        .stamp-block .seal-meta strong { color: #111827; }

        table.totals {
            float: right;
            width: 280px;
            border-collapse: collapse;
        }
        table.totals td {
            padding: 9px 14px;
            font-size: 12.5px;
        }
        table.totals tr td:first-child { color: #6b7280; }
        table.totals tr td:last-child  { text-align: right; color: #111827; font-weight: 600; }
        table.totals tr + tr td { border-top: 1px solid #f1f5f9; }
        table.totals tr.total td {
            background: #7c3aed;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: .3px;
        }
        table.totals tr.total td:first-child { color: #ffffff !important; }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10.5px;
            color: #9ca3af;
            text-align: center;
            line-height: 1.6;
        }
        .footer strong { color: #6b7280; }

    </style>
</head>
<body>
    <div class="page">
        <div class="invoice-title">Invoice</div>

        <div class="row">
            <div class="col-left brand">
                <div class="biz">{{ config('app.name') }}</div>
                <div class="tag">Cloud Attendance &amp; Workforce Management</div>
            </div>
            <div class="col-right">
                <table class="meta-card" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="label">Invoice #</td>
                        <td class="value">{{ $invoice->number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Issue Date</td>
                        <td class="value">{{ \Illuminate\Support\Carbon::parse($invoice->issue_date)->format('d M Y') }}</td>
                    </tr>
                    @if ((float) $invoice->tax_percent > 0)
                        <tr>
                            <td class="label">VAT</td>
                            <td class="value">{{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="divider"></div>

        <table class="parties">
            <tr>
                <td>
                    <div class="party-block">
                        <div class="label">From</div>
                        <div class="name">{{ config('app.name') }}</div>
                        <div class="line">support@mytime2cloud.com</div>
                        <div class="line muted">mytime2cloud.com</div>
                    </div>
                </td>
                <td class="gap-cell"></td>
                <td>
                    <div class="party-block">
                        <div class="label">Billed To</div>
                        <div class="name">{{ $company->name ?? '—' }}</div>
                        @if (!empty($company->location))
                            <div class="line">{{ $company->location }}</div>
                        @endif
                        @if (!empty($company->p_o_box_no))
                            <div class="line muted">P.O. Box {{ $company->p_o_box_no }}</div>
                        @endif
                        @if (!empty(optional($company->user)->email))
                            <div class="line muted">{{ $company->user->email }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th class="num">#</th>
                    <th>Description</th>
                    <th class="qty">Qty</th>
                    <th class="rate">Rate</th>
                    <th class="amt">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="num">1</td>
                    <td>
                        <div class="desc-title">{{ $invoice->description }}</div>
                        <div class="muted" style="margin-top:4px;">
                            Payment received on {{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            via {{ ucfirst($payment->method) }}@if ($payment->reference_no) · Ref {{ $payment->reference_no }}@endif
                        </div>
                    </td>
                    <td class="right">1</td>
                    <td class="right">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                    <td class="right">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-row">
            <div class="left-pane">
                @if ($invoice->sent_at || $payment)
                    <div class="stamp-block">
                        <div class="seal">PAID</div>
                        <div class="seal-meta">
                            <strong>{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</strong> received on
                            {{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') }}<br>
                            via {{ ucfirst($payment->method) }}@if ($payment->reference_no) · Ref {{ $payment->reference_no }}@endif
                        </div>
                    </div>
                @endif
            </div>

            <table class="totals">
                <tr>
                    <td>Subtotal</td>
                    <td>{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
                @if ((float) $invoice->tax_percent > 0)
                    <tr>
                        <td>VAT ({{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%)</td>
                        <td>{{ $invoice->currency }} {{ number_format((float) $invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Amount Paid</td>
                    <td style="color:#047857 !important;">- {{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td>
                </tr>
                <tr class="total">
                    <td>Balance Due</td>
                    <td>{{ $invoice->currency }} 0.00</td>
                </tr>
            </table>
        </div>

        <div style="clear:both;"></div>

        <div class="footer">
            <strong>{{ config('app.name') }}</strong> &nbsp;·&nbsp; This is a system-generated invoice and does not require a signature.<br>
            For any queries about this invoice, please contact <strong>support@mytime2cloud.com</strong>.
        </div>
    </div>
</body>
</html>
