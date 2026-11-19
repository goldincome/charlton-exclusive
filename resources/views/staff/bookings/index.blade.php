@extends('layouts.panel', ['title' => 'Staff Dashboard'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Today's bookings</h1>
    <p style="color: var(--slate-600);">Check in guests and mark bookings completed. {{ $today }} bookings today.</p>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($bookings as $booking)
        <div class="card-light">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        {{ $booking->start_time->format('D j M Y, H:i') }}
                    </p>
                    <h3 style="font-size: 22px; margin: 6px 0 4px;">{{ $booking->bookingable?->name ?? 'Booking' }}</h3>
                    <p style="color: var(--slate-600); font-size: 15px;">
                        {{ $booking->user->name }} · {{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }} · 
                        Ref: {{ $booking->reference }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-{{ in_array($booking->status, ['confirmed', 'approved', 'completed', 'checked_in']) ? 'green' : 'gold' }}">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #E0D5B8;">
                @if ($booking->status === 'confirmed')
                    <form method="POST" action="{{ route('staff.bookings.check-in', $booking) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-navy btn-sm">Check in</button>
                    </form>
                @elseif ($booking->status === 'checked_in')
                    <form method="POST" action="{{ route('staff.bookings.complete', $booking) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-navy btn-sm">Mark completed</button>
                    </form>

                    <form method="POST" action="{{ route('staff.bookings.no-show', $booking) }}" style="display: inline;" onsubmit="return confirm('Mark this booking as a no-show?');">
                        @csrf
                        <button type="submit" class="btn btn-line-dark btn-sm">No-show</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="card-light">
            <p>No bookings to check in at the moment.</p>
        </div>
    @endforelse
</div>

@if ($bookings->hasPages())
    <div style="margin-top: 32px;">{{ $bookings->links() }}</div>
@endif
@endsection