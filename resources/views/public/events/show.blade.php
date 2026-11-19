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
<section style="padding-block: clamp(24px, 4vw, 48px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('events.index') }}">Events</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">{{ $event->name }}</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 500px;">
                <div style="border-radius: 4px; overflow: hidden; background: var(--navy-800); border: 1px solid rgba(201,169,97,.3);">
                    @if ($event->og_image)
                        <img src="{{ route('media.public', $event->og_image) }}" alt="{{ $event->name }}" width="800" height="500" style="width: 100%; height: 380px; object-fit: cover; display: block;">
                    @else
                        <div style="height: 380px; display: flex; align-items: center; justify-content: center; color: var(--mist-300);">
                            [Event image]
                        </div>
                    @endif
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
                    @elseif ($event->members_only && (auth()->guest() || ! auth()->user()->hasMemberAccess()))
                        <div style="background: rgba(201,169,97,.12); border: 1px solid rgba(201,169,97,.3); padding: 16px; border-radius: 4px; text-align: center; margin-top: 12px;">
                            <p style="color: var(--champagne-200); font-weight: 600; margin: 0 0 6px;">Members-Only Event</p>
                            <p style="color: var(--mist-300); font-size: 14px; margin: 0 0 16px;">This event is exclusive to club members. Please log in or register to continue.</p>
                            @guest
                                <div style="display: flex; gap: 10px; justify-content: center;">
                                    <a href="{{ route('login') }}" class="btn btn-gold btn-sm">Sign in</a>
                                    <a href="{{ route('register') }}" class="btn btn-line btn-sm">Register</a>
                                </div>
                            @else
                                <a href="{{ route('membership.index') }}" class="btn btn-gold btn-sm">View Membership</a>
                            @endguest
                        </div>
                    @else
                        @if ($errors->any())
                            <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 10px 12px; border-radius: 4px; margin-bottom: 14px; font-size: 13px;">
                                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('cart.add.event', $event) }}" style="display: grid; gap: 14px;">
                            @csrf
                            @guest
                                <div style="font-size: 13px; color: var(--champagne-200); background: rgba(201,169,97,.1); padding: 8px 10px; border-radius: 4px; border: 1px solid rgba(201,169,97,.3);">
                                    Add to cart as guest. Sign in at checkout.
                                </div>
                            @endguest
                            <div>
                                <label class="field-label" for="tickets" style="color: var(--ivory-100);">Number of tickets</label>
                                <input class="field-dark" id="tickets" name="tickets" type="number" min="1" max="{{ $event->tickets_per_booking }}" value="{{ old('tickets', 1) }}" required>
                            </div>
                            <button type="submit" class="btn btn-gold" style="width: 100%;">Add to cart</button>
                            <p style="font-size: 13px; color: var(--mist-300); text-align: center; margin: 0;">Secure checkout via Stripe.</p>
                        </form>
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