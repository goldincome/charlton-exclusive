@extends('layouts.panel', ['title' => 'Complete membership payment'])

@section('content')
@php
    $money = fn (int $pence) => '£'.number_format($pence / 100, 2);
@endphp

<header style="margin-bottom: 32px;">
    <a href="{{ route('account.applications.index') }}" class="link-claret" style="font-size: 14px;">← Back to my membership</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">Choose how you pay</h1>
    <p style="color: var(--slate-600); max-width: 760px;">
        Your application for <strong>{{ $plan->name }}</strong> has been approved. Choose monthly or yearly billing to activate your membership.
        Your subscription renews automatically and you can cancel by contacting the club.
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

<div class="card-light" style="margin-bottom: 24px;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start;">
        <div>
            <p class="eyebrow" style="font-size: 12px; color: var(--claret-700); margin-bottom: 6px;">Application {{ $application->reference }}</p>
            <h2 style="font-size: 26px; margin: 0 0 6px;">{{ $plan->name }}</h2>
            <p style="color: var(--slate-600); margin: 0;">{{ $plan->description }}</p>
        </div>
        <span class="badge badge-gold">Approved</span>
    </div>

    @if (! empty($plan->benefits))
        <ul style="margin: 18px 0 0 1.1rem; padding: 0; color: var(--slate-600); display: grid; gap: 6px;">
            @foreach ($plan->benefits as $benefit)
                <li>{{ $benefit }}</li>
            @endforeach
        </ul>
    @endif
</div>

<form method="POST" action="{{ route('account.applications.pay.start', $application) }}" style="display: grid; gap: 24px;">
    @csrf

    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <label class="card-light" style="display: block; cursor: pointer; border: 2px solid {{ old('billing_frequency', 'annual') === 'annual' ? 'var(--gold-500)' : '#E0D5B8' }};">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <input type="radio" name="billing_frequency" value="annual" required @checked(old('billing_frequency', 'annual') === 'annual') style="margin-top: 4px; width: 20px; height: 20px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <strong style="font-size: 20px;">Yearly</strong>
                        <span class="badge badge-green">Discounts included</span>
                    </div>
                    <p style="font-size: 30px; margin: 10px 0 4px; font-family: var(--font-display); color: var(--claret-700);">{{ $money((int) $plan->annual_fee) }}<span style="font-size: 15px; font-family: var(--font-body); color: var(--slate-600);"> / year</span></p>
                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">Only yearly members receive member discounts on sections and services.</p>
                </div>
            </div>
        </label>

        <label class="card-light" style="display: block; cursor: {{ $plan->monthlyAvailable() ? 'pointer' : 'not-allowed' }}; opacity: {{ $plan->monthlyAvailable() ? '1' : '.65' }}; border: 2px solid {{ old('billing_frequency') === 'monthly' ? 'var(--gold-500)' : '#E0D5B8' }};">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <input type="radio" name="billing_frequency" value="monthly" @checked(old('billing_frequency') === 'monthly') @disabled(! $plan->monthlyAvailable()) style="margin-top: 4px; width: 20px; height: 20px;">
                <div>
                    <strong style="font-size: 20px;">Monthly</strong>
                    <p style="font-size: 30px; margin: 10px 0 4px; font-family: var(--font-display); color: var(--claret-700);">{{ $money((int) $plan->monthly_fee) }}<span style="font-size: 15px; font-family: var(--font-body); color: var(--slate-600);"> / month</span></p>
                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">
                        @if ($plan->monthlyAvailable())
                            Members-only access at standard rates. Switch to yearly at any time to receive discounts.
                        @else
                            Not available for this plan.
                        @endif
                    </p>
                </div>
            </div>
        </label>
    </section>

    <div class="card-light" style="background: rgba(201,169,97,.08); border: 1px solid rgba(201,169,97,.3);">
        <p style="margin: 0; font-size: 14px; color: var(--slate-600);">
            Your card will be charged today and automatically on each renewal. There is no entrance fee. If a renewal payment fails, we will email you the invoice and a secure payment link.
        </p>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <button type="submit" class="btn btn-navy">Continue to secure payment</button>
        <a href="{{ route('account.applications.index') }}" class="btn btn-line-dark">Not now</a>
    </div>
</form>

<div class="card-light" style="margin-top: 24px;">
    <h2 style="font-size: 18px; margin: 0 0 8px;">Need help?</h2>
    <p style="margin: 0; color: var(--slate-600);">
        {{ config('club.name') }}, {{ config('club.address') }}<br>
        {{ config('club.phone') }} · <a href="mailto:{{ config('club.email') }}" class="link-claret">{{ config('club.email') }}</a>
    </p>
</div>
@endsection
