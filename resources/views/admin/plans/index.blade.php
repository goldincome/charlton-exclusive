@extends('layouts.panel', ['title' => 'Membership plans'])

@section('content')
<header style="margin-bottom: 32px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Membership plans</h1>
        <p style="color: var(--slate-600);">Plans replace membership categories. Saving a plan queues a Stripe sync (Product/Price); the webhook/local sync updates Stripe IDs.</p>
    </div>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-navy btn-sm">Add plan</a>
</header>

<div class="card-light" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f6f1e4; text-align:left;">
                <th style="padding:14px 16px;">Plan</th>
                <th style="padding:14px 16px;">Fees</th>
                <th style="padding:14px 16px;">Stripe</th>
                <th style="padding:14px 16px;">Status</th>
                <th style="padding:14px 16px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($plans as $plan)
                <tr style="border-top:1px solid #E0D5B8; vertical-align:top;">
                    <td style="padding:14px 16px;">
                        <strong>{{ $plan->name }}</strong><br>
                        <span style="color:var(--slate-600); font-size:13px;">{{ $plan->slug }} · {{ $plan->discounts_count }} discount rules</span>
                    </td>
                    <td style="padding:14px 16px; font-size:14px;">
                        Entrance £{{ number_format($plan->entrance_fee / 100, 2) }}<br>
                        Annual £{{ number_format($plan->annual_fee / 100, 2) }}
                    </td>
                    <td style="padding:14px 16px; font-size:13px; color:var(--slate-600);">
                        Sync: {{ $plan->stripe_sync_status ?: 'not synced' }}<br>
                        @if($plan->stripe_price_id) Price: {{ $plan->stripe_price_id }}<br>@endif
                        @if($plan->stripe_sync_error) <span style="color:#C62828;">{{ \Illuminate\Support\Str::limit($plan->stripe_sync_error, 80) }}</span>@endif
                    </td>
                    <td style="padding:14px 16px;">{{ $plan->is_active ? 'Active' : 'Inactive' }}</td>
                    <td style="padding:14px 16px; white-space:nowrap;">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="link-claret">Edit</a>
                        <form method="POST" action="{{ route('admin.plans.sync', $plan) }}" style="display:inline; margin-left:10px;">
                            @csrf
                            <button type="submit" class="link-claret" style="background:none;border:none;padding:0;cursor:pointer;">Sync Stripe</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:18px 16px; color:var(--slate-600);">No plans yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
