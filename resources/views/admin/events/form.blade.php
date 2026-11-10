@extends('layouts.panel', ['title' => ($event->exists ? 'Edit' : 'Create').' event'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.events.index') }}" class="link-claret" style="font-size: 14px;">← Back to events</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $event->exists ? 'Edit event: '.$event->name : 'Create new event' }}</h1>
    <p style="color: var(--slate-600);">Configure event details, pricing, member discounts, and capacity limits.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        <ul style="margin: 0 0 0 18px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}" style="display: grid; gap: 24px;">
    @csrf
    @if ($event->exists) @method('PUT') @endif

    <div class="card-light" style="display: grid; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="name">Event Name</label>
                <input class="field-light" id="name" name="name" value="{{ old('name', $event->name) }}" required>
            </div>
            <div>
                <label class="field-label" for="slug">Slug <span style="font-weight: 400; color: var(--slate-600);">(optional)</span></label>
                <input class="field-light" id="slug" name="slug" value="{{ old('slug', $event->slug) }}">
            </div>
        </div>

        <div>
            <label class="field-label" for="description">Description</label>
            <textarea class="field-light" id="description" name="description" rows="4">{{ old('description', $event->description) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="starts_at">Starts At</label>
                <input class="field-light" id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div>
                <label class="field-label" for="ends_at">Ends At</label>
                <input class="field-light" id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div>
                <label class="field-label" for="venue">Venue</label>
                <input class="field-light" id="venue" name="venue" value="{{ old('venue', $event->venue) }}" placeholder="e.g. Main Dining Hall">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="capacity">Total Capacity</label>
                <input class="field-light" id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $event->capacity ?? 50) }}" required>
            </div>
            <div>
                <label class="field-label" for="tickets_per_booking">Max Tickets / Booking</label>
                <input class="field-light" id="tickets_per_booking" name="tickets_per_booking" type="number" min="1" value="{{ old('tickets_per_booking', $event->tickets_per_booking ?? 6) }}" required>
            </div>
            <div>
                <label class="field-label" for="price_per_ticket">Price per Ticket (£)</label>
                <input class="field-light" id="price_per_ticket" name="price_per_ticket" type="number" step="0.01" min="0" value="{{ old('price_per_ticket', number_format(($event->price_per_ticket ?? 0) / 100, 2, '.', '')) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="member_discount_type">Member Discount Type</label>
                <select class="field-light" id="member_discount_type" name="member_discount_type">
                    <option value="percent" @selected(old('member_discount_type', $event->member_discount_type) === 'percent')>Percentage (%)</option>
                    <option value="fixed" @selected(old('member_discount_type', $event->member_discount_type) === 'fixed')>Fixed amount off (£)</option>
                    <option value="flat" @selected(old('member_discount_type', $event->member_discount_type) === 'flat')>Flat rate (£)</option>
                </select>
            </div>
            <div>
                <label class="field-label" for="member_discount_value">Member Discount Value</label>
                <input class="field-light" id="member_discount_value" name="member_discount_value" type="number" min="0" value="{{ old('member_discount_value', $event->member_discount_value ?? 0) }}" required>
                <span style="font-size: 12px; color: var(--slate-600);">Enter percentage (e.g. 15 for 15%) or pence (e.g. 500 for £5.00 off).</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="seo_title">SEO Title</label>
                <input class="field-light" id="seo_title" name="seo_title" value="{{ old('seo_title', $event->seo_title) }}">
            </div>
            <div>
                <label class="field-label" for="seo_description">SEO Description</label>
                <input class="field-light" id="seo_description" name="seo_description" value="{{ old('seo_description', $event->seo_description) }}">
            </div>
        </div>

        <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-top: 8px;">
            <label style="display: flex; gap: 10px; align-items: center; cursor: pointer;">
                <input type="checkbox" name="active" value="1" @checked(old('active', $event->exists ? $event->active : true)) style="width: 18px; height: 18px;">
                <span>Active event (visible on public site)</span>
            </label>
            <label style="display: flex; gap: 10px; align-items: center; cursor: pointer;">
                <input type="checkbox" name="members_only" value="1" @checked(old('members_only', $event->members_only)) style="width: 18px; height: 18px;">
                <span>Members only event</span>
            </label>
        </div>
    </div>

    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button type="submit" class="btn btn-navy">{{ $event->exists ? 'Update Event' : 'Create Event' }}</button>
        <a href="{{ route('admin.events.index') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection
