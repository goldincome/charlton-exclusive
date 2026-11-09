<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application approved</title>
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
                                <p style="margin: 0 0 12px;">We have verified your documents and approved your application. Welcome to {{ $clubName ?? config('club.name') }}.</p>
                                <p style="margin: 0 0 8px;"><strong>Membership plan:</strong> {{ $planName }}</p>
                                @if (!empty($applicationReference))
                                    <p style="margin: 0 0 16px;"><strong>Application reference:</strong> {{ $applicationReference }}</p>
                                @endif
                                <p style="margin: 0 0 12px;"><strong>Complete your payment to activate your membership.</strong></p>
                                <p style="margin: 0 0 8px;"><strong>Yearly:</strong> {{ $annualFee }} per year — includes member discounts.</p>
                                @if (!empty($monthlyAvailable))
                                    <p style="margin: 0 0 16px;"><strong>Monthly:</strong> {{ $monthlyFee }} per month — members-only access at standard rates.</p>
                                @else
                                    <p style="margin: 0 0 16px;">Monthly billing is not available for this plan.</p>
                                @endif
                                <p style="margin: 0 0 16px;">There is no entrance fee. Your subscription renews automatically and only yearly members receive discounts.</p>
                            </div>
                            @if (!empty($payUrl))
                                <div style="margin: 28px 0;">
                                    <a href="{{ $payUrl }}" style="display: inline-block; padding: 12px 28px; background: #C9A961; color: #1A2332; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 15px;">Choose billing &amp; pay</a>
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
