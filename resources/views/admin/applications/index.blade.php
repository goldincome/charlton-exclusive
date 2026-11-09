@extends('layouts.panel', ['title' => 'Manage Applications'])

@section('content')
@php
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Pending review',
        'fees_notified' => 'Approved — payment due',
        'payment_received' => 'Payment received',
        'active' => 'Active',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
    ];
@endphp

<header style="margin-bottom: 32px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Membership Applications</h1>
        <p style="color: var(--slate-600); max-width: 820px;">
            Verify the applicant's original ID and uploaded documents, choose their plan, then approve the application.
            The applicant is emailed a payment link and activates their membership by subscribing monthly or yearly.
        </p>
    </div>
</header>

<form method="GET" action="{{ route('admin.applications.index') }}" style="margin-bottom: 20px; display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
    <div>
        <label class="field-label" for="status_filter">Status</label>
        <select class="field-light" id="status_filter" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-navy btn-sm">Filter</button>
</form>

<div style="display: grid; gap: 16px;">
    @forelse ($applications as $application)
        <div class="card-light">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div>
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        Submitted {{ $application->submitted_at?->format('j M Y') ?? 'Not submitted' }}
                    </p>
                    <h3 style="font-size: 22px; margin: 6px 0 4px;">
                        <a href="{{ route('admin.applications.show', $application) }}" style="text-decoration: none; color: inherit;">
                            {{ $application->user->name }}
                        </a>
                    </h3>
                    <p style="color: var(--slate-600); font-size: 15px; margin: 0;">
                        Plan: <strong>{{ $application->resolvedPlan()?->name ?? 'Not selected' }}</strong>
                        · Ref: {{ $application->reference }}
                        · Email: {{ $application->user->email }}
                    </p>
                    @if ($application->hasVerifiedDocuments())
                        <p style="margin: 8px 0 0; font-size: 13px; color: #1B5E20;">Documents verified</p>
                    @else
                        <p style="margin: 8px 0 0; font-size: 13px; color: var(--claret-700);">Documents not yet verified</p>
                    @endif
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-{{ $application->status === 'active' ? 'green' : (in_array($application->status, ['rejected', 'withdrawn', 'expired'], true) ? 'red' : 'gold') }}">
                        {{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}
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
