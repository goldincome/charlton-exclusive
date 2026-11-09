@extends('layouts.panel', ['title' => 'Upgrade membership'])

@section('content')
@php
    $money = fn (int $pence) => '£'.number_format($pence / 100, 2);
@endphp

<header style="margin-bottom: 32px;">
    <a href="{{ route('account.applications.index') }}" class="link-claret" style="font-size: 14px;">← Back to my membership</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $isCurrentPlan ? 'Switch to yearly billing' : 'Upgrade your membership' }}</h1>
    <p style="color: var(--slate-600); max-width: 760px;">
        Changes take effect immediately. Stripe charges the prorated difference today and your subscription continues from the new plan.
        Your card must be approved before the change is made.
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
    <h2 style="font-size: 20px; margin: 0 0 10px;">Choose a plan</h2>
    <div style="display: grid; gap: 10px;">
        @foreach ($plans as $plan)
            @php $selected = $plan->id === $target->id; @endphp
            <a href="{{ route('account.membership.upgrade', ['plan' => $plan->id]) }}"
               style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: center; padding: 14px 16px; border: 2px solid {{ $selected ? 'var(--gold-500)' : '#E0D5B8' }}; border-radius: 4px; text-decoration: none; color: inherit; background: {{ $selected ? 'rgba(201,169,97,.08)' : '#fff' }};">
                <div>
                    <strong style="display: block; font-size: 18px;">{{ $plan->name }}</strong>
                    <span style="font-size: 14px; color: var(--slate-600);">
                        @if ($plan->id === $membership->plan_id)
                            Current plan · switch to yearly billing
                        @else
                            {{ $money((int) $plan->annual_fee) }} / year
                            @if ($plan->monthlyAvailable()) · {{ $money((int) $plan->monthly_fee) }} / month @endif
                        @endif
                    </span>
                </div>
                <span class="badge badge-{{ $selected ? 'gold' : 'neutral' }}">{{ $selected ? 'Selected' : 'Choose' }}</span>
            </a>
        @endforeach
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <div class="card-light">
        <h2 style="font-size: 22px; margin: 0 0 12px;">{{ $target->name }}</h2>
        <p style="color: var(--slate-600); margin: 0 0 16px;">{{ $target->description }}</p>

        <dl style="display: grid; gap: 10px; margin: 0 0 16px; font-size: 15px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #E0D5B8; padding-bottom: 8px;">
                <dt>Yearly fee</dt>
                <dd style="margin: 0; font-weight: 600;">{{ $money((int) $target->annual_fee) }}</dd>
            </div>
            @if ($target->monthlyAvailable())
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #E0D5B8; padding-bottom: 8px;">
                    <dt>Monthly fee</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $money((int) $target->monthly_fee) }}</dd>
                </div>
            @endif
            <div style="display: flex; justify-content: space-between;">
                <dt>Entrance fee</dt>
                <dd style="margin: 0; font-weight: 600;">None</dd>
            </div>
        </dl>

        @if (! empty($target->benefits))
            <h3 style="font-size: 17px; margin: 0 0 8px;">Included benefits</h3>
            <ul style="margin: 0; padding-left: 20px; color: var(--slate-600); display: grid; gap: 6px;">
                @foreach ($target->benefits as $benefit)
                    <li>{{ $benefit }}</li>
                @endforeach
            </ul>
        @endif

        @if (! $isCurrentPlan && $target->monthlyAvailable())
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #E0D5B8;">
                <p style="font-size: 13px; font-weight: 600; margin: 0 0 8px; text-transform: uppercase; letter-spacing: .06em; color: var(--slate-600);">Billing frequency</p>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('account.membership.upgrade', ['plan' => $target->id, 'frequency' => 'annual']) }}" class="btn {{ $frequency === 'annual' ? 'btn-navy' : 'btn-line-dark' }} btn-sm">Yearly — discounts</a>
                    <a href="{{ route('account.membership.upgrade', ['plan' => $target->id, 'frequency' => 'monthly']) }}" class="btn {{ $frequency === 'monthly' ? 'btn-navy' : 'btn-line-dark' }} btn-sm">Monthly — access only</a>
                </div>
            </div>
        @endif
    </div>

    <aside class="card-light" style="background: var(--navy-900); color: var(--ivory-100);">
        <h2 style="font-size: 20px; margin: 0 0 12px;">Payment today</h2>

        @if ($quoteError)
            <p style="color: #FFCDD2; font-size: 14px; margin: 0 0 16px;">{{ $quoteError }}</p>
        @elseif ($quote)
            <p style="font-size: 34px; font-family: var(--font-display); margin: 0 0 6px; color: var(--champagne-200);">
                {{ $money((int) $quote['amount_due']) }}
            </p>
            <p style="color: var(--mist-300); font-size: 14px; margin: 0 0 18px;">
                Prorated charge for the rest of your current billing period.
            </p>
        @endif

        <p style="color: var(--mist-300); font-size: 13px; margin: 0 0 16px;">
            New recurring amount:
            <strong style="color: var(--ivory-100);">{{ $money($target->feeFor($frequency)) }} / {{ $frequency === 'monthly' ? 'month' : 'year' }}</strong>
        </p>

        @if (! $quoteError)
            <form method="POST" action="{{ route('account.membership.upgrade.store') }}">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $target->id }}">
                <input type="hidden" name="billing_frequency" value="{{ $frequency }}">
                <button type="submit" class="btn btn-gold" style="width: 100%;">
                    {{ $isCurrentPlan ? 'Switch to yearly' : 'Confirm upgrade' }}
                </button>
            </form>
        @else
            <a href="{{ route('contact') }}" class="btn btn-line-light" style="width: 100%;">Contact the club</a>
        @endif

        <p style="color: var(--mist-300); font-size: 12px; margin: 16px 0 0;">
            Downgrades and cancellations are handled by the club. Contact us if you need to change or cancel your membership.
        </p>
    </aside>
</div>
@endsection
