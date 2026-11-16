@extends('layouts.panel', ['title' => 'My membership'])

@section('content')
@php
    $money = fn (int $pence) => '£'.number_format($pence / 100, 2);
    $hasActivePlan = $currentPlan !== null;
    $isPastDueWithPlan = $membership && $membership->status === 'past_due' && $hasActivePlan;
    $isActive = ($membership && $membership->status === \App\Models\Membership::ACTIVE) || $isPastDueWithPlan;
    $planStatus = $isActive ? 'active' : ($membership?->status ?? $currentApplication?->status);
    $planBadge = $isActive
        ? 'green'
        : (in_array($planStatus, ['rejected', 'withdrawn', 'expired', 'suspended', 'terminated', 'cancelled', 'past_due'], true) ? 'red' : 'gold');
    $statusLabels = [
        'draft' => 'Draft',
        'submitted' => 'Pending review',
        'fees_notified' => 'Approved — payment due',
        'payment_received' => 'Payment received',
        'active' => 'Active',
        'past_due' => $hasActivePlan ? 'Paid' : 'Payment overdue',
        'suspended' => 'Suspended',
        'cancelled' => 'Cancelled',
        'terminated' => 'Terminated',
        'rejected' => 'Not approved',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
    ];
    $effectivePlanStatus = $isPastDueWithPlan ? 'past_due' : $planStatus;
    $discountLabel = function ($section) {
        $type = $section->pivot->type ?? 'percent';
        $value = (int) ($section->pivot->value ?? 0);

        if ($value <= 0) {
            return 'Included';
        }

        return $type === 'fixed' ? '£'.number_format($value / 100, 2).' off' : $value.'% off';
    };
    $sectionCta = function ($section) {
        return match ($section->slug) {
            'events' => ['text' => 'Host your events in a serene environment, click here to book event', 'url' => route('events.index')],
            'meeting-rooms' => ['text' => 'Book our well-equipped meeting rooms for your conferences and gatherings, click here to book', 'url' => route('rooms.index')],
            'dining' => ['text' => 'Enjoy fine dining in our restaurant and lounge, click here to view dining', 'url' => route('dining')],
            'library' => ['text' => 'Relax and study in our quiet library and reading room', 'url' => route('membership.section', $section->slug)],
            'reciprocal' => ['text' => 'Explore our partner clubs across the reciprocal network', 'url' => route('membership.section', $section->slug)],
            default => ['text' => $section->description, 'url' => route('membership.section', $section->slug)],
        };
    };
    $samePlanSwitch = $membership?->isMonthly()
        ? $upgradePlans->firstWhere('id', $membership->plan_id)
        : null;
    $higherPlans = $membership
        ? $upgradePlans->reject(fn ($plan) => $plan->id === $membership->plan_id)->values()
        : $upgradePlans;
@endphp

<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">My membership</h1>
        <p style="color: var(--slate-600);">Your plan, billing, benefits and available upgrades.</p>
    </div>
    @if (! $currentPlan && ! $currentApplication)
        <a href="{{ route('account.applications.start') }}" class="btn btn-navy">Apply for membership</a>
    @endif
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

@if ($currentPlan)
    <section style="margin-bottom: 40px;">
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start;">
                <div>
                    <p class="eyebrow" style="font-size: 12px; color: var(--gold-500); margin-bottom: 8px;">Your plan</p>
                    <h2 style="font-size: 30px; margin: 0 0 8px;">{{ $currentPlan->name }}</h2>
                    <p style="color: var(--mist-300); margin: 0;">{{ $currentPlan->description }}</p>
                </div>
                <span class="badge badge-{{ $planBadge }}">{{ $statusLabels[$effectivePlanStatus] ?? ucfirst(str_replace('_', ' ', $effectivePlanStatus ?? 'not started')) }}</span>
            </div>

            @if ($membership)
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(201,169,97,.25);">
                    <div>
                        <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Member since</p>
                        <p style="margin: 0;">{{ $membership->starts_at?->format('j F Y') ?? $membership->created_at?->format('j F Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Renews</p>
                        <p style="margin: 0;">{{ $membership->renews_at?->format('j F Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Billing</p>
                        <p style="margin: 0;">{{ $membership->isMonthly() ? 'Monthly' : 'Yearly' }}</p>
                    </div>
                    @if ($membership->ends_at && $membership->cancelled_at)
                        <div>
                            <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Access ends</p>
                            <p style="margin: 0;">{{ $membership->ends_at->format('j F Y') }}</p>
                        </div>
                    @endif
                </div>
            @elseif ($currentApplication)
                <p style="color: var(--mist-300); margin: 20px 0 0; padding-top: 20px; border-top: 1px solid rgba(201,169,97,.25);">
                    Application {{ $currentApplication->reference }}
                    · {{ $statusLabels[$currentApplication->status] ?? ucfirst($currentApplication->status) }}.
                </p>
            @endif

            @php
                $isMonthlyMember = $membership && $membership->isMonthly();
                $isYearlyMember = $membership && $membership->isAnnual();
            @endphp
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px; margin-top: 24px;">
                @if ($isMonthlyMember)
                    <div>
                        <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Monthly fee</p>
                        <p style="margin: 0; font-size: 20px;">{{ $money((int) $currentPlan->monthly_fee) }}</p>
                    </div>
                @else
                    <div>
                        <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Yearly fee</p>
                        <p style="margin: 0; font-size: 20px;">{{ $money((int) $currentPlan->annual_fee) }}</p>
                    </div>
                    @if ($currentPlan->monthlyAvailable() && ! $isYearlyMember)
                        <div>
                            <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Or monthly</p>
                            <p style="margin: 0; font-size: 20px;">{{ $money((int) $currentPlan->monthly_fee) }}</p>
                        </div>
                    @endif
                @endif
                <div>
                    <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 4px; text-transform: uppercase; letter-spacing: .06em;">Entrance fee</p>
                    <p style="margin: 0; font-size: 20px;">None</p>
                </div>
            </div>

            @if (! empty($currentPlan->benefits))
                <div style="margin-top: 24px;">
                    <p style="font-size: 12px; color: var(--gold-500); margin: 0 0 10px; text-transform: uppercase; letter-spacing: .06em;">Your benefits</p>
                    <ul style="margin: 0; padding-left: 20px; color: var(--mist-300); display: grid; gap: 6px;">
                        @foreach ($currentPlan->benefits as $benefit)
                            <li>{{ $benefit }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @if ($membership?->isMonthly())
            <div class="card-light" style="margin-bottom: 20px; background: rgba(201,169,97,.1); border: 1px solid rgba(201,169,97,.35);">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 14px; align-items: center;">
                    <div>
                        <h3 style="font-size: 20px; margin: 0 0 6px;">Switch to yearly and unlock discounts</h3>
                        <p style="margin: 0; color: var(--slate-600);">Monthly members keep members-only access, but only yearly members receive discounts on sections and services.</p>
                    </div>
                    @if ($samePlanSwitch)
                        <a href="{{ route('account.membership.upgrade', ['plan' => $samePlanSwitch->id]) }}" class="btn btn-navy btn-sm">Switch to yearly</a>
                    @endif
                </div>
            </div>
        @endif

        @php
            $includedSections = $currentPlan?->sections?->filter(fn ($section) => $section->active && ($section->pivot->is_active ?? true)) ?? collect();
        @endphp
        @if ($includedSections->isNotEmpty())
            <div class="card-light">
                <h3 style="font-size: 20px; margin: 0 0 6px;">Sections in your plan</h3>
                <p style="color: var(--slate-600); font-size: 14px; margin: 0 0 16px;">
                    @if ($membership?->isMonthly())
                        Your subscription type (Monthly billing) does not offer section discounts. <a href="{{ route('account.membership.upgrade', ['plan' => $samePlanSwitch?->id ?? $currentPlan->id]) }}" class="link-claret" style="font-weight: 600;">Upgrade to a yearly plan</a> so you can get section discounts up to 25% off.
                    @else
                        You are getting this discount because you are a Yearly paying member of <strong>{{ $currentPlan->name }}</strong>.
                    @endif
                </p>
                <div style="display: grid; gap: 10px;">
                    @foreach ($includedSections as $section)
                        @php $cta = $sectionCta($section); @endphp
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; padding: 12px 14px; border: 1px solid #E0D5B8; border-radius: 4px; background: #faf8f3;">
                            <div>
                                <strong><a href="{{ $cta['url'] }}" class="link-claret" style="text-decoration: none;">{{ $section->name }} →</a></strong>
                                <p style="font-size: 13px; color: var(--slate-600); margin: 4px 0 0;">
                                    <a href="{{ $cta['url'] }}" style="color: inherit; text-decoration: none;">{{ $cta['text'] }}</a>
                                </p>
                            </div>
                            @php
                                $dLabel = $discountLabel($section);
                                $badgeText = $membership?->isMonthly()
                                    ? ($dLabel === 'Included' ? 'Upgrade to get discounts' : 'Upgrade to get '.$dLabel)
                                    : $dLabel;
                            @endphp
                            <span class="badge badge-gold">{{ $badgeText }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($isActive)
            <p style="color: var(--slate-600); font-size: 14px; margin: 18px 0 0;">
                To cancel your membership or discuss your plan, please contact the club on
                <a href="tel:{{ preg_replace('/\s+/', '', config('club.phone')) }}" class="link-claret">{{ config('club.phone') }}</a>
                or <a href="mailto:{{ config('club.email') }}" class="link-claret">{{ config('club.email') }}</a>.
            </p>
        @endif
    </section>
@endif

@if ($applications->isNotEmpty())
    <section style="margin-bottom: 40px;">
        <h2 style="font-size: 24px; margin-bottom: 6px;">Application status</h2>
        <p style="color: var(--slate-600); margin-bottom: 18px;">Follow the progress of your membership application.</p>

        <div style="display: grid; gap: 20px;">
            @foreach ($applications as $application)
                <div class="card-light">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start;">
                        <div>
                            <h3 style="font-size: 22px; margin: 0 0 6px;">{{ $application->resolvedPlan()?->name ?? 'Membership application' }}</h3>
                            <p style="color: var(--slate-600); margin: 0;">
                                Reference {{ $application->reference }} · submitted {{ $application->submitted_at?->format('j F Y') ?? 'not yet' }}
                            </p>
                        </div>
                        <span class="badge badge-{{ $application->status === 'active' ? 'green' : (in_array($application->status, ['rejected', 'withdrawn', 'expired'], true) ? 'red' : 'gold') }}">
                            {{ $statusLabels[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>
                    </div>

                    @if ($application->status === 'submitted')
                        <div style="margin-top: 16px; padding: 14px; background: rgba(201,169,97,.12); border: 1px solid rgba(201,169,97,.3); border-radius: 4px;">
                            <p style="margin: 0 0 6px; font-weight: 600;">Bring an accepted ID to the office</p>
                            <p style="margin: 0; font-size: 14px; color: var(--slate-600);">
                                We are reviewing your application. Please visit {{ config('club.name') }}, {{ config('club.address') }}, with your original ID so we can verify your documents.
                            </p>
                        </div>
                    @elseif ($application->status === 'fees_notified')
                        <div style="margin-top: 16px; padding: 14px; background: rgba(201,169,97,.12); border: 1px solid rgba(201,169,97,.3); border-radius: 4px;">
                            <p style="margin: 0 0 6px; font-weight: 600;">Approved — payment due</p>
                            <p style="margin: 0 0 12px; font-size: 14px; color: var(--slate-600);">Choose monthly or yearly billing to activate your membership.</p>
                            <a href="{{ route('account.applications.pay', $application) }}" class="btn btn-navy btn-sm">Choose billing &amp; pay</a>
                        </div>
                    @elseif ($application->status === 'rejected' && $application->rejection_reason)
                        <p style="margin: 16px 0 0; font-size: 14px; color: var(--claret-700);"><strong>Reason:</strong> {{ $application->rejection_reason }}</p>
                    @endif

                    @if ($application->invoices->isNotEmpty())
                        <div style="margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap;">
                            @foreach ($application->invoices as $invoice)
                                <a href="{{ route('account.invoices.show', $invoice) }}" class="btn btn-line-dark btn-sm">
                                    View invoice {{ $invoice->reference }} ({{ ucfirst($invoice->status) }})
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if (! in_array($application->status, ['active', 'payment_received'], true))
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; padding-top: 16px; border-top: 1px solid #E0D5B8;">
                            <a href="{{ route('account.applications.form') }}" class="btn btn-line-dark btn-sm">Download application form</a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif


@if (! $currentPlan && ! $currentApplication)
    <div class="card-light">
        <h2 style="font-size: 22px; margin: 0 0 10px;">You are not a member yet</h2>
        <p style="color: var(--slate-600); margin: 0 0 16px;">
            You can still book rooms and services as a guest at standard rates. <strong>Subscribe to a yearly membership plan</strong> and unlock exclusive member discounts across all club sections:
        </p>
        @if (isset($sections) && $sections->isNotEmpty())
            <div style="display: grid; gap: 10px; margin-bottom: 20px;">
                @foreach ($sections as $section)
                    @php
                        $samplePivot = $section->plans->first()?->pivot;
                        $sampleType = $samplePivot->type ?? 'percent';
                        $sampleVal = (int) ($samplePivot->value ?? 15);
                        $sampleDiscount = $sampleVal > 0 ? ($sampleType === 'fixed' ? '£'.number_format($sampleVal / 100, 2).' off' : $sampleVal.'% off') : 'Member access';
                        $cta = $sectionCta($section);
                    @endphp
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; padding: 12px 14px; border: 1px solid #E0D5B8; border-radius: 4px; background: #faf8f3;">
                        <div>
                            <strong><a href="{{ $cta['url'] }}" class="link-claret" style="text-decoration: none;">{{ $section->name }} →</a></strong>
                            <p style="font-size: 13px; color: var(--slate-600); margin: 4px 0 0;">
                                <a href="{{ $cta['url'] }}" style="color: inherit; text-decoration: none;">{{ $cta['text'] }}</a>
                            </p>
                        </div>
                        <span class="badge badge-gold">Subscribe to get {{ $sampleDiscount }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        <a href="{{ route('account.applications.start') }}" class="btn btn-navy">Subscribe to a plan</a>
    </div>
@endif
@endsection
