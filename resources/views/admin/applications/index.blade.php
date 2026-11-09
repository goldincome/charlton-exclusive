@extends('layouts.panel', ['title' => 'Manage Applications'])

@section('content')
<header style="margin-bottom: 32px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Membership Applications</h1>
        <p style="color: var(--slate-600);">Review and manage candidate applications, interviews, and admissions.</p>
    </div>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($applications as $application)
        <div class="card-light">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div>
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        Submitted {{ $application->submitted_at?->format('j M Y') ?? 'Draft' }}
                    </p>
                    <h3 style="font-size: 22px; margin: 6px 0 4px;">
                        <a href="{{ route('admin.applications.show', $application) }}" style="text-decoration: none; color: inherit;">
                            {{ $application->user->name }}
                        </a>
                    </h3>
                    <p style="color: var(--slate-600); font-size: 15px; margin: 0;">
                        Category: <strong>{{ $application->category->name }}</strong> · Email: {{ $application->user->email }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-gold">
                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                    </span>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #E0D5B8;">
                <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-navy btn-sm">Review application</a>
            </div>
        </div>
    @empty
        <div class="card-light">
            <p style="margin: 0; color: var(--slate-600);">No applications found.</p>
        </div>
    @endforelse
</div>

@if ($applications->hasPages())
    <div style="margin-top: 32px;">{{ $applications->links() }}</div>
@endif
@endsection
