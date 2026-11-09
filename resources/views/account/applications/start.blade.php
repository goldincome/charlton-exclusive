@extends('layouts.panel', ['title' => 'Membership application'])

@section('content')
@php
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Pending review',
        'fees_notified' => 'Approved — payment due',
        'payment_received' => 'Payment received',
        'active' => 'Active',
        'rejected' => 'Not approved',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
    ];
@endphp

<header style="margin-bottom: 32px;">
    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700); margin-bottom: 8px;">Charlton Exclusive Club</p>
    <h1 style="font-size: 34px; margin: 0 0 10px;">Application pending review</h1>
    <p style="color: var(--slate-600); max-width: 720px;">
        Thank you for registering, {{ auth()->user()->name }}. To become a member you need to submit a membership application.
        You can apply online now or download the application form and bring it to the office.
    </p>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

@if ($application)
    <div class="card-light" style="margin-bottom: 28px; border-left: 4px solid var(--gold-500);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start;">
            <div>
                <p class="eyebrow" style="font-size: 12px; color: var(--claret-700); margin-bottom: 6px;">Your application</p>
                <h2 style="font-size: 24px; margin: 0 0 6px;">{{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}</h2>
                <p style="color: var(--slate-600); margin: 0;">
                    Reference <strong>{{ $application->reference }}</strong>
                    @if ($application->submitted_at)
                        · submitted {{ $application->submitted_at->format('j F Y') }}
                    @endif
                </p>
            </div>
            <span class="badge badge-{{ $application->status === 'active' ? 'green' : ($application->status === 'rejected' ? 'red' : 'gold') }}">
                {{ $statusLabels[$application->status] ?? ucfirst($application->status) }}
            </span>
        </div>

        @if ($application->status === 'submitted')
            <div style="margin-top: 20px; padding-top: 18px; border-top: 1px solid #E0D5B8;">
                <p style="margin: 0 0 8px; font-weight: 600;">Next step: visit the office</p>
                <p style="color: var(--slate-600); margin: 0;">
                    Please bring an accepted photo ID to the office so we can verify your documents. Once approved, you will receive an email with a link to choose your plan and pay.
                </p>
            </div>
        @elseif ($application->status === 'fees_notified')
            <div style="margin-top: 20px;">
                <a href="{{ route('account.applications.pay', $application) }}" class="btn btn-navy">Choose billing &amp; pay</a>
            </div>
        @elseif ($application->status === 'active')
            <div style="margin-top: 20px;">
                <a href="{{ route('account.applications.index') }}" class="btn btn-navy">View my membership</a>
            </div>
        @endif
    </div>
@endif

@if (! $application || in_array($application->status, ['rejected', 'withdrawn', 'expired'], true))
    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 32px;">
        <article class="card-light" style="display: flex; flex-direction: column;">
            <p class="eyebrow" style="font-size: 12px; color: var(--claret-700); margin-bottom: 8px;">Option 1</p>
            <h2 style="font-size: 24px; margin: 0 0 10px;">Apply online</h2>
            <p style="color: var(--slate-600); margin: 0 0 16px;">
                Complete the application now. You will need your address and phone number, your profession, a CV, and a scan or photo of an accepted ID.
            </p>
            <a href="{{ route('account.applications.create') }}" class="btn btn-navy" style="margin-top: auto; align-self: flex-start;">Apply online</a>
        </article>

        <article class="card-light" style="display: flex; flex-direction: column;">
            <p class="eyebrow" style="font-size: 12px; color: var(--claret-700); margin-bottom: 8px;">Option 2</p>
            <h2 style="font-size: 24px; margin: 0 0 10px;">Download application</h2>
            <p style="color: var(--slate-600); margin: 0 0 16px;">
                Download and complete the application form, then bring it to the office together with an accepted ID.
            </p>
            <a href="{{ route('account.applications.form') }}" class="btn btn-line-dark" style="margin-top: auto; align-self: flex-start;">Download application (PDF)</a>
        </article>
    </section>
@endif

<section class="card-light">
    <h2 style="font-size: 22px; margin: 0 0 14px;">Accepted identification</h2>
    <p style="color: var(--slate-600); margin: 0 0 12px;">Please bring one of the following original documents to the office:</p>
    <ul style="margin: 0 0 22px 1.1rem; padding: 0; color: var(--slate-600); display: grid; gap: 6px;">
        @foreach ($idTypes as $label)
            <li>{{ $label }}</li>
        @endforeach
    </ul>

    <h2 style="font-size: 22px; margin: 0 0 10px;">Office</h2>
    <address style="font-style: normal; color: var(--slate-600); line-height: 1.7;">
        {{ config('club.name') }}<br>
        {{ config('club.address') }}<br>
        Phone: <a href="tel:{{ preg_replace('/\s+/', '', config('club.phone')) }}" class="link-claret">{{ config('club.phone') }}</a><br>
        Email: <a href="mailto:{{ config('club.email') }}" class="link-claret">{{ config('club.email') }}</a>
    </address>
</section>
@endsection
