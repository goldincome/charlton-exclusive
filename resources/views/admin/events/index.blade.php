@extends('layouts.panel', ['title' => 'Events Management'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Events</h1>
        <p style="color: var(--slate-600);">Manage club events and calendar.</p>
    </div>
    <a href="{{ route('admin.events.create') }}" class="btn btn-navy">Create event</a>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($events as $event)
        <div class="card-light">
            <h3 style="font-size: 20px; margin: 0 0 4px;">{{ $event->title }}</h3>
            <p style="color: var(--slate-600); font-size: 14px; margin: 0;">{{ $event->start_time->format('j M Y, H:i') }}</p>
        </div>
    @empty
        <div class="card-light"><p style="margin: 0; color: var(--slate-600);">No events found.</p></div>
    @endforelse
</div>
@endsection
