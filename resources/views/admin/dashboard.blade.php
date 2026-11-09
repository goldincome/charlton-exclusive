@extends('layouts.panel', ['title' => 'Admin Dashboard'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Admin Dashboard</h1>
    <p style="color: var(--slate-600);">Overview of club operations and activity.</p>
</header>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100);">
        <p style="font-size: 13px; color: var(--mist-300); margin-bottom: 6px;">Members</p>
        <p style="font-size: 36px; font-family: var(--font-display); color: var(--champagne-200); margin: 0;">{{ $stats['members'] }}</p>
    </div>

    <div class="card-light">
        <p style="font-size: 13px; color: var(--slate-600); margin-bottom: 6px;">Pending applications</p>
        <p style="font-size: 36px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $stats['applications'] }}</p>
    </div>

    <div class="card-light">
        <p style="font-size: 13px; color: var(--slate-600); margin-bottom: 6px;">Booking requests</p>
        <p style="font-size: 36px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $stats['pending_bookings'] }}</p>
    </div>

    <div class="card-light">
        <p style="font-size: 13px; color: var(--slate-600); margin-bottom: 6px;">Food orders</p>
        <p style="font-size: 36px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $stats['pending_orders'] }}</p>
    </div>

    <div class="card-light">
        <p style="font-size: 13px; color: var(--slate-600); margin-bottom: 6px;">Revenue (net)</p>
        <p style="font-size: 28px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">£{{ number_format($stats['revenue'] / 100, 0) }}</p>
    </div>

    <div class="card-light">
        <p style="font-size: 13px; color: var(--slate-600); margin-bottom: 6px;">Unpaid invoices</p>
        <p style="font-size: 36px; font-family: var(--font-display); color: var(--claret-700); margin: 0;">{{ $stats['unpaid_invoices'] }}</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 16px;">Recent bookings</h2>
        <div style="display: grid; gap: 12px;">
            @forelse ($recentBookings as $booking)
                <a href="{{ route('admin.bookings.show', $booking) }}" class="card-light" style="display: block; background: #fff; border: 1px solid #E0D5B8; text-decoration: none; color: inherit;">
                    <p style="font-size: 13px; color: var(--claret-700); margin-bottom: 4px;">{{ $booking->start_time->format('j M, H:i') }}</p>
                    <p style="font-weight: 600; margin: 0;">{{ $booking->user->name }}</p>
                    <p style="font-size: 14px; color: var(--slate-600); margin: 4px 0 0;">{{ $booking->bookingable?->name }}</p>
                </a>
            @empty
                <p style="color: var(--slate-600);">No recent bookings.</p>
            @endforelse
        </div>
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 16px;">Recent applications</h2>
        <div style="display: grid; gap: 12px;">
            @forelse ($recentApplications as $application)
                <a href="{{ route('admin.applications.show', $application) }}" class="card-light" style="display: block; background: #fff; border: 1px solid #E0D5B8; text-decoration: none; color: inherit;">
                    <p style="font-weight: 600; margin: 0 0 4px;">{{ $application->user->name }}</p>
                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">{{ $application->resolvedPlan()?->name ?? 'Plan not selected' }}</p>
                    <span class="badge badge-gold" style="margin-top: 6px;">{{ ucfirst($application->status) }}</span>
                </a>
            @empty
                <p style="color: var(--slate-600);">No recent applications.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection