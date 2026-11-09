<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application received</title>
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
                                <p style="margin: 0 0 12px;">Thank you for submitting your membership application to {{ $clubName ?? config('club.name') }}.</p>
                                <p style="margin: 0 0 8px;"><strong>Application reference:</strong> {{ $applicationReference }}</p>
                                <p style="margin: 0 0 16px;">Your application is now pending review.</p>
                                <p style="margin: 0 0 12px;"><strong>Next step: visit the office</strong></p>
                                <p style="margin: 0 0 16px;">
                                    Please bring an accepted original ID (national ID card, driver's licence or international passport)
                                    to the office so we can verify your documents. Once your application is approved, we will email
                                    you a link to choose monthly or yearly billing and complete payment.
                                </p>
                                <p style="margin: 0 0 8px;"><strong>Office</strong></p>
                                <p style="margin: 0 0 16px;">
                                    {{ $clubName ?? config('club.name') }}<br>
                                    {{ $clubAddress ?? config('club.address') }}<br>
                                    {{ $clubPhone ?? config('club.phone') }}<br>
                                    {{ $clubEmail ?? config('club.email') }}
                                </p>
                            </div>
                            @if (!empty($membershipUrl))
                                <div style="margin: 28px 0;">
                                    <a href="{{ $membershipUrl }}" style="display: inline-block; padding: 12px 28px; background: #C9A961; color: #1A2332; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 15px;">View your application</a>
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
