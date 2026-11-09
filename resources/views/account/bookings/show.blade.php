@extends('layouts.panel', ['title' => 'Booking details'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('account.bookings.index') }}" class="link-claret" style="font-size: 14px;">← Back to bookings</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $booking->bookingable?->name ?? 'Booking' }}</h1>
    <p style="color: var(--slate-600);">Ref: {{ $booking->reference }}</p>
</header>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-bottom: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Booking details</h2>
            <dl style="display: grid; gap: 12px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Start</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $booking->start_time->format('D j M Y, H:i') }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">End</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $booking->end_time->format('D j M Y, H:i') }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Guests</dt>
                    <dd style="margin: 0;">{{ $booking->guests }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <dt style="color: var(--slate-600);">Status</dt>
                    <dd style="margin: 0;"><span class="badge badge-{{ $booking->status === 'confirmed' ? 'green' : 'gold' }}">{{ ucfirst($booking->status) }}</span></dd>
                </div>
            </dl>

            @if ($booking->notes)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E0D5B8;">
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Your notes</h3>
                    <p style="color: var(--slate-600);">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>

        @if ($booking->invoices->isNotEmpty())
            <div class="card-light">
                <h2 style="font-size: 22px; margin-bottom: 16px;">Invoices</h2>
                <div style="display: grid; gap: 12px;">
                    @foreach ($booking->invoices as $invoice)
                        <a href="{{ route('account.invoices.show', $invoice) }}" class="card-light" style="display: block; background: #fff; border: 1px solid #E0D5B8; text-decoration: none; color: inherit;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="font-weight: 600; margin-bottom: 4px;">{{ $invoice->number }}</p>
                                    <p style="font-size: 14px; color: var(--slate-600);">{{ $invoice->issued_at->format('j M Y') }}</p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-weight: 600; margin-bottom: 4px;">£{{ number_format($invoice->total / 100, 2) }}</p>
                                    <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : 'gold' }}">{{ ucfirst($invoice->status) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 22px; margin-bottom: 12px;">Actions</h2>

            @if ($booking->status === 'approved' && $booking->invoices->where('status', 'unpaid')->isNotEmpty())
                @php $unpaid = $booking->invoices->where('status', 'unpaid')->first(); @endphp
                <p style="color: var(--mist-300); margin-bottom: 16px;">Payment due to confirm your booking.</p>
                <a href="{{ route('account.invoices.show', $unpaid) }}" class="btn btn-gold" style="width: 100%; margin-bottom: 12px;">Pay now</a>
            @endif

            @can('cancel', $booking)
                @if (in_array($booking->status, ['requested', 'approved', 'confirmed']))
                    <form method="POST" action="{{ route('account.bookings.cancel', $booking) }}" style="margin-top: 12px;" onsubmit="return confirm('Cancel this booking? Refunds follow the published cancellation policy.');">
                        @csrf
                        <label class="field-label" for="reason">Reason (optional)</label>
                        <textarea class="field-dark" id="reason" name="reason" rows="3" placeholder="Why are you cancelling?"></textarea>
                        <button type="submit" class="btn btn-line" style="width: 100%; margin-top: 12px;">Cancel booking</button>
                    </form>
                @endif
            @endcan
        </div>

        @if ($booking->price_locked)
            <div class="card-light">
                <h3 style="font-size: 18px; margin-bottom: 12px;">Price</h3>
                <p style="font-size: 28px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">
                    £{{ number_format($booking->price_locked / 100, 2) }}
                </p>
                @if (($booking->discount_amount ?? 0) > 0 || ($booking->discount_applied ?? 0) > 0)
                    <p style="font-size: 13px; color: var(--slate-600); margin-top: 8px;">
                        Member discount applied:
                        @if (($booking->discount_amount ?? 0) > 0 && $booking->discount_type === 'fixed')
                            £{{ number_format($booking->discount_amount / 100, 2) }} off
                        @elseif (($booking->discount_amount ?? 0) > 0 && $booking->discount_type === 'percent')
                            {{ (int) ($booking->discount_value ?? 0) }}% (saved £{{ number_format($booking->discount_amount / 100, 2) }})
                        @elseif ($booking->discount_type === 'percent')
                            {{ rtrim(rtrim(number_format((float) $booking->discount_applied, 2), '0'), '.') }}%
                        @else
                            £{{ number_format((float) $booking->discount_applied, 2) }} off
                        @endif
                    </p>
                @endif
            </div>
        @endif
    </aside>
</div>
@endsection