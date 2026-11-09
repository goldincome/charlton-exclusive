<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership active</title>
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
                                <p style="margin: 0 0 12px;">Your payment has been received and your membership is now active. Welcome to {{ $clubName ?? config('club.name') }}.</p>
                                <p style="margin: 0 0 8px;"><strong>Membership plan:</strong> {{ $planName ?? 'Membership' }}</p>
                                @if (!empty($applicationReference))
                                    <p style="margin: 0 0 8px;"><strong>Application reference:</strong> {{ $applicationReference }}</p>
                                @endif
                                @if (!empty($billingFrequency))
                                    <p style="margin: 0 0 8px;"><strong>Billing:</strong> {{ $billingFrequency }}</p>
                                @endif
                                @if (!empty($renewsAt))
                                    <p style="margin: 0 0 8px;"><strong>Next renewal:</strong> {{ $renewsAt }}</p>
                                @endif
                                @if (!empty($invoiceNumber))
                                    <p style="margin: 0 0 8px;"><strong>Invoice:</strong> {{ $invoiceNumber }}</p>
                                @endif
                                @if (!empty($amountPaid))
                                    <p style="margin: 0 0 16px;"><strong>Amount paid:</strong> {{ $amountPaid }}</p>
                                @endif
                                @if (!empty($renewsAt))
                                    <p style="margin: 0 0 8px;"><strong>Next renewal:</strong> {{ $renewsAt }}</p>
                                @endif
                                <p style="margin: 0 0 16px;">
                                    @if (($billingFrequency ?? '') === 'Monthly')
                                        As a monthly member you have members-only access. Switch to yearly billing at any time to receive member discounts.
                                    @else
                                        As a yearly member you receive member discounts on sections and services.
                                    @endif
                                </p>
                                <p style="margin: 0 0 16px;">Your subscription renews automatically. If a renewal payment fails, we will email you the invoice and a secure payment link to keep your membership active.</p>
                            </div>
                            @if (!empty($membershipUrl))
                                <div style="margin: 28px 0;">
                                    <a href="{{ $membershipUrl }}" style="display: inline-block; padding: 12px 28px; background: #C9A961; color: #1A2332; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 15px;">View my membership</a>
                                </div>
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
