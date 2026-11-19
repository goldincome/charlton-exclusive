@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('rooms.index') }}">Meeting rooms</a> <span aria-hidden="true">/</span>
            <a href="{{ route('rooms.show', $hall->slug) }}">{{ $hall->name }}</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">Request a booking</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 460px; display: grid; gap: 28px;">
                <div>
                    <p class="eyebrow" style="color: var(--gold-500);">Book {{ $hall->name }}</p>
                    <h1 style="font-size: clamp(34px, 4.4vw, 52px); margin: 14px 0 16px;">Request a booking</h1>
                    <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 20px;">
                        {{ $hall->description }}
                    </p>
                </div>

                <div style="border-radius: 6px; overflow: hidden; background: #E9E1CB; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    @if ($hall->heroImage())
                        <img id="main-hero-img" src="{{ route('media.public', $hall->heroImage()->path) }}" alt="{{ $hall->heroImage()->alt_text ?: $hall->name }}"
                             width="800" height="600" style="width: 100%; height: 280px; object-fit: cover; display: block;">
                    @else
                        <div class="placeholder" style="height: 280px; background: #E9E1CB; color: var(--slate-600); border: 0; display: flex; align-items: center; justify-content: center;">[Photograph: {{ $hall->name }}]</div>
                    @endif
                </div>

                @if ($hall->images->isNotEmpty())
                    <div>
                        <h3 style="font-size: 15px; color: var(--champagne-200); margin-bottom: 10px;">Room Gallery (Click thumbnail to view)</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 8px;">
                            @foreach ($hall->images as $index => $image)
                                <button type="button" onclick="swapHeroImage('{{ route('media.public', $image->path) }}', '{{ addslashes($image->alt_text ?: $hall->name) }}', this)"
                                        style="border-radius: 4px; overflow: hidden; display: block; border: 2px solid {{ $index === 0 ? 'var(--gold-500)' : 'rgba(201,169,97,0.3)' }}; background: transparent; padding: 0; cursor: pointer; transition: border-color 0.2s;"
                                        class="room-thumbnail-btn">
                                    <img src="{{ route('media.public', $image->path) }}" alt="{{ $image->alt_text ?: $hall->name }}" width="300" height="200" loading="lazy"
                                         style="width: 100%; height: 50px; object-fit: cover; display: block;">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="card-dark">
                    <dl style="display: grid; gap: 10px; font-size: 15px; margin: 0;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Capacity</dt>
                            <dd style="margin: 0;">{{ $hall->capacity }} guests</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Minimum</dt>
                            <dd style="margin: 0;">{{ $hall->min_hours }} hours</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Changeover</dt>
                            <dd style="margin: 0;">{{ $hall->buffer_minutes }} min buffer</dd>
                        </div>
                    </dl>
                </div>

                @if ($memberRate)
                    <p style="font-size: 15px; color: var(--mist-300); margin: 0;">
                        Your member rate: <strong style="color: var(--champagne-200);">£{{ number_format($memberRate / 100, 0) }}</strong> per hour
                    </p>
                @endif

                <ul style="margin: 0; padding: 0 0 0 1.1rem; color: var(--mist-300); font-size: 15px;">
                    <li style="margin-bottom: 6px;">Booking slot opens 30 minutes before your start time</li>
                    <li style="margin-bottom: 6px;">Full refund if cancelled 7+ days before start</li>
                    <li style="margin-bottom: 6px;">50% refund if cancelled 48 hours to 7 days before</li>
                    <li>No refund if cancelled within 48 hours</li>
                </ul>
            </div>

            <aside style="flex: 1 1 420px;">
                <div class="card-dark">
                    <h2 style="font-size: 26px; margin-bottom: 20px;">Booking details</h2>

                    @if ($errors->any())
                        <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif

                    @guest
                        <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            <p style="margin: 0 0 6px;"><strong>Add to cart as guest</strong></p>
                            <p style="margin: 0; font-size: 14px; color: var(--mist-300);">You can add this room to your cart now. You will be asked to sign in or register when you proceed to checkout.</p>
                        </div>
                    @endguest

                    <form method="POST" action="{{ route('cart.add.room', $hall) }}" style="display: grid; gap: 18px;">
                        @csrf
                        <input type="hidden" id="start_time" name="start_time" value="{{ old('start_time') }}">
                        <input type="hidden" id="end_time" name="end_time" value="{{ old('end_time') }}">

                        <div>
                            <label class="field-label" for="booking_date">Select Date</label>
                            <input class="field-dark" id="booking_date" name="booking_date" type="date" required value="{{ old('booking_date') }}" style="color-scheme: dark;">
                        </div>

                        <div>
                            <label class="field-label">Available Time Slot(s)</label>
                            <div style="position: relative;">
                                <div id="booking-time-display" style="width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.06); border: 1px solid rgba(201,169,97,0.4); border-radius: 4px; color: var(--ivory-100); cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                    <span>Select date first to view slots</span>
                                    <span>▼</span>
                                </div>
                                <div id="booking-time-options" style="position: absolute; z-index: 10; width: 100%; margin-top: 4px; background: #111b2b; border: 1px solid rgba(201,169,97,0.4); border-radius: 4px; max-height: 220px; overflow-y: auto; display: none; padding: 8px;">
                                    <p style="color: var(--mist-300); font-size: 13px; padding: 6px;">Please choose a date above.</p>
                                </div>
                            </div>
                            <div id="booking-time-message" style="margin-top: 6px; font-size: 13px; color: #F0A7B2;"></div>
                        </div>

                        <div>
                            <label class="field-label" for="guests">Number of guests</label>
                            <input class="field-dark" id="guests" name="guests" type="number" min="1" max="{{ $hall->capacity }}" value="{{ old('guests', 1) }}" required>
                            @error('guests')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="notes">Notes <span style="font-weight: 400; color: var(--mist-300);">(optional)</span></label>
                            <textarea class="field-dark" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <label style="display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: var(--mist-300);">
                            <input type="checkbox" name="rules_ack" value="1" required @checked(old('rules_ack')) style="margin-top: 4px; width: 20px; height: 20px;">
                            <span>I accept the <a href="{{ route('legal', 'club-rules') }}" class="link-gold">club rules</a> and the cancellation policy above.</span>
                        </label>
                        @error('rules_ack')<p class="error-text">{{ $message }}</p>@enderror

                        <button type="submit" class="btn btn-gold" style="width: 100%;">Add to cart</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const datePicker = document.getElementById('booking_date');
    const displayBox = document.getElementById('booking-time-display');
    const optionsBox = document.getElementById('booking-time-options');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const msgBox = document.getElementById('booking-time-message');

    if (datePicker) {
        const today = new Date().toISOString().split('T')[0];
        datePicker.setAttribute('min', today);
        if (!datePicker.value) {
            datePicker.value = today;
            fetchSlots(today);
        }

        datePicker.addEventListener('click', function() {
            if (typeof this.showPicker === 'function') {
                try {
                    this.showPicker();
                } catch (e) {}
            }
        });

        datePicker.addEventListener('change', function() {
            const selectedDate = this.value;
            if (selectedDate) {
                fetchSlots(selectedDate);
            } else {
                optionsBox.innerHTML = '<p style="color: var(--mist-300); font-size: 13px; padding: 6px;">Please choose a date above.</p>';
                displayBox.querySelector('span').textContent = 'Select date first to view slots';
                optionsBox.style.display = 'none';
            }
        });
    }

    if (displayBox && optionsBox) {
        displayBox.addEventListener('click', function() {
            if (optionsBox.children.length > 0) {
                optionsBox.style.display = optionsBox.style.display === 'block' ? 'none' : 'block';
            } else {
                msgBox.textContent = 'Please select a date first.';
            }
        });

        document.addEventListener('click', function(e) {
            if (!displayBox.contains(e.target) && !optionsBox.contains(e.target)) {
                optionsBox.style.display = 'none';
            }
        });
    }

    function fetchSlots(dateStr) {
        optionsBox.innerHTML = '<p style="color: var(--champagne-200); font-size: 13px; padding: 6px;">Loading available slots...</p>';
        optionsBox.style.display = 'block';

        fetch(`{{ route('time.slots') }}?hall_id={{ $hall->id }}&booking_date=${dateStr}`)
            .then(res => res.json())
            .then(data => {
                optionsBox.innerHTML = '';
                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach((slotText, index) => {
                        const label = document.createElement('label');
                        label.style.display = 'flex';
                        label.style.alignItems = 'center';
                        label.style.gap = '8px';
                        label.style.padding = '6px 8px';
                        label.style.cursor = 'pointer';
                        label.style.color = 'var(--ivory-100)';
                        label.style.fontSize = '14px';

                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'slot_selection[]';
                        checkbox.value = slotText;
                        checkbox.style.width = '16px';
                        checkbox.style.height = '16px';

                        checkbox.addEventListener('change', updateSelectedTimes);

                        label.appendChild(checkbox);
                        label.appendChild(document.createTextNode(slotText));
                        optionsBox.appendChild(label);
                    });
                } else {
                    optionsBox.innerHTML = '<p style="color: #F0A7B2; font-size: 13px; padding: 6px;">No available slots for this date.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                optionsBox.innerHTML = '<p style="color: #F0A7B2; font-size: 13px; padding: 6px;">Error loading slots. Try again.</p>';
            });
    }

    function updateSelectedTimes() {
        const checkboxes = optionsBox.querySelectorAll('input[type="checkbox"]:checked');
        const displaySpan = displayBox.querySelector('span');

        if (checkboxes.length === 0) {
            displaySpan.textContent = 'Select Time Slot(s)';
            startTimeInput.value = '';
            endTimeInput.value = '';
            return;
        }

        const dateStr = datePicker.value;
        const selectedTexts = Array.from(checkboxes).map(cb => cb.value);
        displaySpan.textContent = selectedTexts.length === 1 ? selectedTexts[0] : selectedTexts.length + ' slots selected';

        // Parse first start and last end
        const firstSlotParts = selectedTexts[0].split(' - ');
        const lastSlotParts = selectedTexts[selectedTexts.length - 1].split(' - ');

        if (firstSlotParts.length === 2 && lastSlotParts.length === 2) {
            startTimeInput.value = `${dateStr} ${convertTo24Hour(firstSlotParts[0])}`;
            endTimeInput.value = `${dateStr} ${convertTo24Hour(lastSlotParts[1])}`;
        }
    }

    function convertTo24Hour(time12h) {
        const [time, modifier] = time12h.split(' ');
        let [hours, minutes] = time.split(':');
        if (hours === '12') {
            hours = modifier === 'AM' ? '00' : '12';
        } else if (modifier === 'PM') {
            hours = String(parseInt(hours, 10) + 12);
        }
        return `${hours.padStart(2, '0')}:${minutes}:00`;
    }
});

function swapHeroImage(src, alt, btn) {
    const mainImg = document.getElementById('main-hero-img');
    if (mainImg) {
        mainImg.src = src;
        mainImg.alt = alt;
    }
    document.querySelectorAll('.room-thumbnail-btn').forEach(b => {
        b.style.borderColor = 'rgba(201,169,97,0.3)';
    });
    btn.style.borderColor = 'var(--gold-500)';
}
</script>
@endsection
