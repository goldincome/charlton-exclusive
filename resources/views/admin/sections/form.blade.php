@extends('layouts.panel', ['title' => ($section->exists ? 'Edit' : 'Add').' section'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.sections.index') }}" class="link-claret" style="font-size: 14px;">← Back to sections</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $section->exists ? 'Edit section' : 'Add section' }}</h1>
    <p style="color: var(--slate-600);">Set access (members only or open), pause/resume, and configure per-plan discounts used when a member uses this section. Fixed discounts are stored as pence; percent discounts are 0–100.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background:#FEEBEE;color:#C62828;padding:14px 18px;border-radius:4px;margin-bottom:24px;border-left:4px solid #E53935;">
        Please check the form and try again.
        <ul style="margin:8px 0 0 18px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $section->exists ? route('admin.sections.update', $section) : route('admin.sections.store') }}" style="display:grid; gap:24px;">
    @csrf
    @if ($section->exists) @method('PUT') @endif

    <div class="card-light" style="display:grid; gap:16px;">
        <div>
            <label class="field-label" for="name">Name</label>
            <input class="field-light" id="name" name="name" value="{{ old('name', $section->name) }}" required>
        </div>
        <div>
            <label class="field-label" for="slug">Slug (optional)</label>
            <input class="field-light" id="slug" name="slug" value="{{ old('slug', $section->slug) }}">
        </div>
        <div>
            <label class="field-label" for="description">Description</label>
            <textarea class="field-light" id="description" name="description" rows="3">{{ old('description', $section->description) }}</textarea>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
            <div>
                <label class="field-label" for="access_type">Access</label>
                <select class="field-light" id="access_type" name="access_type" required>
                    <option value="members_only" @selected(old('access_type', $section->access_type) === 'members_only')>Members only (discounts apply)</option>
                    <option value="open" @selected(old('access_type', $section->access_type) === 'open')>Open to members and non-members</option>
                </select>
            </div>
            <div>
                <label class="field-label" for="type">Booking/order type</label>
                <select class="field-light" id="type" name="type" required>
                    <option value="slot" @selected(old('type', $section->type) === 'slot')>Slot (hall/room/party/event)</option>
                    <option value="cart" @selected(old('type', $section->type) === 'cart')>Cart (food/orders)</option>
                </select>
            </div>
        </div>
        <label style="display:flex; gap:10px; align-items:center;">
            <input type="checkbox" name="active" value="1" @checked(old('active', $section->exists ? $section->active : true))>
            <span>Active (unchecked = paused)</span>
        </label>
    </div>

    <div class="card-light">
        <h2 style="font-size:22px; margin-bottom:8px;">Membership plan discounts</h2>
        <p style="color:var(--slate-600); margin-bottom:16px;">For members-only sections, each plan can have its own discount. Discounts are calculated each time the member uses the section (not yearly). <strong>Percent</strong>: 0–100. <strong>Fixed</strong>: amount in pence (e.g. 1500 = £15.00 off).</p>
        <div style="overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f6f1e4; text-align:left;">
                        <th style="padding:12px;">Plan</th>
                        <th style="padding:12px;">Discount type</th>
                        <th style="padding:12px;">Value (percent points or pence)</th>
                        <th style="padding:12px;">Enabled</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                        @php $discount = $section->discounts->firstWhere('plan_id', $plan->id); @endphp
                        <tr style="border-top:1px solid #E0D5B8;">
                            <td style="padding:12px;"><strong>{{ $plan->name }}</strong></td>
                            <td style="padding:12px;">
                                <select class="field-light" name="discounts[{{ $plan->id }}][type]">
                                    <option value="percent" @selected(old("discounts.$plan->id.type", $discount->type ?? 'percent') === 'percent')>Percent</option>
                                    <option value="fixed" @selected(in_array(old("discounts.$plan->id.type", $discount->type ?? ''), ['fixed','flat'], true))>Fixed (pence)</option>
                                </select>
                            </td>
                            <td style="padding:12px;">
                                <input class="field-light" type="number" min="0" step="1" name="discounts[{{ $plan->id }}][value]" value="{{ old("discounts.$plan->id.value", $discount->value ?? 0) }}" style="max-width:180px;">
                            </td>
                            <td style="padding:12px;">
                                <input type="checkbox" name="discounts[{{ $plan->id }}][is_active]" value="1" @checked(old("discounts.$plan->id.is_active", $discount->is_active ?? ($section->access_type !== 'open')))>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <button type="submit" class="btn btn-navy">{{ $section->exists ? 'Save section' : 'Create section' }}</button>
        <a href="{{ route('admin.sections.index') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection
