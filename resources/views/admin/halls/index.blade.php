@extends('layouts.panel', ['title' => 'Meeting Rooms & Halls'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Meeting Rooms & Halls</h1>
        <p style="color: var(--slate-600);">Manage bookable spaces, capacities, and hourly rates.</p>
    </div>
    <a href="{{ route('admin.halls.create') }}" class="btn btn-navy">Add room</a>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

<div style="display: grid; gap: 16px;">
    @forelse ($halls as $hall)
        <div class="card-light" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
            <div>
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 6px;">
                    <h3 style="font-size: 20px; margin: 0;">{{ $hall->name }}</h3>
                    <span class="badge badge-{{ $hall->active ? 'green' : 'red' }}" style="padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: {{ $hall->active ? '#E8F5E9' : '#FEEBEE' }}; color: {{ $hall->active ? '#1B5E20' : '#C62828' }};">
                        {{ $hall->active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p style="color: var(--slate-600); font-size: 14px; margin: 0 0 8px;">
                    Capacity: <strong>{{ $hall->capacity }}</strong> standing ({{ $hall->capacity_seated ?? '—' }} seated) · Minimum: <strong>{{ $hall->min_hours }} hrs</strong> · Buffer: <strong>{{ $hall->buffer_minutes }} mins</strong>
                </p>
                @if($hall->description)
                    <p style="color: var(--slate-600); font-size: 13px; margin: 0; max-width: 600px;">{{ Str::limit($hall->description, 120) }}</p>
                @endif
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('admin.halls.edit', $hall) }}" class="btn btn-line-dark btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.halls.destroy', $hall) }}" onsubmit="return confirm('Are you sure you want to delete this room?');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background: #FEEBEE; color: #C62828; border: 1px solid #E53935;">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card-light"><p style="margin: 0; color: var(--slate-600);">No meeting rooms found.</p></div>
    @endforelse
</div>
@endsection
