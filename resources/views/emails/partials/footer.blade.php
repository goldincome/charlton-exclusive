<tr>
    <td style="background: #F9FAFB; padding: 24px 32px; border-top: 1px solid #E5E7EB; font-size: 12px; color: #6B7280; text-align: center;">
        <p style="margin: 0 0 8px;">
            {{ $clubName ?? config('club.name') }}<br>
            {{ $clubAddress ?? config('club.address') }}<br>
            {{ $clubPhone ?? config('club.phone') }} · {{ $clubEmail ?? config('club.email') }}
        </p>
        <p style="margin: 8px 0 0;">
            <a href="{{ route('home') }}" style="color: #7A1F2E; text-decoration: none;">Visit our website</a> ·
            <a href="{{ route('legal.privacy') }}" style="color: #7A1F2E; text-decoration: none;">Privacy</a>
        </p>
    </td>
</tr>
