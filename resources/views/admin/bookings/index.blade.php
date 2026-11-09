@extends('layouts.panel', ['title' => 'Manage Bookings'])

@section('content')
<header style="margin-bottom: 32px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Bookings Management</h1>
        <p style="color: var(--slate-600);">Review, approve or reject member booking requests.</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.bookings.index') }}" class="btn {{ request('status') ? 'btn-line-dark' : 'btn-navy' }} btn-sm">All</a>
        @foreach ($statuses as $st)
            <a href="{{ route('admin.bookings.index', ['status' => $st]) }}" class="btn {{ request('status') === $st ? 'btn-navy' : 'btn-line-dark' }} btn-sm">
                {{ ucfirst(str_replace('_', ' ', $st)) }}
            </a>
        @endforeach
    </div>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($bookings as $booking)
        <div class="card-light">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        {{ $booking->start_time->format('D j M Y, H:i') }} — Ref: {{ $booking->reference }}
                    </p>
                    <h3 style="font-size: 22px; margin: 6px 0 4px;">
                        <a href="{{ route('admin.bookings.show', $booking) }}" style="text-decoration: none; color: inherit;">
                            {{ $booking->bookingable?->name ?? 'Booking' }}
                        </a>
                    </h3>
                    <p style="color: var(--slate-600); font-size: 15px; margin: 0;">
                        Member: <strong>{{ $booking->user->name }}</strong> ({{ $booking->user->email }}) ·
                        {{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}
                        @if ($booking->price_locked)
                            · £{{ number_format($booking->price_locked / 100, 2) }}
                        @endif
                    </p>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-{{ in_array($booking->status, ['confirmed', 'completed', 'checked_in']) ? 'green' : ($booking->status === 'requested' ? 'gold' : 'neutral') }}">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #E0D5B8; align-items: center;">
                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-navy btn-sm">View details</a>

                @if ($booking->status === 'requested')
                    <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-gold btn-sm">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" style="display: inline; display: flex; gap: 8px; align-items: center;" onsubmit="return confirm('Reject this booking?');">
                        @csrf
                        <input type="text" name="reason" placeholder="Reason for rejection" required style="padding: 4px 8px; border: 1px solid #E0D5B8; border-radius: 4px; font-size: 13px;" />
                        <button type="submit" class="btn btn-line-dark btn-sm">Reject</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="card-light">
            <p style="margin: 0; color: var(--slate-600);">No bookings found.</p>
        </div>
    @endforelse
</div>

@if ($bookings->hasPages())
    <div style="margin-top: 32px;">{{ $bookings->links() }}</div>
@endif
@endsection
