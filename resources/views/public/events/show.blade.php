@extends('layouts.public')

@php
    $pricing = app(\App\Services\PricingService::class);
    $memberPrice = $pricing->applyDiscount((int) $event->price_per_ticket, (string) $event->member_discount_type, (int) $event->member_discount_value);

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $event->name,
        'description' => $event->description,
        'startDate' => $event->starts_at->toIso8601String(),
        'endDate' => $event->ends_at->toIso8601String(),
        'eventStatus' => $event->cancelled_at ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'location' => [
            '@type' => 'Place',
            'name' => $event->venue ?: 'Charlton Exclusive Club',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Unit 6, Block 3 Woolwich, Dockyard Industrial Estate, Woolwich Church St',
                'addressLocality' => 'Charlton, London',
                'postalCode' => 'SE18 5PQ',
                'addressCountry' => 'GB',
            ],
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($event->price_per_ticket / 100, 2, '.', ''),
            'priceCurrency' => 'GBP',
            'url' => route('events.show', $event->slug),
            'availability' => $event->cancelled_at ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
        ],
    ];
@endphp

@section('content')
<section style="padding-block: clamp(40px, 5vw, 72px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('events.index') }}">Events</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">{{ $event->name }}</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 500px;">
                <div style="height: 380px; border-radius: 4px; background: var(--navy-800); border: 1px solid rgba(201,169,97,.3); display: flex; align-items: center; justify-content: center; color: var(--mist-300);">
                    [Event image]
                </div>

                <div class="prose-club" style="margin-top: 32px; color: var(--ivory-100);">
                    {!! nl2br(e($event->description)) !!}
                </div>
            </div>

            <aside style="flex: 1 1 340px;">
                <div class="card-dark">
                    <p class="eyebrow" style="font-size: 12px; color: var(--gold-500);">
                        <time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('l j F Y') }}</time>
                    </p>
                    <h1 style="font-size: clamp(30px, 3.6vw, 42px); margin: 12px 0 18px;">{{ $event->name }}</h1>

                    <dl style="display: grid; gap: 10px; font-size: 16px; margin-bottom: 22px;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Time</dt>
                            <dd style="margin: 0;">{{ $event->starts_at->format('H:i') }}–{{ $event->ends_at->format('H:i') }}</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(201,169,97,.25); padding-bottom: 8px;">
                            <dt style="color: var(--mist-300);">Venue</dt>
                            <dd style="margin: 0;">{{ $event->venue ?: 'The club' }}</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Tickets per booking</dt>
                            <dd style="margin: 0;">Up to {{ $event->tickets_per_booking }}</dd>
                        </div>
                    </dl>

                    <p style="font-size: 26px; font-family: var(--font-display); color: var(--champagne-200); margin-bottom: 4px;">
                        £{{ number_format($event->price_per_ticket / 100, 2) }}
                    </p>
                    <p style="font-size: 15px; color: var(--mist-300); margin-bottom: 22px;">
                        Members £{{ number_format($memberPrice / 100, 2) }} · prices include VAT
                    </p>

                    @if ($event->cancelled_at)
                        <p class="badge badge-red">Cancelled</p>
                        <p style="margin-top: 16px; color: var(--mist-300);">This event has been cancelled. Ticket holders will be refunded in full.</p>
                    @elseif ($event->starts_at->isPast())
                        <p class="badge badge-neutral">Past event</p>
                    @else
                        <a href="{{ route('book.event', $event) }}" class="btn btn-gold" style="width: 100%;">Request a place</a>
                        <p style="font-size: 14px; color: var(--mist-300); margin-top: 12px;">The club confirms your place by email.</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</section>

@if ($upcoming->isNotEmpty())
    <section style="background: var(--navy-800); padding-block: clamp(48px, 6vw, 80px);">
        <div class="container-x">
            <h2 style="font-size: 30px; margin-bottom: 24px;">Also coming up</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                @foreach ($upcoming as $other)
                    <a href="{{ route('events.show', $other->slug) }}" class="card-dark" style="display: block;">
                        <p class="eyebrow" style="font-size: 12px; color: var(--gold-500);">{{ $other->starts_at->format('j M Y') }}</p>
                        <h3 style="font-size: 21px; margin-top: 8px;">{{ $other->name }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection