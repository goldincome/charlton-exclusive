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
<section style="padding-block: clamp(40px, 5vw, 72px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('rooms.index') }}">Meeting rooms</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">{{ $hall->name }}</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 480px;">
                <div style="border-radius: 4px; overflow: hidden; background: #E9E1CB;">
                    @if ($hall->heroImage())
                        <img src="{{ route('media.public', $hall->heroImage()->path) }}" alt="{{ $hall->heroImage()->alt_text ?: $hall->name }}"
                             width="1200" height="800" fetchpriority="high" style="width: 100%; height: 420px; object-fit: cover;">
                    @else
                        <div class="placeholder" style="height: 420px; background: #E9E1CB; color: var(--slate-600); border: 0;">[Photograph: {{ $hall->name }}]</div>
                    @endif
                </div>

                @if ($hall->images->count() > 1)
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-top: 10px;">
                        @foreach ($hall->images->skip(1) as $image)
                            <a href="{{ route('media.public', $image->path) }}" data-lightbox="{{ route('media.public', $image->path) }}" data-alt="{{ $image->alt_text }}">
                                <img src="{{ route('media.public', $image->path) }}" alt="{{ $image->alt_text ?: $hall->name }}" width="300" height="200" loading="lazy"
                                     style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px;">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div style="flex: 1 1 340px;">
                <p class="eyebrow" style="color: var(--gold-500);">{{ $hall->area ?: 'Meeting room' }}</p>
                <h1 style="font-size: clamp(34px, 4.4vw, 52px); margin: 14px 0 18px;">{{ $hall->name }}</h1>
                <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 24px;">{{ $hall->description }}</p>

                <dl style="display: grid; gap: 12px; margin: 0 0 28px; font-size: 16px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 10px;">
                        <dt style="color: var(--mist-300);">Capacity</dt>
                        <dd style="margin: 0;">Up to {{ $hall->capacity }} guests{{ $hall->capacity_seated ? ' · '.$hall->capacity_seated.' seated' : '' }}</dd>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 10px;">
                        <dt style="color: var(--mist-300);">Minimum booking</dt>
                        <dd style="margin: 0;">{{ $hall->min_hours }} hours</dd>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <dt style="color: var(--mist-300);">Changeover buffer</dt>
                        <dd style="margin: 0;">{{ $hall->buffer_minutes }} minutes</dd>
                    </div>
                </dl>

                <a href="{{ route('book.room', $hall) }}" class="btn btn-gold" style="width: 100%;">Request a booking</a>
                <p style="font-size: 14px; color: var(--mist-300); margin-top: 12px;">No payment is taken until the club approves your request.</p>
            </div>
        </div>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; gap: 56px;">
        <div style="flex: 1 1 360px;">
            <h2 style="font-size: 30px; margin-bottom: 16px;">Rates</h2>
            <table class="rate-table">
                <caption class="sr-only">Rates for {{ $hall->name }}</caption>
                <thead>
                    <tr><th scope="col">Day</th><th scope="col">Standard</th><th scope="col">Member</th></tr>
                </thead>
                <tbody>
                    @foreach ($days as $key => $label)
                        @php $rate = $hall->rates->firstWhere('day_type', $key); @endphp
                        @if ($rate)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>£{{ number_format($rate->standard_price_per_hour / 100, 2) }} / hr</td>
                                <td style="color: var(--claret-700); font-weight: 600;">£{{ number_format($rate->memberPricePerHour() / 100, 2) }} / hr</td>
                            </tr>
                        @endif
                    @endforeach
                    @if ($hall->rates->isEmpty())
                        <tr><td colspan="3">Rates for this room are being confirmed.</td></tr>
                    @endif
                </tbody>
            </table>
            <p style="font-size: 14px; color: var(--slate-600); margin-top: 16px;">Prices include VAT at 20%.</p>
        </div>

        <div style="flex: 1 1 320px;">
            <h2 style="font-size: 30px; margin-bottom: 16px;">In the room</h2>
            @if ($hall->features->isNotEmpty())
                <ul style="margin: 0 0 24px 1.1rem; padding: 0; color: var(--slate-600);">
                    @foreach ($hall->features as $feature)
                        <li style="margin-bottom: 8px;">{{ $feature->name }}</li>
                    @endforeach
                </ul>
            @else
                <p style="color: var(--slate-600);">Room features will be listed shortly.</p>
            @endif

            @if ($hall->hours->isNotEmpty())
                <h3 style="font-size: 22px; margin: 24px 0 12px;">Opening hours</h3>
                <ul style="margin: 0 0 24px 1.1rem; padding: 0; color: var(--slate-600); font-size: 15px;">
                    @foreach ($hall->hours->sortBy('weekday') as $hour)
                        <li style="margin-bottom: 6px;">{{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$hour->weekday] }}:
                            {{ \Illuminate\Support\Carbon::parse($hour->opens_at)->format('H:i') }}–{{ \Illuminate\Support\Carbon::parse($hour->closes_at)->format('H:i') }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>

@if ($others->isNotEmpty())
    <section style="padding-block: clamp(48px, 6vw, 80px);">
        <div class="container-x">
            <h2 style="font-size: 30px; margin-bottom: 24px;">Other rooms</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                @foreach ($others as $other)
                    <a href="{{ route('rooms.show', $other->slug) }}" class="card-dark" style="display: block;">
                        <h3 style="font-size: 22px;">{{ $other->name }}</h3>
                        <p style="color: var(--mist-300); margin-top: 8px;">Up to {{ $other->capacity }} guests</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection