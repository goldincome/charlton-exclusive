@extends('layouts.panel', ['title' => ($plan->exists ? 'Edit' : 'Add').' plan'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.plans.index') }}" class="link-claret" style="font-size: 14px;">← Back to plans</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $plan->exists ? 'Edit plan' : 'Add plan' }}</h1>
    <p style="color: var(--slate-600);">Money fields are entered in pounds (e.g. 1200.00) and stored as pence. Plans support both <strong>yearly</strong> and <strong>monthly</strong> billing options with separate prices; members choose their payment frequency upon checkout. Saving queues Stripe sync.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background:#FEEBEE;color:#C62828;padding:14px 18px;border-radius:4px;margin-bottom:24px;border-left:4px solid #E53935;">
        <ul style="margin:0 0 0 18px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" style="display:grid; gap:24px;">
    @csrf
    @if ($plan->exists) @method('PUT') @endif

    <div class="card-light" style="display:grid; gap:16px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
            <div><label class="field-label" for="name">Name</label><input class="field-light" id="name" name="name" value="{{ old('name', $plan->name) }}" required></div>
            <div><label class="field-label" for="slug">Slug</label><input class="field-light" id="slug" name="slug" value="{{ old('slug', $plan->slug) }}"></div>
        </div>
        <div><label class="field-label" for="description">Description</label><textarea class="field-light" id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea></div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;">
            <div><label class="field-label" for="entrance_fee">Entrance fee (£)</label><input class="field-light" id="entrance_fee" name="entrance_fee" type="number" step="0.01" min="0" value="{{ old('entrance_fee', number_format(($plan->entrance_fee ?? 0)/100, 2, '.', '')) }}" required></div>
            <div><label class="field-label" for="annual_fee">Annual fee (£)</label><input class="field-light" id="annual_fee" name="annual_fee" type="number" step="0.01" min="0" value="{{ old('annual_fee', number_format(($plan->annual_fee ?? 0)/100, 2, '.', '')) }}" required></div>
            <div><label class="field-label" for="monthly_fee">Monthly fee (£)</label><input class="field-light" id="monthly_fee" name="monthly_fee" type="number" step="0.01" min="0" value="{{ old('monthly_fee', $plan->monthly_fee !== null ? number_format($plan->monthly_fee/100, 2, '.', '') : '') }}" required placeholder="e.g. 45.00"></div>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;">
            <div><label class="field-label" for="price">Stripe primary price (£)</label><input class="field-light" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', number_format((($plan->price ?: $plan->annual_fee) ?? 0)/100, 2, '.', '')) }}"></div>
        </div>
        <label style="display:flex; gap:10px; align-items:center;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->exists ? $plan->is_active : true))> <span>Active plan</span></label>
        @if($plan->exists)
            <div style="font-size:13px; color:var(--slate-600); background:#f6f1e4; padding:12px; border-radius:4px;">
                <strong>Stripe sync:</strong> {{ $plan->stripe_sync_status ?: 'not synced' }} @if($plan->stripe_synced_at) ({{ $plan->stripe_synced_at }}) @endif<br>
                <strong>Product:</strong> {{ $plan->stripe_product_id ?: '—' }} · <strong>Price:</strong> {{ $plan->stripe_price_id ?: '—' }}
                @if($plan->stripe_sync_error)<br><span style="color:#C62828;">{{ $plan->stripe_sync_error }}</span>@endif
            </div>
        @endif
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <button type="submit" class="btn btn-navy">Save plan & queue Stripe sync</button>
        <a href="{{ route('admin.plans.index') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection
