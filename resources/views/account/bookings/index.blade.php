@extends('layouts.panel', ['title' => 'My bookings'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">My bookings</h1>
    <p style="color: var(--slate-600);">View and manage your room, event and table bookings.</p>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($bookings as $booking)
        <a href="{{ route('account.bookings.show', $booking) }}" class="card-light" style="display: block; text-decoration: none; color: inherit;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        {{ $booking->start_time->format('D j M Y, H:i') }}
                    </p>
                    <h3 style="font-size: 22px; margin: 6px 0 4px;">{{ $booking->bookingable?->name ?? 'Booking' }}</h3>
                    <p style="color: var(--slate-600); font-size: 15px;">
                        {{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }} · 
                        Ref: {{ $booking->reference }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-{{ in_array($booking->status, ['confirmed', 'approved', 'completed', 'checked_in']) ? 'green' : ($booking->status === 'requested' ? 'gold' : 'neutral') }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                    @if ($booking->price_locked)
                        <p style="margin: 8px 0 0; font-weight: 600;">£{{ number_format($booking->price_locked / 100, 2) }}</p>
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="card-light">
            <p>You have no bookings yet.</p>
            <a href="{{ route('rooms.index') }}" class="btn btn-navy btn-sm" style="margin-top: 16px;">Book a meeting room</a>
        </div>
    @endforelse
</div>

@if ($bookings->hasPages())
    <div style="margin-top: 32px;">{{ $bookings->links() }}</div>
@endif
@endsection