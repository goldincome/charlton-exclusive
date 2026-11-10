@extends('layouts.public')

@php
    $days = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday', 'BankHoliday' => 'Bank holiday'];
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'MeetingRoom',
        'name' => $hall->name,
        'description' => $hall->description,
        'maximumAttendeeCapacity' => $hall->capacity,
        'url' => route('rooms.show', $hall->slug),
    ];
@endphp

@section('content')
<section style="padding-block: clamp(48px, 6vw, 96px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 24px;">
            <a href="{{ route('home') }}" class="link-gold">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('rooms.index') }}" class="link-gold">Meeting rooms</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">{{ $hall->name }}</span>
        </nav>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: start;">
            <!-- Left Column: Photography & Room Details -->
            <div style="display: grid; gap: 32px;">
                <div style="border-radius: 6px; overflow: hidden; background: #E9E1CB; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    @if ($hall->heroImage())
                        <img id="main-hero-img" src="{{ route('media.public', $hall->heroImage()->path) }}" alt="{{ $hall->heroImage()->alt_text ?: $hall->name }}"
                             width="1200" height="800" fetchpriority="high" style="width: 100%; height: 420px; object-fit: cover; display: block;">
                        <div id="main-hero-placeholder" class="placeholder" style="height: 420px; background: #E9E1CB; color: var(--slate-600); border: 0; display: none; align-items: center; justify-content: center;">[Photograph: {{ $hall->name }}]</div>
                    @else
                        <img id="main-hero-img" src="" alt="{{ $hall->name }}" style="width: 100%; height: 420px; object-fit: cover; display: none;">
                        <div id="main-hero-placeholder" class="placeholder" style="height: 420px; background: #E9E1CB; color: var(--slate-600); border: 0; display: flex; align-items: center; justify-content: center;">[Photograph: {{ $hall->name }}]</div>
                    @endif
                </div>

                @if ($hall->images->isNotEmpty())
                    <div>
                        <h3 style="font-size: 16px; color: var(--champagne-200); margin-bottom: 12px;">Room Gallery (Click thumbnail to view)</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(75px, 1fr)); gap: 8px;">
                            @foreach ($hall->images as $index => $image)
                                <button type="button" onclick="swapHeroImage('{{ route('media.public', $image->path) }}', '{{ addslashes($image->alt_text ?: $hall->name) }}', this)"
                                        style="border-radius: 4px; overflow: hidden; display: block; border: 2px solid {{ $index === 0 ? 'var(--gold-500)' : 'rgba(201,169,97,0.3)' }}; background: transparent; padding: 0; cursor: pointer; transition: border-color 0.2s;"
                                        class="room-thumbnail-btn" data-img-src="{{ route('media.public', $image->path) }}">
                                    <img src="{{ route('media.public', $image->path) }}" alt="{{ $image->alt_text ?: $hall->name }}" width="300" height="200" loading="lazy"
                                         style="width: 100%; height: 60px; object-fit: cover; display: block;">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Rates & Features Card -->
                <div class="card-dark" style="padding: 32px; display: grid; gap: 24px;">
                    <div>
                        <h2 style="font-size: 24px; margin-bottom: 14px; color: var(--champagne-200);">Room Rates & Hours</h2>
                        <table style="width: 100%; font-size: 15px; border-collapse: collapse; color: var(--mist-300);">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(201,169,97,0.3); text-align: left;">
                                    <th style="padding: 8px 0; color: var(--ivory-100);">Day</th>
                                    <th style="padding: 8px 0; color: var(--ivory-100);">Standard</th>
                                    <th style="padding: 8px 0; color: var(--ivory-100);">Member</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($days as $key => $label)
                                    @php $rate = $hall->rates->firstWhere('day_type', $key); @endphp
                                    @if ($rate)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                            <td style="padding: 10px 0;">{{ $label }}</td>
                                            <td style="padding: 10px 0;">£{{ number_format($rate->standard_price_per_hour / 100, 2) }} / hr</td>
                                            <td style="padding: 10px 0; color: var(--champagne-200); font-weight: 600;">£{{ number_format($rate->memberPricePerHour() / 100, 2) }} / hr</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <p style="font-size: 13px; color: var(--mist-300); margin-top: 10px;">All prices include VAT at 20%. Member rates apply to annual subscribers.</p>
                    </div>

                    @if ($hall->features->isNotEmpty())
                        <div>
                            <h3 style="font-size: 20px; margin-bottom: 10px; color: var(--champagne-200);">Amenities & Features</h3>
                            <ul style="margin: 0; padding-left: 18px; color: var(--mist-300); font-size: 15px; display: grid; gap: 6px;">
                                @foreach ($hall->features as $feature)
                                    <li>{{ $feature->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($hall->hours->isNotEmpty())
                        <div>
                            <h3 style="font-size: 20px; margin-bottom: 10px; color: var(--champagne-200);">Opening Hours</h3>
                            <ul style="margin: 0; padding-left: 18px; color: var(--mist-300); font-size: 15px; display: grid; gap: 4px;">
                                @foreach ($hall->hours->sortBy('weekday') as $hour)
                                    <li>{{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$hour->weekday] }}:
                                        {{ \Illuminate\Support\Carbon::parse($hour->opens_at)->format('H:i') }}–{{ \Illuminate\Support\Carbon::parse($hour->closes_at)->format('H:i') }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Room Overview & Booking Request Sidebar -->
            <div style="display: grid; gap: 24px; position: sticky; top: 24px;">
                <div class="card-dark" style="padding: 36px; display: grid; gap: 24px; border: 1px solid rgba(201,169,97,0.3);">
                    <div>
                        <span class="eyebrow" style="color: var(--gold-500); display: inline-block; margin-bottom: 8px;">{{ $hall->area ?: 'Meeting room' }}</span>
                        <h1 style="font-size: clamp(32px, 4vw, 44px); margin: 0 0 14px; line-height: 1.15;">{{ $hall->name }}</h1>
                        <p style="font-size: 16px; color: var(--mist-300); line-height: 1.6; margin: 0;">{{ $hall->description }}</p>
                    </div>

                    <dl style="display: grid; gap: 12px; margin: 0; padding-block: 16px; border-block: 1px solid rgba(201,169,97,0.2); font-size: 15px;">
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Capacity</dt>
                            <dd style="margin: 0; color: var(--ivory-100); font-weight: 500;">Up to {{ $hall->capacity }} guests{{ $hall->capacity_seated ? ' · '.$hall->capacity_seated.' seated' : '' }}</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Minimum booking</dt>
                            <dd style="margin: 0; color: var(--ivory-100); font-weight: 500;">{{ $hall->min_hours }} hours</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Changeover buffer</dt>
                            <dd style="margin: 0; color: var(--ivory-100); font-weight: 500;">{{ $hall->buffer_minutes }} minutes</dd>
                        </div>
                    </dl>

                    @if (isset($memberRate) && $memberRate)
                        <div style="background: rgba(201,169,97,0.08); padding: 12px 16px; border-radius: 4px; border-left: 3px solid var(--gold-500);">
                            <p style="font-size: 14px; color: var(--mist-300); margin: 0;">
                                Your member rate: <strong style="color: var(--champagne-200);">£{{ number_format($memberRate / 100, 0) }}</strong> / hour
                            </p>
                        </div>
                    @endif

                    <!-- Booking Form -->
                    <div style="display: grid; gap: 18px; border-top: 1px solid rgba(201,169,97,0.2); padding-top: 24px;">
                        <h2 style="font-size: 22px; margin: 0; color: var(--champagne-200);">Request a booking for {{ $hall->name }}</h2>
                        <p style="font-size: 14px; color: var(--mist-300); margin: 0;">
                            Select date, time slots & guests. Approved requests receive a 72-hour payment window.
                        </p>

                        @if ($errors->any())
                            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 12px 14px; border-radius: 4px; font-size: 13px;">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        @guest
                            <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 12px 14px; border-radius: 4px; font-size: 13px;">
                                <p style="margin: 0 0 6px;"><strong>Sign in required to submit</strong></p>
                                <p style="margin: 0 0 10px;">Select your times below, then sign in to send your request.</p>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('login') }}" class="btn btn-gold btn-sm">Sign in</a>
                                    <a href="{{ route('register') }}" class="btn btn-line btn-sm">Register</a>
                                </div>
                            </div>
                        @endguest

                        <form method="POST" action="{{ route('account.book.room.store', $hall) }}" style="display: grid; gap: 16px;">
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
                                    <div id="booking-time-display" style="width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.06); border: 1px solid rgba(201,169,97,0.4); border-radius: 4px; color: var(--ivory-100); cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                                        <span>Select date first to view slots</span>
                                        <span>▼</span>
                                    </div>
                                    <div id="booking-time-options" style="position: absolute; z-index: 10; width: 100%; margin-top: 4px; background: #111b2b; border: 1px solid rgba(201,169,97,0.4); border-radius: 4px; max-height: 220px; overflow-y: auto; display: none; padding: 8px;">
                                        <p style="color: var(--mist-300); font-size: 13px; padding: 6px;">Please choose a date above.</p>
                                    </div>
                                </div>
                                <div id="booking-time-message" style="margin-top: 4px; font-size: 13px; color: #F0A7B2;"></div>
                            </div>

                            <div>
                                <label class="field-label" for="guests">Number of guests</label>
                                <input class="field-dark" id="guests" name="guests" type="number" min="1" max="{{ $hall->capacity }}" value="{{ old('guests', 1) }}" required>
                                @error('guests')<p class="error-text">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="field-label" for="notes">Notes <span style="font-weight: 400; color: var(--mist-300);">(optional)</span></label>
                                <textarea class="field-dark" id="notes" name="notes" rows="2" placeholder="Catering requirements or setup notes">{{ old('notes') }}</textarea>
                                @error('notes')<p class="error-text">{{ $message }}</p>@enderror
                            </div>

                            <label style="display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--mist-300);">
                                <input type="checkbox" name="rules_ack" value="1" required @checked(old('rules_ack')) style="margin-top: 3px; width: 18px; height: 18px; accent-color: var(--gold-500);">
                                <span>I accept the <a href="{{ route('legal', 'club-rules') }}" class="link-gold">club rules</a> and cancellation policy.</span>
                            </label>
                            @error('rules_ack')<p class="error-text">{{ $message }}</p>@enderror

                            <button type="submit" class="btn btn-gold" style="width: 100%; padding-block: 14px; font-weight: 600; letter-spacing: 0.05em;">Send booking request</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($others->isNotEmpty())
    <section style="padding-block: clamp(48px, 6vw, 80px); background: rgba(0,0,0,0.15); border-top: 1px solid rgba(201,169,97,0.15);">
        <div class="container-x">
            <h2 style="font-size: 28px; margin-bottom: 24px; color: var(--champagne-200);">Other meeting rooms</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                @foreach ($others as $other)
                    <a href="{{ route('rooms.show', $other->slug) }}" class="card-dark" style="display: block; padding: 24px; border: 1px solid rgba(201,169,97,0.2);">
                        <h3 style="font-size: 20px; margin-bottom: 8px; color: var(--ivory-100);">{{ $other->name }}</h3>
                        <p style="color: var(--mist-300); font-size: 14px; margin: 0 0 16px;">Up to {{ $other->capacity }} guests</p>
                        <span style="font-size: 14px; color: var(--gold-500); font-weight: 500;">View room & book →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<script>
function swapHeroImage(src, alt, btn) {
    const heroImg = document.getElementById('main-hero-img');
    const placeholder = document.getElementById('main-hero-placeholder');
    if (heroImg) {
        heroImg.src = src;
        heroImg.alt = alt;
        heroImg.style.display = 'block';
    }
    if (placeholder) {
        placeholder.style.display = 'none';
    }
    document.querySelectorAll('.room-thumbnail-btn').forEach(b => {
        b.style.borderColor = 'rgba(201,169,97,0.3)';
    });
    if (btn) {
        btn.style.borderColor = 'var(--gold-500)';
    }
}

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
                        checkbox.style.accentColor = 'var(--gold-500)';

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
</script>
@endsection
