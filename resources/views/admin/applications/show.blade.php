@extends('layouts.panel', ['title' => 'Application - ' . $application->user->name])

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
    $idTypes = [
        'national_id' => 'National ID card',
        'drivers_licence' => "Driver's licence",
        'passport' => 'International passport',
    ];
    $isAdmin = auth()->user()->isAdmin();
    $plan = $application->resolvedPlan();
@endphp

<header style="margin-bottom: 32px;">
    <a href="{{ $isAdmin ? route('admin.applications.index') : route('committee.applications.index') }}" class="link-claret" style="font-size: 14px;">← Back to applications</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $application->user->name }}</h1>
    <p style="color: var(--slate-600);">
        Plan: {{ $plan?->name ?? 'Not selected' }} · Ref: {{ $application->reference }} ·
        Status: {{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}
    </p>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-bottom: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Candidate details</h2>
            <dl style="display: grid; gap: 12px;">
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Full name</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $application->user->name }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Email</dt>
                    <dd style="margin: 0;">{{ $application->user->email }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Phone</dt>
                    <dd style="margin: 0;">{{ $application->user->phone ?? '—' }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Address</dt>
                    <dd style="margin: 0; text-align: right;">
                        {{ $application->user->address_line_1 ?? '—' }}<br>
                        @if ($application->user->address_line_2){{ $application->user->address_line_2 }}<br>@endif
                        {{ $application->user->city ?? '' }} {{ $application->user->postcode ?? '' }}
                    </dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Profession</dt>
                    <dd style="margin: 0;">{{ $application->profession ?? '—' }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">ID type</dt>
                    <dd style="margin: 0;">{{ $idTypes[$application->id_type] ?? '—' }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px;">
                    <dt style="color: var(--slate-600);">Submitted</dt>
                    <dd style="margin: 0;">{{ $application->submitted_at?->format('j M Y, H:i') ?? 'Not submitted' }}</dd>
                </div>
            </dl>
        </div>

        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Documents</h2>
            <div style="display: grid; gap: 12px;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: center; padding: 12px 14px; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <div>
                        <strong>Curriculum vitae</strong>
                        <p style="margin: 4px 0 0; font-size: 13px; color: var(--slate-600);">{{ $application->cv_path ? 'Uploaded' : 'Not uploaded' }}</p>
                    </div>
                    @if ($application->cv_path)
                        <a href="{{ route('admin.applications.documents', ['application' => $application, 'type' => 'cv']) }}" class="btn btn-line-dark btn-sm">Download CV</a>
                    @endif
                </div>
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: center; padding: 12px 14px; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <div>
                        <strong>ID document</strong>
                        <p style="margin: 4px 0 0; font-size: 13px; color: var(--slate-600);">{{ $application->id_path ? 'Uploaded' : 'Not uploaded' }}</p>
                    </div>
                    @if ($application->id_path)
                        <a href="{{ route('admin.applications.documents', ['application' => $application, 'type' => 'id']) }}" class="btn btn-line-dark btn-sm">Download ID</a>
                    @endif
                </div>
            </div>

            <div style="margin-top: 16px; padding: 12px 14px; border-radius: 4px; background: {{ $application->hasVerifiedDocuments() ? '#E8F5E9' : 'rgba(201,169,97,.12)' }};">
                @if ($application->hasVerifiedDocuments())
                    <p style="margin: 0; color: #1B5E20; font-size: 14px;">
                        Documents verified {{ $application->documents_verified_at->format('j M Y, H:i') }}
                        @if ($application->documentsVerifiedBy)
                            by {{ $application->documentsVerifiedBy->name }}
                        @endif.
                    </p>
                @else
                    <p style="margin: 0; color: var(--slate-600); font-size: 14px;">Documents have not been verified yet. Check the original ID at the office before approving.</p>
                @endif
            </div>
        </div>

        @if ($application->invoices->isNotEmpty())
            <div class="card-light">
                <h2 style="font-size: 20px; margin-bottom: 12px;">Invoices</h2>
                <div style="display: grid; gap: 8px;">
                    @foreach ($application->invoices->sortByDesc('id') as $invoice)
                        <div style="display: flex; justify-content: space-between; gap: 12px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                            <span>{{ $invoice->number }}</span>
                            <span>{{ $invoice->grossFormatted() }} · {{ ucfirst($invoice->status) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Admin actions</h2>

            @if (! $isAdmin)
                <p style="margin: 0; color: var(--mist-300); font-size: 14px;">You can review this application. Only administrators can verify documents, choose plans, approve or cancel memberships.</p>
            @else
                @if (! $application->hasVerifiedDocuments())
                    <form method="POST" action="{{ route('admin.applications.verify', $application) }}" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(201,169,97,.25);">
                        @csrf
                        <p style="font-size: 14px; color: var(--mist-300); margin: 0 0 10px;">Confirm you have seen the applicant's original ID and checked the uploaded CV and ID document.</p>
                        <button type="submit" class="btn btn-gold" style="width: 100%;">Mark documents verified</button>
                    </form>
                @endif

                @if ($application->status !== 'active')
                    <form method="POST" action="{{ route('admin.applications.approve', $application) }}" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(201,169,97,.25);">
                        @csrf
                        <label class="field-label" for="plan_id" style="color: var(--ivory-100);">Membership plan</label>
                        <select name="plan_id" id="plan_id" class="field-dark" style="margin-bottom: 10px;" required>
                            <option value="">Select a plan…</option>
                            @foreach ($plans as $planOption)
                                <option value="{{ $planOption->id }}" @selected((int) old('plan_id', $application->plan_id) === $planOption->id)>{{ $planOption->name }} — £{{ number_format($planOption->annual_fee / 100, 2) }}/year</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-gold" style="width: 100%;" @disabled(! $application->hasVerifiedDocuments())>
                            Approve &amp; email payment link
                        </button>
                        @unless ($application->hasVerifiedDocuments())
                            <p style="font-size: 12px; color: #FFCDD2; margin: 8px 0 0;">Verify documents first to enable approval.</p>
                        @endunless
                    </form>

                    <form method="POST" action="{{ route('admin.applications.reject', $application) }}" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(201,169,97,.25);">
                        @csrf
                        <label class="field-label" for="reason" style="color: var(--ivory-100);">Rejection reason</label>
                        <textarea name="reason" id="reason" rows="2" class="field-dark" placeholder="Required to reject…" style="margin-bottom: 10px;" required></textarea>
                        <button type="submit" class="btn btn-line-light" style="width: 100%;">Reject application</button>
                    </form>
                @else
                    <p style="margin: 0 0 16px; color: var(--champagne-200);">This application is active. The member is paying by subscription.</p>
                @endif

                <p style="font-size: 12px; color: var(--mist-300); margin: 0;">
                    Monthly or yearly billing is chosen by the member on the secure payment page. Only yearly members receive discounts. Cancellation is handled from the member's page.
                </p>
            @endif
        </div>
    </aside>
</div>
@endsection
