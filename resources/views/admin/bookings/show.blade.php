@extends('layouts.panel', ['title' => 'Booking Management - ' . $booking->reference])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.bookings.index') }}" class="link-claret" style="font-size: 14px;">← Back to bookings</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $booking->bookingable?->name ?? 'Booking' }}</h1>
    <p style="color: var(--slate-600);">Ref: {{ $booking->reference }} · Member: <strong>{{ $booking->user->name }}</strong> ({{ $booking->user->email }})</p>
</header>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-bottom: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Booking Information</h2>
            <dl style="display: grid; gap: 12px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Status</dt>
                    <dd style="margin: 0;">
                        <span class="badge badge-{{ in_array($booking->status, ['confirmed', 'approved', 'completed', 'checked_in']) ? 'green' : 'gold' }}">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </dd>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Start Time</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $booking->start_time->format('D j M Y, H:i') }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">End Time</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $booking->end_time->format('D j M Y, H:i') }}</dd>
                </div>
                @if ($booking->bookingable_type === \App\Models\Hall::class)
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                        <dt style="color: var(--slate-600);">Duration</dt>
                        <dd style="margin: 0; font-weight: 600;">
                            @php
                                $durationMinutes = $booking->start_time->diffInMinutes($booking->end_time);
                                $hours = floor($durationMinutes / 60);
                                $mins = $durationMinutes % 60;
                            @endphp
                            {{ $hours > 0 ? $hours . ' ' . Str::plural('hour', $hours) : '' }}
                            {{ $mins > 0 ? $mins . ' mins' : '' }}
                        </dd>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                        <dt style="color: var(--slate-600);">Selected Times</dt>
                        <dd style="margin: 0; text-align: right; font-weight: 600;">
                            @php
                                $cursor = $booking->start_time->copy();
                                $end = $booking->end_time->copy();
                                $slotRanges = [];
                                while ($cursor->lt($end)) {
                                    $slotEnd = $cursor->copy()->addHour();
                                    if ($slotEnd->gt($end)) {
                                        $slotEnd = $end->copy();
                                    }
                                    $slotRanges[] = $cursor->format('g:i A').' - '.$slotEnd->format('g:i A');
                                    $cursor->addHour();
                                }
                            @endphp
                            {{ implode(', ', $slotRanges) }}
                        </dd>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Guests</dt>
                    <dd style="margin: 0;">{{ $booking->guests }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <dt style="color: var(--slate-600);">Created At</dt>
                    <dd style="margin: 0;">{{ $booking->created_at->format('j M Y, H:i') }}</dd>
                </div>
            </dl>

            @if ($booking->notes)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E0D5B8;">
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Member Notes</h3>
                    <p style="color: var(--slate-600); margin: 0;">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>

        @if ($booking->invoices->isNotEmpty())
            <div class="card-light">
                <h2 style="font-size: 22px; margin-bottom: 16px;">Invoices</h2>
                <div style="display: grid; gap: 12px;">
                    @foreach ($booking->invoices as $invoice)
                        <div class="card-light" style="background: #fff; border: 1px solid #E0D5B8;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="font-weight: 600; margin-bottom: 4px;">{{ $invoice->number }}</p>
                                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">Issued: {{ $invoice->issued_at->format('j M Y') }}</p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-weight: 600; margin-bottom: 4px;">£{{ number_format($invoice->total / 100, 2) }}</p>
                                    <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : 'gold' }}">{{ ucfirst($invoice->status) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        @php
            $final = $booking->final_amount ?? $booking->price_locked ?? 0;
            $original = $booking->original_amount ?? $final;
            $discount = $booking->discount_amount ?? 0;
        @endphp
        @if ($final > 0 || $original > 0)
            <div class="card-light" style="margin-bottom: 20px;">
                <h3 style="font-size: 18px; margin-bottom: 12px;">Price Breakdown</h3>
                @if ($original > 0 && $original !== $final)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; color: var(--slate-600);">
                        <span>Subtotal</span>
                        <span>£{{ number_format($original / 100, 2) }}</span>
                    </div>
                @endif
                @if ($discount > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; color: var(--slate-600);">
                        <span>
                            Member discount
                            @if ($booking->discount_type === 'percent')
                                ({{ (int) $booking->discount_value }}%)
                            @endif
                        </span>
                        <span>-£{{ number_format($discount / 100, 2) }}</span>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 8px; padding-top: 8px; border-top: 1px solid #E0D5B8;">
                    <span style="font-weight: 600;">Total Price</span>
                    <span style="font-size: 24px; font-family: var(--font-display); color: var(--claret-700);">
                        £{{ number_format($final / 100, 2) }}
                    </span>
                </div>
            </div>
        @endif

        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Actions</h2>

            @if ($booking->status === 'requested')
                <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" style="margin-bottom: 12px;">
                    @csrf
                    <button type="submit" class="btn btn-gold" style="width: 100%;">Approve booking</button>
                </form>

                <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}">
                    @csrf
                    <label class="field-label" for="reason" style="color: var(--ivory-100);">Rejection reason</label>
                    <textarea class="field-dark" id="reason" name="reason" rows="3" placeholder="Provide reason..." required style="margin-bottom: 12px;"></textarea>
                    <button type="submit" class="btn btn-line" style="width: 100%;">Reject booking</button>
                </form>
            @else
                <p style="color: var(--mist-300); margin: 0;">No actions available for status: {{ ucfirst($booking->status) }}</p>
            @endif
        </div>

        <div class="card-light">
            <h3 style="font-size: 18px; margin-bottom: 12px;">Member Details</h3>
            <p style="font-weight: 600; margin: 0 0 4px;">{{ $booking->user->name }}</p>
            <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 8px;">{{ $booking->user->email }}</p>
            <a href="{{ route('admin.members.show', $booking->user) }}" class="link-claret" style="font-size: 14px;">View member profile →</a>
        </div>
    </aside>
</div>
@endsection
