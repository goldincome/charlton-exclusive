@extends('layouts.panel', ['title' => 'Meeting Rooms & Halls'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Meeting Rooms & Halls</h1>
        <p style="color: var(--slate-600);">Manage bookable spaces.</p>
    </div>
    <a href="{{ route('admin.halls.create') }}" class="btn btn-navy">Add room</a>
</header>
<div style="display: grid; gap: 16px;">
    @forelse ($halls as $hall)
        <div class="card-light"><h3 style="font-size: 20px; margin: 0;">{{ $hall->name }}</h3></div>
    @empty
        <div class="card-light"><p style="margin: 0; color: var(--slate-600);">No rooms found.</p></div>
    @endforelse
</div>
@endsection
