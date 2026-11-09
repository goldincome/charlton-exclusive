<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership cancelled</title>
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
                                <p style="margin: 0 0 12px;">Your membership has been cancelled by the club.</p>
                                <p style="margin: 0 0 8px;"><strong>Membership plan:</strong> {{ $planName }}</p>
                                <p style="margin: 0 0 8px;"><strong>Effective date:</strong> {{ $effectiveDate }}</p>
                                @if (!empty($reason))
                                    <p style="margin: 0 0 16px;"><strong>Reason:</strong> {{ $reason }}</p>
                                @endif
                                <p style="margin: 0 0 16px;">
                                    @if (!empty($immediately))
                                        Your membership access has ended. You can still book rooms and services as a guest at standard rates.
                                    @else
                                        Your membership remains active until the effective date. After that you can still book rooms and services as a guest at standard rates.
                                    @endif
                                </p>
                                <p style="margin: 0 0 16px;">If you believe this was a mistake, please contact the club.</p>
                            </div>
                        </td>
                    </tr>
                    @include('emails.partials.footer')
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
