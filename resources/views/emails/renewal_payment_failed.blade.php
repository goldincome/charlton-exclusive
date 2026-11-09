<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renewal payment failed</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #F3F4F6; color: #1F2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background: #F3F4F6; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background: #FFFFFF; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    @include('emails.partials.header')
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 16px;">{{ $greeting ?? 'Hello,' }}</p>
                            <div style="line-height: 1.6; color: #374151;">
                                <p style="margin: 0 0 12px;">We could not collect your membership renewal payment. Your membership is now marked as payment overdue.</p>
                                <p style="margin: 0 0 8px;"><strong>Membership plan:</strong> {{ $planName }}</p>
                                @if (!empty($invoiceNumber))
                                    <p style="margin: 0 0 8px;"><strong>Invoice:</strong> {{ $invoiceNumber }}</p>
                                @endif
                                @if (!empty($amountDue))
                                    <p style="margin: 0 0 16px;"><strong>Amount due:</strong> {{ $amountDue }}</p>
                                @endif
                                <p style="margin: 0 0 16px;">Please pay the outstanding invoice using the secure link below to keep your membership active.</p>
                            </div>
                            @if (!empty($hostedInvoiceUrl))
                                <div style="margin: 28px 0;">
                                    <a href="{{ $hostedInvoiceUrl }}" style="display: inline-block; padding: 12px 28px; background: #C9A961; color: #1A2332; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 15px;">Pay invoice</a>
                                </div>
                            @endif
                            @if (!empty($billingUrl))
                                <p style="margin: 16px 0 0; font-size: 14px; color: #6B7280;"><a href="{{ $billingUrl }}" style="color: #7A1F2E;">View your invoices</a></p>
                            @endif
                        </td>
                    </tr>
                    @include('emails.partials.footer')
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
