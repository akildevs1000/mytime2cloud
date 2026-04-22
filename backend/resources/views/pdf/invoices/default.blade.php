<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }
        .page { padding: 36px 42px; }
        .row { width: 100%; }
        .row:after { content: ""; display: block; clear: both; }
        .col-left  { float: left;  width: 55%; }
        .col-right { float: right; width: 45%; text-align: right; }
        h1 {
            font-size: 28px;
            letter-spacing: 4px;
            color: #7c3aed;
            margin: 0 0 6px 0;
            text-transform: uppercase;
        }
        .muted { color: #6b7280; }
        .label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 2px;
        }
        .value { font-size: 13px; color: #111827; }
        .section {
            margin-top: 22px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .grid { width: 100%; }
        .grid td {
            padding: 6px 10px 6px 0;
            vertical-align: top;
        }
        .desc-box {
            margin-top: 10px;
            padding: 14px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 13px;
            color: #111827;
            white-space: pre-wrap;
        }
        table.totals {
            float: right;
            width: 280px;
            margin-top: 16px;
            border-collapse: collapse;
        }
        table.totals td {
            padding: 8px 12px;
            font-size: 13px;
        }
        table.totals tr.total td {
            background: #7c3aed;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
        }
        table.totals tr + tr td {
            border-top: 1px solid #e5e7eb;
        }
        .footer {
            margin-top: 60px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="row">
            <div class="col-left">
                <h1>Invoice</h1>
                <div class="muted">{{ config('app.name') }}</div>
            </div>
            <div class="col-right">
                <div class="label">Invoice #</div>
                <div class="value">{{ $invoice->number }}</div>
                <div style="height:8px;"></div>
                <div class="label">Issue Date</div>
                <div class="value">{{ \Illuminate\Support\Carbon::parse($invoice->issue_date)->format('d M Y') }}</div>
                @if ((float) $invoice->tax_percent > 0)
                    <div style="height:8px;"></div>
                    <div class="label">VAT</div>
                    <div class="value">{{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%</div>
                @endif
            </div>
        </div>

        <div class="section">
            <table class="grid">
                <tr>
                    <td style="width:50%;">
                        <div class="label">Billed To</div>
                        <div class="value" style="font-size:14px;font-weight:700;">{{ $company->name ?? '—' }}</div>
                        @if (!empty($company->location))
                            <div class="muted" style="margin-top:4px;">{{ $company->location }}</div>
                        @endif
                        @if (!empty($company->p_o_box_no))
                            <div class="muted">P.O. Box {{ $company->p_o_box_no }}</div>
                        @endif
                    </td>
                    <td style="width:50%;">
                        <div class="label">Payment</div>
                        <div class="value">{{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y') }}</div>
                        <div class="muted" style="margin-top:4px;">Method: {{ ucfirst($payment->method) }}</div>
                        @if ($payment->reference_no)
                            <div class="muted">Ref: {{ $payment->reference_no }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="label">Description</div>
            <div class="desc-box">{{ $invoice->description }}</div>
        </div>

        <div class="row">
            <table class="totals">
                <tr>
                    <td class="muted">Subtotal</td>
                    <td style="text-align:right;">{{ $invoice->currency }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
                @if ((float) $invoice->tax_percent > 0)
                    <tr>
                        <td class="muted">VAT ({{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%)</td>
                        <td style="text-align:right;">{{ $invoice->currency }} {{ number_format((float) $invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Total</td>
                    <td style="text-align:right;">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <div style="clear:both;"></div>

        <div class="footer">
            This is a system-generated invoice. For any queries, please contact support.
        </div>
    </div>
</body>
</html>
