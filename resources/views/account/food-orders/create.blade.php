@extends('layouts.panel', ['title' => 'Place a food order'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('account.food-orders.index') }}" class="link-claret" style="font-size: 14px;">← Back to orders</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">Place a food order</h1>
    <p style="color: var(--slate-600);">Order food for an event, meeting or personal dining.</p>
</header>

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        Please check the highlighted fields and try again.
    </div>
@endif

<form method="POST" action="{{ route('account.food-orders.store') }}" style="display: grid; gap: 32px;">
    @csrf
    
    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">Order type</h2>
        <div style="display: grid; gap: 14px;">
            <label style="display: flex; gap: 14px; align-items: flex-start; padding: 16px; border: 2px solid #E0D5B8; border-radius: 4px; cursor: pointer;">
                <input type="radio" name="type" value="personal" required @checked(old('type') === 'personal') style="margin-top: 4px; width: 20px; height: 20px;">
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Personal dining</strong>
                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">Order for yourself or a small group.</p>
                </div>
            </label>
            <label style="display: flex; gap: 14px; align-items: flex-start; padding: 16px; border: 2px solid #E0D5B8; border-radius: 4px; cursor: pointer;">
                <input type="radio" name="type" value="party" required @checked(old('type') === 'party') style="margin-top: 4px; width: 20px; height: 20px;">
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Event or party</strong>
                    <p style="font-size: 14px; color: var(--slate-600); margin: 0;">Order for a meeting room booking or club event.</p>
                </div>
            </label>
        </div>
        @error('type')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">When and where</h2>
        <div style="display: grid; gap: 18px;">
            <div>
                <label class="field-label" for="requested_for">Delivery date & time</label>
                <input class="field-light" id="requested_for" name="requested_for" type="datetime-local" value="{{ old('requested_for') }}" required>
                @error('requested_for')<p class="error-text">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="field-label" for="location">Location <span style="font-weight: 400; color: var(--slate-500);">(optional)</span></label>
                <input class="field-light" id="location" name="location" value="{{ old('location') }}" placeholder="e.g., Meeting Room A">
                @error('location')<p class="error-text">{{ $message }}</p>@enderror
            </div>

            @if ($hallBookings->isNotEmpty())
                <div>
                    <label class="field-label" for="booking_id">Link to a booking <span style="font-weight: 400; color: var(--slate-500);">(optional, for discount)</span></label>
                    <select class="field-light" id="booking_id" name="booking_id">
                        <option value="">No booking</option>
                        @foreach ($hallBookings as $booking)
                            <option value="{{ $booking->id }}" @selected(old('booking_id') == $booking->id)>
                                {{ $booking->bookingable->name }} · {{ $booking->start_time->format('j M Y') }}
                            </option>
                        @endforeach
                    </select>
                    <p style="font-size: 13px; color: var(--slate-600); margin-top: 6px;">Link to a confirmed room booking to receive {{ \App\Models\AppConfig::int('hall_food_discount_value', 20) }}% off food.</p>
                </div>
            @endif

            <div>
                <label class="field-label" for="contact_phone">Contact phone <span style="font-weight: 400; color: var(--slate-500);">(optional)</span></label>
                <input class="field-light" id="contact_phone" name="contact_phone" type="tel" value="{{ old('contact_phone') }}">
            </div>
        </div>
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">Select items</h2>
        <p style="color: var(--slate-600); margin-bottom: 20px;">Choose from the dining menu. Prices include VAT at 20%.</p>
        
        <div id="menu-items" style="display: grid; gap: 14px;">
            @foreach ($items as $item)
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center; padding: 14px; border: 1px solid #E0D5B8; border-radius: 4px;">
                    <div style="flex: 1;">
                        <strong style="display: block; margin-bottom: 4px;">{{ $item->name }}</strong>
                        @if ($item->description)
                            <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 6px;">{{ $item->description }}</p>
                        @endif
                        <p style="font-size: 14px; color: var(--claret-700); font-weight: 600; margin: 0;">£{{ number_format($item->price / 100, 2) }}</p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <label for="item_{{ $item->id }}_qty" class="sr-only">Quantity for {{ $item->name }}</label>
                        <input class="field-light" id="item_{{ $item->id }}_qty" name="items[{{ $loop->index }}][qty]" type="number" min="0" max="50" value="{{ old('items.'.$loop->index.'.qty', 0) }}" style="width: 80px;">
                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                    </div>
                </div>
            @endforeach
        </div>
        @error('items')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="card-light">
        <h2 style="font-size: 22px; margin-bottom: 20px;">Dietary requirements</h2>
        <div>
            <label class="field-label" for="dietary_notes">Allergies, intolerances or preferences</label>
            <textarea class="field-light" id="dietary_notes" name="dietary_notes" rows="4" placeholder="Let us know about any dietary requirements...">{{ old('dietary_notes') }}</textarea>
            @error('dietary_notes')<p class="error-text">{{ $message }}</p>@enderror
        </div>
        
        <label style="display: flex; gap: 12px; align-items: flex-start; margin-top: 16px; font-size: 14px; color: var(--slate-600);">
            <input type="checkbox" name="dietary_consent" value="1" required @checked(old('dietary_consent')) style="margin-top: 4px; width: 20px; height: 20px;">
            <span>I consent to the club storing and using this information to prepare my order safely, as described in the <a href="{{ route('legal', 'privacy') }}" class="link-claret" target="_blank">privacy policy</a>.</span>
        </label>
        @error('dietary_consent')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <button type="submit" class="btn btn-navy">Submit order request</button>
        <a href="{{ route('account.food-orders.index') }}" class="btn btn-line-dark">Cancel</a>
    </div>
</form>
@endsection