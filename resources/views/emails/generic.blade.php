<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Message from Charlton Exclusive Club' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #F3F4F6; color: #1F2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background: #F3F4F6; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background: #FFFFFF; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: #1A2332; padding: 24px 32px; text-align: center;">
                            <h1 style="margin: 0; font-family: Georgia, serif; font-size: 24px; color: #E8D5B7; font-weight: normal;">
                                Charlton Exclusive Club
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            @if (isset($greeting))
                                <p style="margin: 0 0 16px; font-size: 16px;">{{ $greeting }}</p>
                            @endif

                            <div style="line-height: 1.6; color: #374151;">
                                {!! $body !!}
                            </div>

                            @if (isset($actionUrl) && isset($actionText))
                                <div style="margin: 32px 0;">
                                    <a href="{{ $actionUrl }}" style="display: inline-block; padding: 12px 28px; background: #C9A961; color: #1A2332; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 15px;">
                                        {{ $actionText }}
                                    </a>
                                </div>
                            @endif

                            @if (isset($footer))
                                <p style="margin: 24px 0 0; font-size: 14px; color: #6B7280;">{{ $footer }}</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #F9FAFB; padding: 24px 32px; border-top: 1px solid #E5E7EB; font-size: 12px; color: #6B7280; text-align: center;">
                            <p style="margin: 0 0 8px;">
                                Charlton Exclusive Club<br>
                                Unit 6, Block 3 Woolwich, Dockyard Industrial Estate<br>
                                Woolwich Church St, Charlton, London SE18 5PQ
                            </p>
                            <p style="margin: 8px 0 0;">
                                <a href="{{ route('home') }}" style="color: #7A1F2E; text-decoration: none;">Visit our website</a> · 
                                <a href="{{ route('legal', 'privacy') }}" style="color: #7A1F2E; text-decoration: none;">Privacy</a>
                            </p>
                        </td>
                    </tr>
                </table>

                @if (isset($unsubscribe))
                    <p style="margin: 16px 0 0; font-size: 11px; color: #9CA3AF; text-align: center;">
                        You are receiving this because you are a member or applicant. 
                        <a href="{{ $unsubscribe }}" style="color: #6B7280; text-decoration: underline;">Unsubscribe</a>
                    </p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>