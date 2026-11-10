@extends('layouts.panel', ['title' => 'Member - ' . $user->name])

@section('content')
@php
    $money = fn (int $pence) => '£'.number_format($pence / 100, 2);
    $membership = $user->membership;
    $profile = $user->memberProfile;
    $hasActivePlan = $membership?->plan !== null;
    $isPastDueWithPlan = $membership && $membership->status === 'past_due' && $hasActivePlan;
    $statusLabels = [
        'pending' => 'Pending',
        'active' => 'Active',
        'past_due' => $hasActivePlan ? 'Paid' : 'Payment overdue',
        'suspended' => 'Suspended',
        'terminated' => 'Terminated',
        'cancelled' => 'Cancelled',
    ];
    $status = $membership?->status;
    $badge = ($status === 'active' || $isPastDueWithPlan) ? 'green' : (in_array($status, ['cancelled', 'terminated', 'suspended'], true) ? 'red' : 'gold');
@endphp

<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.members.index') }}" class="link-claret" style="font-size: 14px;">← Back to members</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $user->name }}</h1>
    <p style="color: var(--slate-600);">
        Email: {{ $user->email }}
        · Member number: {{ $profile?->member_number ?? '—' }}
        · Joined: {{ $profile?->joined_at?->format('j M Y') ?? $user->created_at->format('j M Y') }}
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

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start; margin-bottom: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: flex-start;">
                <div>
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700); margin-bottom: 6px;">Membership</p>
                    <h2 style="font-size: 26px; margin: 0 0 6px;">{{ $membership?->plan?->name ?? 'No active plan' }}</h2>
                    @if ($membership)
                        <p style="color: var(--slate-600); margin: 0;">
                            {{ $membership->isMonthly() ? 'Monthly billing — members-only access, no discounts' : 'Yearly billing — member discounts included' }}
                        </p>
                    @else
                        <p style="color: var(--slate-600); margin: 0;">This user does not have a membership record yet.</p>
                    @endif
                </div>
                @if ($membership)
                    <span class="badge badge-{{ $badge }}">{{ $statusLabels[$status] ?? ucfirst((string) $status) }}</span>
                @endif
            </div>

            @if ($membership)
                <dl style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; margin: 22px 0 0; padding-top: 18px; border-top: 1px solid #E0D5B8;">
                    <div>
                        <dt style="font-size: 12px; color: var(--slate-600); text-transform: uppercase; letter-spacing: .06em;">Member since</dt>
                        <dd style="margin: 4px 0 0; font-weight: 600;">{{ $membership->starts_at?->format('j F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size: 12px; color: var(--slate-600); text-transform: uppercase; letter-spacing: .06em;">Renews</dt>
                        <dd style="margin: 4px 0 0; font-weight: 600;">{{ $membership->renews_at?->format('j F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size: 12px; color: var(--slate-600); text-transform: uppercase; letter-spacing: .06em;">Billing</dt>
                        <dd style="margin: 4px 0 0; font-weight: 600;">{{ $membership->isMonthly() ? 'Monthly' : 'Yearly' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size: 12px; color: var(--slate-600); text-transform: uppercase; letter-spacing: .06em;">Access ends</dt>
                        <dd style="margin: 4px 0 0; font-weight: 600;">{{ $membership->ends_at?->format('j F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt style="font-size: 12px; color: var(--slate-600); text-transform: uppercase; letter-spacing: .06em;">Subscription</dt>
                        <dd style="margin: 4px 0 0; font-weight: 600;">{{ $membership->hasStripeSubscription() ? 'Active in Stripe' : 'None' }}</dd>
                    </div>
                </dl>

                @if ($membership->plan)
                    <div style="display: flex; flex-wrap: wrap; gap: 24px; margin-top: 18px;">
                        <p style="margin: 0; color: var(--slate-600);">Yearly fee: <strong style="color: var(--ink-900);">{{ $money((int) $membership->plan->annual_fee) }}</strong></p>
                        @if ($membership->plan->monthlyAvailable())
                            <p style="margin: 0; color: var(--slate-600);">Monthly fee: <strong style="color: var(--ink-900);">{{ $money((int) $membership->plan->monthly_fee) }}</strong></p>
                        @endif
                    </div>
                @endif

                @if ($membership->cancelled_at)
                    <div style="margin-top: 18px; padding: 12px 14px; background: #FEEBEE; border-left: 4px solid #E53935; border-radius: 4px;">
                        <p style="margin: 0; font-size: 14px; color: #C62828;">
                            Cancellation requested {{ $membership->cancelled_at->format('j M Y') }}
                            @if ($membership->cancellation_reason)
                                — {{ $membership->cancellation_reason }}
                            @endif
                        </p>
                    </div>
                @endif
            @endif
        </div>

        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 14px;">Profile</h2>
            <dl style="display: grid; gap: 10px; margin: 0;">
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Phone</dt>
                    <dd style="margin: 0;">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Address</dt>
                    <dd style="margin: 0; text-align: right;">
                        {{ $user->address_line_1 ?? '—' }}<br>
                        @if ($user->address_line_2){{ $user->address_line_2 }}<br>@endif
                        {{ $user->city ?? '' }} {{ $user->postcode ?? '' }}
                    </dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                    <dt style="color: var(--slate-600);">Financial status</dt>
                    <dd style="margin: 0;">{{ $profile?->is_financial ? 'In good standing' : 'Not financial' }}</dd>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 16px;">
                    <dt style="color: var(--slate-600);">Account status</dt>
                    <dd style="margin: 0;">{{ $profile?->is_suspended ? 'Suspended' : 'Active' }}</dd>
                </div>
            </dl>
        </div>

        @if ($user->applications->isNotEmpty())
            <div class="card-light">
                <h2 style="font-size: 20px; margin-bottom: 12px;">Applications</h2>
                <div style="display: grid; gap: 8px;">
                    @foreach ($user->applications->sortByDesc('id') as $application)
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                            <span>{{ $application->reference }} · {{ $application->plan?->name ?? 'Plan not selected' }}</span>
                            <span style="color: var(--slate-600);">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 20px; margin: 0 0 14px;">Admin actions</h2>

            @if (! $membership)
                <p style="margin: 0; color: var(--mist-300); font-size: 14px;">There is no membership to manage for this user yet.</p>
            @else
                @if ($membership->status === 'suspended')
                    <form method="POST" action="{{ route('admin.members.reinstate', $user) }}" style="margin-bottom: 16px;">
                        @csrf
                        <button type="submit" class="btn btn-gold" style="width: 100%;">Reinstate membership</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.members.suspend', $user) }}" style="margin-bottom: 16px;">
                        @csrf
                        <button type="submit" class="btn btn-line-light" style="width: 100%;">Suspend membership</button>
                    </form>
                @endif

                @if (! $membership->cancelled_at && $status !== 'cancelled' && $status !== 'terminated')
                    <form method="POST" action="{{ route('admin.members.cancel', $user) }}" style="padding-top: 16px; border-top: 1px solid rgba(201,169,97,.25);">
                        @csrf
                        <p style="font-size: 13px; color: var(--mist-300); margin: 0 0 10px;">
                            Only administrators can cancel a membership. By default the subscription stops renewing at the end of the current billing period.
                        </p>
                        <label class="field-label" for="reason" style="color: var(--ivory-100);">Cancellation reason</label>
                        <textarea name="reason" id="reason" rows="2" class="field-dark" required placeholder="Reason recorded on the audit log and emailed to the member…" style="margin-bottom: 10px;"></textarea>
                        <label style="display: flex; gap: 8px; align-items: flex-start; font-size: 13px; color: var(--mist-300); margin-bottom: 12px;">
                            <input type="checkbox" name="immediately" value="1" style="margin-top: 3px;">
                            <span>Cancel immediately and revoke access now</span>
                        </label>
                        <button type="submit" class="btn btn-gold" style="width: 100%;">Cancel membership</button>
                    </form>
                @else
                    <p style="margin: 16px 0 0; padding-top: 16px; border-top: 1px solid rgba(201,169,97,.25); font-size: 13px; color: var(--champagne-200);">
                        This membership has already been cancelled.
                    </p>
                @endif
            @endif
        </div>
    </aside>
</div>
@endsection
