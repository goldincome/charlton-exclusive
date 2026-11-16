<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Has Been Created</title>
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
                                <p style="margin: 0 0 12px;">An account has been created for you at {{ $clubName ?? config('club.name') }} following your membership application (Reference: <strong>{{ $applicationReference }}</strong>).</p>
                                <p style="margin: 0 0 16px;">You can log in using your email address: <strong>{{ $userEmail }}</strong></p>
                                <p style="margin: 0 0 16px;">To set your password and access your account, please click the button below:</p>
                                <div style="margin: 28px 0;">
                                    <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 28px; background: #C9A961; color: #1A2332; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 15px;">Set Your Password</a>
                                </div>
                                <p style="margin: 0 0 16px; font-size: 14px; color: #6B7280;">Once logged in, you can change your password to whatever you want at any time.</p>
                                <p style="margin: 24px 0 8px;"><strong>Club Office</strong></p>
                                <p style="margin: 0; color: #6B7280;">
                                    {{ $clubName ?? config('club.name') }}<br>
                                    {{ $clubAddress ?? config('club.address') }}<br>
                                    {{ $clubPhone ?? config('club.phone') }}<br>
                                    {{ $clubEmail ?? config('club.email') }}
                                </p>
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
