@extends('layouts.panel', ['title' => ($hall->exists ? 'Edit' : 'Create').' meeting room'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.halls.index') }}" class="link-claret" style="font-size: 14px;">← Back to meeting rooms</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $hall->exists ? 'Edit room: '.$hall->name : 'Create new meeting room' }}</h1>
    <p style="color: var(--slate-600);">Configure meeting room capacities, hourly rates by day type, operating hours, and features.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        <ul style="margin: 0 0 0 18px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $hall->exists ? route('admin.halls.update', $hall) : route('admin.halls.store') }}" enctype="multipart/form-data" style="display: grid; gap: 24px;">
    @csrf
    @if ($hall->exists) @method('PUT') @endif

    <div class="card-light" style="display: grid; gap: 16px;">
        <h2 style="font-size: 22px; margin: 0 0 8px;">General Information</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="name">Room Name</label>
                <input class="field-light" id="name" name="name" value="{{ old('name', $hall->name) }}" required>
            </div>
            <div>
                <label class="field-label" for="slug">Slug <span style="font-weight: 400; color: var(--slate-600);">(optional)</span></label>
                <input class="field-light" id="slug" name="slug" value="{{ old('slug', $hall->slug) }}">
            </div>
        </div>

        <div>
            <label class="field-label" for="description">Description</label>
            <textarea class="field-light" id="description" name="description" rows="4">{{ old('description', $hall->description) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            <div>
                <label class="field-label" for="capacity">Standing Capacity</label>
                <input class="field-light" id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $hall->capacity ?? 20) }}" required>
            </div>
            <div>
                <label class="field-label" for="capacity_seated">Seated Capacity</label>
                <input class="field-light" id="capacity_seated" name="capacity_seated" type="number" min="0" value="{{ old('capacity_seated', $hall->capacity_seated ?? 16) }}">
            </div>
            <div>
                <label class="field-label" for="area">Area Size</label>
                <input class="field-light" id="area" name="area" value="{{ old('area', $hall->area) }}" placeholder="e.g. 45 m²">
            </div>
            <div>
                <label class="field-label" for="min_hours">Minimum Hours</label>
                <input class="field-light" id="min_hours" name="min_hours" type="number" min="1" max="12" value="{{ old('min_hours', $hall->min_hours ?? 2) }}" required>
            </div>
            <div>
                <label class="field-label" for="buffer_minutes">Buffer Minutes</label>
                <input class="field-light" id="buffer_minutes" name="buffer_minutes" type="number" min="0" max="180" value="{{ old('buffer_minutes', $hall->buffer_minutes ?? 30) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 12px;">
            <div>
                <label class="field-label" for="seo_title">SEO Title</label>
                <input class="field-light" id="seo_title" name="seo_title" value="{{ old('seo_title', $hall->seo_title) }}">
            </div>
            <div>
                <label class="field-label" for="seo_description">SEO Description</label>
                <input class="field-light" id="seo_description" name="seo_description" value="{{ old('seo_description', $hall->seo_description) }}">
            </div>
        </div>

        <div>
            <label class="field-label" for="image">Upload Main Room Image <span style="font-weight: 400; color: var(--slate-600);">(JPG/PNG, max 10MB)</span></label>
            <input class="field-light" id="image" name="image" type="file" accept="image/*">
        </div>

        <div>
            <label class="field-label" for="images">Upload Additional Room Images <span style="font-weight: 400; color: var(--slate-600);">(Multiple JPG/PNG, max 10MB each)</span></label>
            <input class="field-light" id="images" name="images[]" type="file" accept="image/*" multiple>
        </div>

        @if($hall->exists && $hall->images->isNotEmpty())
            <div>
                <label class="field-label">Existing Room Images</label>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 8px;">
                    @foreach($hall->images as $img)
                        <div style="background: #fff; padding: 8px; border: 1px solid #E0D5B8; border-radius: 4px; display: flex; flex-direction: column; gap: 8px; align-items: center;">
                            <img src="{{ route('media.public', $img->path) }}" alt="Room image" style="width: 120px; height: 90px; object-fit: cover; border-radius: 4px;">
                            <button type="submit" formaction="{{ route('admin.halls.images.destroy', $img) }}" formmethod="POST" onclick="return confirm('Delete this image?');" class="btn btn-sm" style="background: #FEEBEE; color: #C62828; border: 1px solid #E53935; padding: 4px 8px; font-size: 12px;">Delete</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div style="margin-top: 8px;">
            <label style="display: flex; gap: 10px; align-items: center; cursor: pointer;">
                <input type="checkbox" name="active" value="1" @checked(old('active', $hall->exists ? $hall->active : true)) style="width: 18px; height: 18px;">
                <span>Active room (available for booking on public website)</span>
            </label>
        </div>
    </div>

    <!-- Room Rates per Day Type -->
    <div class="card-light" style="display: grid; gap: 16px;">
        <h2 style="font-size: 22px; margin: 0 0 4px;">Hourly Rates by Day Type</h2>
        <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 12px;">Enter standard hourly price in pounds (e.g. 45.00 for £45.00/hr) and member discount settings.</p>

        @php
            $dayTypes = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'BankHoliday'];
        @endphp
        <div style="display: grid; gap: 12px;">
            @foreach($dayTypes as $dt)
                @php
                    $rate = $hall->rates?->firstWhere('day_type', $dt);
                    $priceVal = $rate ? number_format($rate->standard_price_per_hour / 100, 2, '.', '') : '';
                @endphp
                <div style="display: grid; grid-template-columns: 140px 1fr 1fr 1fr; gap: 12px; align-items: center; background: #fff; padding: 12px; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <strong>{{ $dt }}</strong>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Std Price (£/hr)</label>
                        <input class="field-light" name="ratings[{{ $dt }}][standard_price_per_hour]" type="number" step="0.01" min="0" value="{{ old('ratings.'.$dt.'.standard_price_per_hour', $priceVal) }}" placeholder="e.g. 45.00">
                    </div>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Discount Type</label>
                        <select class="field-light" name="ratings[{{ $dt }}][member_discount_type]">
                            <option value="percent" @selected(old('ratings.'.$dt.'.member_discount_type', $rate?->member_discount_type) === 'percent')>Percentage</option>
                            <option value="fixed" @selected(old('ratings.'.$dt.'.member_discount_type', $rate?->member_discount_type) === 'fixed')>Fixed (£)</option>
                            <option value="flat" @selected(old('ratings.'.$dt.'.member_discount_type', $rate?->member_discount_type) === 'flat')>Flat rate</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Discount Value</label>
                        <input class="field-light" name="ratings[{{ $dt }}][member_discount_value]" type="number" min="0" value="{{ old('ratings.'.$dt.'.member_discount_value', $rate?->member_discount_value ?? 20) }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Operating Hours -->
    <div class="card-light" style="display: grid; gap: 16px;">
        <h2 style="font-size: 22px; margin: 0 0 4px;">Operating Hours</h2>
        <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 12px;">Define opening and closing times for each weekday (0 = Sunday, 1 = Monday, etc.). Leave blank if closed.</p>

        @php
            $weekdays = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                0 => 'Sunday',
            ];
        @endphp
        <div style="display: grid; gap: 12px;">
            @foreach($weekdays as $wNum => $wName)
                @php
                    $hr = $hall->hours?->firstWhere('weekday', $wNum);
                @endphp
                <div style="display: grid; grid-template-columns: 140px 1fr 1fr; gap: 12px; align-items: center; background: #fff; padding: 12px; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <strong>{{ $wName }}</strong>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Opens At</label>
                        <input class="field-light" name="hours[{{ $wNum }}][opens_at]" type="time" value="{{ old('hours.'.$wNum.'.opens_at', $hr?->opens_at) }}">
                    </div>
                    <div>
                        <label style="font-size: 12px; display: block; color: var(--slate-600);">Closes At</label>
                        <input class="field-light" name="hours[{{ $wNum }}][closes_at]" type="time" value="{{ old('hours.'.$wNum.'.closes_at', $hr?->closes_at) }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Features -->
    <div class="card-light" style="display: grid; gap: 16px;">
        <h2 style="font-size: 22px; margin: 0 0 4px;">Room Features & Amenities</h2>
        <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 12px;">Enter each feature on a new line (e.g. Projector, High-speed Wi-Fi, Air Conditioning).</p>
        <div>
            <textarea class="field-light" name="features" rows="4" placeholder="Projector & Screen&#10;High-speed Wi-Fi&#10;Whiteboard&#10;Air Conditioning">{{ old('features', $hall->features?->pluck('name')->implode("\n")) }}</textarea>
        </div>
    </div>

    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button type="submit" class="btn btn-navy">{{ $hall->exists ? 'Update Room' : 'Create Room' }}</button>
        <a href="{{ route('admin.halls.index') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection
