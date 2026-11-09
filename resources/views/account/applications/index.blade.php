@extends('layouts.panel', ['title' => 'My applications'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">My applications</h1>
        <p style="color: var(--slate-600);">View your membership applications and their progress.</p>
    </div>
    @if ($applications->where('isOpen', true)->isEmpty())
        <a href="{{ route('account.applications.create') }}" class="btn btn-navy">Begin an application</a>
    @endif
</header>

<div style="display: grid; gap: 20px;">
    @forelse ($applications as $application)
        <div class="card-light">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 24px; margin: 0 0 6px;">{{ $application->category->name }}</h3>
                    <p style="color: var(--slate-600);">Submitted {{ $application->submitted_at?->format('j F Y') ?? 'Draft' }}</p>
                </div>
                <span class="badge badge-{{ in_array($application->status, ['active', 'approved']) ? 'green' : (in_array($application->status, ['rejected', 'withdrawn']) ? 'neutral' : 'gold') }}">
                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                </span>
            </div>

            @if ($application->sponsorships->isNotEmpty())
                <div style="margin-bottom: 16px; padding-top: 16px; border-top: 1px solid #E0D5B8;">
                    <h4 style="font-size: 16px; margin-bottom: 10px;">Sponsors</h4>
                    <div style="display: grid; gap: 8px;">
                        @foreach ($application->sponsorships as $sponsorship)
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 15px;">{{ $sponsorship->sponsor->name }}</span>
                                <span class="badge badge-{{ $sponsorship->status === 'accepted' ? 'green' : ($sponsorship->status === 'declined' ? 'red' : 'gold') }}">
                                    {{ ucfirst($sponsorship->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($application->status === 'fees_notified')
                <div style="padding: 14px; background: rgba(201,169,97,.12); border: 1px solid rgba(201,169,97,.3); border-radius: 4px;">
                    <p style="margin: 0 0 10px; font-weight: 600;">Your application has been approved!</p>
                    <p style="margin: 0; font-size: 14px; color: var(--slate-600);">An invoice for your entrance and annual fees will be sent shortly.</p>
                </div>
            @endif
        </div>
    @empty
        <div class="card-light">
            <p style="margin-bottom: 16px;">You have not submitted a membership application yet.</p>
            <a href="{{ route('membership.index') }}" class="btn btn-navy btn-sm">Explore membership</a>
        </div>
    @endforelse
</div>
@endsection