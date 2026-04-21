<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscription Expiry Notice</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:32px 0;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);">
                    <tr>
                        <td style="background:#7c3aed;padding:24px 28px;color:#ffffff;">
                            <div style="font-size:13px;letter-spacing:.4px;text-transform:uppercase;opacity:.8;">Mytime2Cloud</div>
                            <div style="font-size:22px;font-weight:700;margin-top:4px;">Subscription Expiry Notice</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 14px 0;font-size:15px;">
                                Hello {{ $contactName ?: 'Admin' }},
                            </p>

                            @if ($daysRemaining <= 0)
                                <p style="margin:0 0 14px 0;font-size:15px;">
                                    The subscription for <strong>{{ $companyName }}</strong> expires <strong>today</strong>.
                                </p>
                            @elseif ($daysRemaining == 1)
                                <p style="margin:0 0 14px 0;font-size:15px;">
                                    The subscription for <strong>{{ $companyName }}</strong> expires <strong>tomorrow</strong>.
                                </p>
                            @else
                                <p style="margin:0 0 14px 0;font-size:15px;">
                                    The subscription for <strong>{{ $companyName }}</strong> will expire in
                                    <strong>{{ $daysRemaining }} days</strong>.
                                </p>
                            @endif

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin:18px 0;">
                                <tr>
                                    <td style="padding:14px 18px;font-size:14px;">
                                        <div style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Expiry Date</div>
                                        <div style="font-size:17px;font-weight:700;margin-top:4px;color:#111827;">
                                            {{ $expiryDate }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 14px 0;font-size:14px;color:#4b5563;">
                                Please renew before the expiry date to avoid service interruption.
                                If the renewal is already in progress, you may safely disregard this message.
                            </p>

                            <p style="margin:20px 0 14px 0;font-size:14px;color:#4b5563;">
                                Need assistance with your renewal? Contact our support team at:<br>
                                <strong style="color:#7c3aed; font-size:16px;">+971 55 330 3991</strong>
                            </p>

                            <p style="margin:24px 0 0 0;font-size:14px;color:#4b5563;">
                                Regards,<br>
                                <strong>{{ config('app.name') }}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f9fafb;padding:16px 28px;font-size:12px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb;">
                            This is an automated notification. Please do not reply to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
