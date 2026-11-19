@extends('layouts.panel', ['title' => 'Events Management'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Events Management</h1>
        <p style="color: var(--slate-600);">Manage club events, ticketing, capacities, and calendar.</p>
    </div>
    <a href="{{ route('admin.events.create') }}" class="btn btn-navy">Create event</a>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

<div style="display: grid; gap: 16px;">
    @forelse ($events as $event)
        <div class="card-light" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
            <div>
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 6px;">
                    <h3 style="font-size: 20px; margin: 0;">{{ $event->name }}</h3>
                    @if ($event->cancelled_at)
                        <span class="badge badge-red" style="padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #FEEBEE; color: #C62828;">Cancelled</span>
                    @else
                        <span class="badge badge-{{ $event->active ? 'green' : 'neutral' }}" style="padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: {{ $event->active ? '#E8F5E9' : '#F5F5F5' }}; color: {{ $event->active ? '#1B5E20' : '#616161' }};">
                            {{ $event->active ? 'Active' : 'Inactive' }}
                        </span>
                    @endif
                </div>
                <p style="color: var(--slate-600); font-size: 14px; margin: 0 0 6px;">
                    Starts: <strong>{{ $event->starts_at?->format('D j M Y, H:i') ?? '—' }}</strong> ·
                    Ends: <strong>{{ $event->ends_at?->format('D j M Y, H:i') ?? '—' }}</strong>
                </p>
                <p style="color: var(--slate-600); font-size: 14px; margin: 0 0 8px;">
                    Capacity: <strong>{{ $event->capacity }}</strong> · Price: <strong>£{{ number_format($event->price_per_ticket / 100, 2) }}</strong> @if($event->venue) · Venue: <strong>{{ $event->venue }}</strong> @endif
                </p>
                @if($event->description)
                    <p style="color: var(--slate-600); font-size: 13px; margin: 0; max-width: 600px;">{{ Str::limit($event->description, 120) }}</p>
                @endif
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-line-dark btn-sm">Edit</a>
                @if (!$event->cancelled_at)
                    <form method="POST" action="{{ route('admin.events.cancel', $event) }}" onsubmit="return confirm('Cancel this event? Confirmed ticket holders will be refunded.');" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="background: #FFF8E1; color: #F57F17; border: 1px solid #FBC02D;">Cancel</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Are you sure you want to delete this event?');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background: #FEEBEE; color: #C62828; border: 1px solid #E53935;">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card-light"><p style="margin: 0; color: var(--slate-600);">No events found.</p></div>
    @endforelse
</div>

@if ($events->hasPages())
    <div style="margin-top: 32px;">{{ $events->links() }}</div>
@endif
@endsection
