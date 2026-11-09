@extends('layouts.panel', ['title' => 'Dashboard'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Welcome back, {{ $user->name }}</h1>
    <p style="color: var(--slate-600);">Manage your bookings, applications and membership.</p>
</header>

@if ($user->membership)
    <div class="card-light" style="margin-bottom: 32px; background: var(--navy-900); color: var(--ivory-100);">
        <p class="eyebrow" style="font-size: 12px; color: var(--gold-500);">Membership</p>
        <h2 style="font-size: 24px; margin: 8px 0 4px;">{{ $user->membership->category->name }}</h2>
        <p style="color: var(--mist-300);">Member since {{ $user->membership->activated_at?->format('F Y') ?? 'N/A' }}</p>
        @if ($user->membership->status === 'suspended')
            <p class="badge badge-red" style="margin-top: 12px;">Membership suspended</p>
        @endif
    </div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <div class="card-light">
        <h3 style="font-size: 18px; margin-bottom: 10px;">Active bookings</h3>
        <p style="font-size: 32px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $bookings->count() }}</p>
    </div>
    <div class="card-light">
        <h3 style="font-size: 18px; margin-bottom: 10px;">Unpaid invoices</h3>
        <p style="font-size: 32px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $invoices->where('status', 'unpaid')->count() }}</p>
    </div>
    <div class="card-light">
        <h3 style="font-size: 18px; margin-bottom: 10px;">Pending sponsorships</h3>
        <p style="font-size: 32px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $pendingSponsorships->count() }}</p>
    </div>
</div>

@if ($bookings->isNotEmpty())
    <section style="margin-bottom: 32px;">
        <h2 style="font-size: 24px; margin-bottom: 16px;">Upcoming bookings</h2>
        <div style="display: grid; gap: 14px;">
            @foreach ($bookings as $booking)
                <a href="{{ route('account.bookings.show', $booking) }}" class="card-light" style="display: block; text-decoration: none; color: inherit;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                        <div>
                            <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">{{ $booking->start_time->format('D j M Y, H:i') }}</p>
                            <h3 style="font-size: 20px; margin: 6px 0;">{{ $booking->bookingable?->name ?? 'Booking' }}</h3>
                            <p style="color: var(--slate-600); font-size: 14px;">{{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }} · {{ ucfirst($booking->status) }}</p>
                        </div>
                        <span class="link-claret">View →</span>
                    </div>
                </a>
            @endforeach
        </div>
        <a href="{{ route('account.bookings.index') }}" class="link-claret" style="display: inline-block; margin-top: 16px;">View all bookings</a>
    </section>
@endif

@if ($pendingSponsorships->isNotEmpty())
    <section style="margin-bottom: 32px;">
        <h2 style="font-size: 24px; margin-bottom: 16px;">Sponsorship requests</h2>
        <div style="display: grid; gap: 14px;">
            @foreach ($pendingSponsorships as $sponsorship)
                <div class="card-light" style="background: rgba(201,169,97,.08); border: 1px solid rgba(201,169,97,.3);">
                    <h3 style="font-size: 20px; margin-bottom: 8px;">{{ $sponsorship->application->user->name }}</h3>
                    <p style="color: var(--slate-600); margin-bottom: 16px;">Applying for {{ $sponsorship->application->category->name }}</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <a href="{{ route('account.sponsorships.index') }}" class="btn btn-navy btn-sm">Review request</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<section>
    <h2 style="font-size: 24px; margin-bottom: 16px;">Quick actions</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('rooms.index') }}" class="btn btn-navy">Book a meeting room</a>
        <a href="{{ route('events.index') }}" class="btn btn-line-dark">View events</a>
        <a href="{{ route('account.food-orders.create') }}" class="btn btn-line-dark">Place a food order</a>
        @if (!$user->membership)
            <a href="{{ route('membership.index') }}" class="btn btn-line-dark">Explore membership</a>
        @endif
    </div>
</section>
@endsection