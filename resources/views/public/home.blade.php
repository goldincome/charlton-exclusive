@extends('layouts.public')

@php
    use App\Models\AppConfig;

    $pricing = app(\App\Services\PricingService::class);

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => 'Charlton Exclusive Club',
        'image' => asset('images/og-default.jpg'),
        'url' => route('home'),
        'telephone' => '+44 2032 474747',
        'priceRange' => '£££',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Unit 6, Block 3 Woolwich, Dockyard Industrial Estate, Woolwich Church St',
            'addressLocality' => 'Charlton, London',
            'postalCode' => 'SE18 5PQ',
            'addressCountry' => 'GB',
        ],
        'areaServed' => ['Charlton', 'Woolwich', 'Greenwich', 'Blackheath', 'Westcombe Park', 'Plumstead', 'Kidbrooke', 'Eltham', 'Thamesmead', 'Abbey Wood', 'Lewisham', 'Deptford'],
        'openingHoursSpecification' => [
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '07:00', 'closes' => '23:00'],
            ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Saturday', 'Sunday'], 'opens' => '08:00', 'closes' => '22:00'],
        ],
    ];
@endphp

@section('content')

{{-- 1. Hero --}}
<section style="position: relative; overflow: hidden; padding: clamp(64px, 9vw, 120px) 0;">
    <svg aria-hidden="true" viewBox="0 0 1440 700" preserveAspectRatio="none"
         style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: .16;" fill="none" stroke="#C9A961" stroke-width="1.2">
        <path d="M-20 420 C 240 340, 420 520, 700 440 S 1180 330, 1460 430"></path>
        <path d="M-20 460 C 240 380, 420 560, 700 480 S 1180 370, 1460 470"></path>
        <path d="M-20 500 C 240 420, 420 600, 700 520 S 1180 410, 1460 510"></path>
        <path d="M-20 540 C 240 460, 420 640, 700 560 S 1180 450, 1460 550"></path>
        <path d="M-20 580 C 240 500, 420 680, 700 600 S 1180 490, 1460 590"></path>
    </svg>

    <div class="container-x" style="position: relative; display: flex; flex-wrap: wrap; align-items: center; gap: 64px;">
        <div style="flex: 1 1 480px;">
            <p class="eyebrow" style="color: var(--gold-500); margin-bottom: 24px;">Charlton · South East London</p>
            <h1 style="font-size: clamp(40px, 5.4vw, 68px); color: var(--ivory-100);" class="text-balance">
                A private address in <em style="color: var(--champagne-200);">South East London.</em>
            </h1>
            <p style="font-size: 19px; color: var(--mist-300); max-width: 560px; margin: 28px 0 40px;">
                Dine, gather and belong. Charlton Exclusive Club is a members’ club with private dining, sports sections, events and elegant meeting rooms, open to guests by request.
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                <a href="{{ route('account.applications.start') }}" class="btn btn-gold">Request an invitation</a>
                <a href="{{ route('rooms.index') }}" class="btn btn-line">Hire a room</a>
            </div>
            <p style="font-size: 14px; color: var(--mist-300); margin-top: 28px;">Members enjoy preferred rates on every booking.</p>
        </div>

        <div style="flex: 0 1 420px; width: 100%; max-width: 420px;">
            <div class="placeholder" style="min-height: 480px; background: var(--navy-800);">
                @if ($halls->first()?->heroImage())
                    <img src="{{ route('media.public', $halls->first()->heroImage()->path) }}"
                         alt="{{ $halls->first()->heroImage()->alt_text ?: $halls->first()->name }}"
                         width="840" height="960" loading="eager" fetchpriority="high"
                         style="width: 100%; height: 480px; object-fit: cover; border-radius: 4px;">
                @else
                    <span>[Photograph: Century Dining Room, evening]</span>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- 2. Three pillars --}}
<section id="dining" style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(64px, 8vw, 96px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--claret-700);">Why belong</p>
        <h2 style="font-size: clamp(32px, 4vw, 44px); margin: 16px 0 48px; max-width: 640px;">
            Three ways to make the club <em>your own</em>
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
            <div class="card-light">
                <svg aria-hidden="true" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#7A1F2E" stroke-width="1.5">
                    <circle cx="12" cy="12" r="8"></circle><circle cx="12" cy="12" r="3"></circle>
                </svg>
                <h3 style="font-size: 28px; margin: 20px 0 12px;">Dine</h3>
                <p style="margin-bottom: 20px; color: var(--slate-600);">
                    The Century Dining Room for personal and party dining. Hire a meeting room and take {{ \App\Models\AppConfig::int('hall_food_discount_value', 20) }}% off food, with beverages excluded.
                </p>
                <a href="{{ route('dining') }}" class="link-claret">Explore dining</a>
            </div>

            <div class="card-light">
                <svg aria-hidden="true" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#7A1F2E" stroke-width="1.5">
                    <circle cx="9" cy="12" r="6"></circle><circle cx="15" cy="12" r="6"></circle>
                </svg>
                <h3 style="font-size: 28px; margin: 20px 0 12px;">Gather</h3>
                <p style="margin-bottom: 20px; color: var(--slate-600);">
                    Events, celebrations and meeting rooms booked online, approved by the club and confirmed after payment.
                </p>
                <a href="{{ route('events.index') }}" class="link-claret">See what’s on</a>
            </div>

            <div class="card-light">
                <svg aria-hidden="true" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#7A1F2E" stroke-width="1.5">
                    <path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"></path>
                </svg>
                <h3 style="font-size: 28px; margin: 20px 0 12px;">Belong</h3>
                <p style="margin-bottom: 20px; color: var(--slate-600);">
                    @if ($sections->isNotEmpty())
                        Join sections such as {{ $sections->take(3)->pluck('name')->join(', ', ' and ') }}, and become part of a community that knows your name.
                    @else
                        Join our sports and social sections, and become part of a community that knows your name.
                    @endif
                </p>
                <a href="{{ route('membership.index') }}" class="link-claret">Discover membership</a>
            </div>
        </div>
    </div>
</section>

{{-- 3. Membership --}}
<section id="membership" style="background: var(--navy-800); padding-block: clamp(64px, 8vw, 96px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; gap: 64px;">
        <div style="flex: 1 1 380px;">
            <p class="eyebrow" style="color: var(--gold-500);">Membership</p>
            <h2 style="font-size: clamp(32px, 4vw, 44px); margin: 16px 0 24px; color: var(--ivory-100);">
                Joined by <em style="color: var(--champagne-200);">introduction</em>
            </h2>
            <p style="color: var(--mist-300); margin-bottom: 32px;">
                Membership is by application — apply online or at the office, then choose monthly or yearly billing with no entrance fee.
            </p>

            <ol style="margin: 0 0 36px; padding: 0; list-style: none; display: grid; gap: 20px;">
                <li style="display: flex; gap: 16px;">
                    <span style="font-family: var(--font-display); font-size: 28px; color: var(--gold-500); min-width: 36px;">1</span>
                    <span><strong style="color: var(--ivory-100);">Apply</strong><br><span style="color: var(--mist-300);">Choose your plan online or download the application form and bring it to the office.</span></span>
                </li>
                <li style="display: flex; gap: 16px;">
                    <span style="font-family: var(--font-display); font-size: 28px; color: var(--gold-500); min-width: 36px;">2</span>
                    <span><strong style="color: var(--ivory-100);">Verify &amp; pay</strong><br><span style="color: var(--mist-300);">Bring an accepted ID to the office; an administrator verifies your documents and emails your payment link.</span></span>
                </li>
                <li style="display: flex; gap: 16px;">
                    <span style="font-family: var(--font-display); font-size: 28px; color: var(--gold-500); min-width: 36px;">3</span>
                    <span><strong style="color: var(--ivory-100);">Join</strong><br><span style="color: var(--mist-300);">Pay monthly or yearly online and start enjoying members-only access and yearly discounts.</span></span>
                </li>
            </ol>

            <a href="{{ route('account.applications.start') }}" class="btn btn-gold">Request an invitation</a>
        </div>

        <div style="flex: 1 1 440px; display: grid; gap: 20px; align-content: start;">
            @forelse ($categories as $category)
                <div class="card-dark" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                    <div>
                        <h3 style="font-size: 26px; color: var(--ivory-100);">{{ $category->name }}</h3>
                        <p style="margin-top: 6px; color: var(--mist-300);">
                            {{ $category->description ?: 'A place at the club on your terms.' }}
                            Entrance fee: none — annual £{{ number_format($category->annual_fee / 100, 0) }}
                            @if ($category->monthlyAvailable()) or monthly £{{ number_format($category->monthly_fee / 100, 0) }} @endif.
                        </p>
                    </div>
                    <a href="{{ route('membership.apply') }}" class="link-gold">Apply</a>
                </div>
            @empty
                @foreach (['Single' => 'For one member.', 'Double' => 'You and your partner.', 'Corporate' => 'For organisations.'] as $name => $copy)
                    <div class="card-dark" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                        <div>
                            <h3 style="font-size: 26px; color: var(--ivory-100);">{{ $name }}</h3>
                            <p style="margin-top: 6px; color: var(--mist-300);">{{ $copy }} Fees published shortly.</p>
                        </div>
                        <a href="{{ route('membership.apply') }}" class="link-gold">Apply</a>
                    </div>
                @endforeach
            @endforelse
        </div>
    </div>
</section>

{{-- 4. Meeting rooms --}}
<section id="rooms" style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(64px, 8vw, 96px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; align-items: center; gap: 64px;">
        <div style="flex: 1 1 400px;">
            <div class="placeholder" style="min-height: 440px; background: #E9E1CB; color: var(--slate-600); border-color: #C9B98A; overflow: hidden;">
                @if ($halls->first()?->heroImage())
                    <img src="{{ route('media.public', $halls->first()->heroImage()->path) }}"
                         alt="{{ $halls->first()->heroImage()->alt_text ?: $halls->first()->name }}"
                         width="800" height="880" loading="lazy"
                         style="width: 100%; height: 440px; object-fit: cover;">
                @else
                    <span>[Photograph: meeting room, set for a board meeting]</span>
                @endif
            </div>
        </div>

        <div style="flex: 1 1 440px;">
            <p class="eyebrow" style="color: var(--claret-700);">Meeting rooms</p>
            <h2 style="font-size: clamp(32px, 4vw, 44px); margin: 16px 0 24px;">Rooms made for <em>serious conversation</em></h2>
            <p style="color: var(--slate-600); margin-bottom: 28px;">
                Choose your room, date and time, and we confirm once approved. Minimum booking three hours. All prices include VAT.
            </p>

            @php
                $exampleHall = $halls->first();
                $weekdayRate = $exampleHall?->rates->firstWhere('day_type', 'Mon');
                $fridayRate = $exampleHall?->rates->firstWhere('day_type', 'Fri');
            @endphp

            <table class="rate-table">
                <caption class="sr-only">Meeting room hourly rates</caption>
                <thead>
                    <tr>
                        <th scope="col">Day</th>
                        <th scope="col">Standard</th>
                        <th scope="col" style="padding-left: 8px;">Member</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Monday to Thursday</td>
                        <td>£{{ number_format(($weekdayRate->standard_price_per_hour ?? 12000) / 100, 0) }} / hour</td>
                        <td style="color: var(--claret-700); font-weight: 600; padding-left: 8px;">
                            £{{ number_format($weekdayRate ? $weekdayRate->memberPricePerHour() / 100 : 96, 0) }} / hour
                        </td>
                    </tr>
                    <tr>
                        <td>Friday</td>
                        <td>£{{ number_format(($fridayRate->standard_price_per_hour ?? 9000) / 100, 0) }} / hour</td>
                        <td style="color: var(--claret-700); font-weight: 600; padding-left: 8px;">
                            £{{ number_format($fridayRate ? $fridayRate->memberPricePerHour() / 100 : 72, 0) }} / hour
                        </td>
                    </tr>
                    <tr>
                        <td>Weekends and bank holidays</td>
                        <td colspan="2" style="color: var(--slate-600); padding-left: 8px;">
                            @if ($exampleHall?->rates->firstWhere('day_type', 'Sat') || $exampleHall?->rates->firstWhere('day_type', 'Sun'))
                                Published per room — request your date.
                            @else
                                Rates set by the club — request your date.
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; margin-top: 28px;">
                <a href="{{ route('rooms.index') }}" class="btn btn-navy">Request a booking</a>
                <a href="{{ route('rooms.index') }}" class="link-claret">View all rooms</a>
            </div>
        </div>
    </div>
</section>

{{-- 5. Events --}}
<section id="events" style="padding-block: clamp(64px, 8vw, 96px);">
    <div class="container-x">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 24px; margin-bottom: 48px;">
            <div>
                <p class="eyebrow" style="color: var(--gold-500);">What’s on</p>
                <h2 style="font-size: clamp(32px, 4vw, 44px); margin-top: 16px; color: var(--ivory-100);">Upcoming <em style="color: var(--champagne-200);">events</em></h2>
            </div>
            <a href="{{ route('events.index') }}" class="link-gold">All events</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
            @forelse ($events as $event)
                <article class="card-dark" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div class="placeholder" style="height: 200px; border: 0; border-bottom: 1px solid rgba(201,169,97,.3); background: var(--navy-900);">
                        [Event image]
                    </div>
                    <div style="padding: 28px; display: flex; flex-direction: column; flex: 1;">
                        <p class="eyebrow" style="font-size: 12px; color: var(--gold-500);">
                            <time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('j M Y · H:i') }}</time>
                        </p>
                        <h3 style="font-size: 24px; margin: 10px 0 8px; color: var(--ivory-100);">{{ $event->name }}</h3>
                        <p style="margin-bottom: 16px; color: var(--mist-300);">
                            £{{ number_format($event->price_per_ticket / 100, 2) }} · Members £{{ number_format(($pricing->applyDiscount((int) $event->price_per_ticket, (string) $event->member_discount_type, (int) $event->member_discount_value)) / 100, 2) }}
                        </p>
                        <a href="{{ route('events.show', $event->slug) }}" class="link-gold" style="margin-top: auto;">Request a place</a>
                    </div>
                </article>
            @empty
                <div class="card-dark">
                    <p style="color: var(--mist-300);">Our next season is being planned. <a href="{{ route('contact') }}" class="link-gold">Ask what’s coming up.</a></p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- 6. Gallery strip + testimonial --}}
<section id="gallery" style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x">
        <div style="max-width: 760px; margin-inline: auto; text-align: center;">
            <p class="eyebrow" style="color: var(--claret-700);">In their words</p>
            @php $quote = $testimonials->first(); @endphp
            <blockquote style="margin: 24px 0 20px; font-family: var(--font-display); font-size: clamp(24px, 3vw, 34px); font-style: italic; line-height: 1.35;">
                “{{ $quote?->quote ?? '[Member quote, shared with permission]' }}”
            </blockquote>
            <p style="margin-bottom: 28px; color: var(--slate-600);">
                {{ $quote?->author_name ?? '[Member name]' }}@if ($quote?->author_context), {{ $quote->author_context }}@endif
            </p>
            <a href="{{ route('gallery.index') }}" class="link-claret">View the gallery</a>
        </div>

        @if ($photos->isNotEmpty())
            <div style="margin-top: 48px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                @foreach ($photos as $photo)
                    <a href="{{ route('gallery.photo', $photo) }}" data-lightbox="{{ route('gallery.photo', $photo) }}" data-alt="{{ $photo->alt_text }}" style="display: block; overflow: hidden; border-radius: 4px;">
                        <img src="{{ route('gallery.thumb', $photo) }}" alt="{{ $photo->alt_text ?: 'Charlton Exclusive Club event photo' }}"
                             width="600" height="600" loading="lazy"
                             style="width: 100%; height: 180px; object-fit: cover; transition: transform .25s ease;">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- 7. Location --}}
<section id="contact" style="background: var(--navy-800); padding-block: clamp(64px, 8vw, 96px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; gap: 64px;">
        <div style="flex: 1 1 380px;">
            <p class="eyebrow" style="color: var(--gold-500);">Find us</p>
            <h2 style="font-size: clamp(32px, 4vw, 44px); margin: 16px 0 24px; color: var(--ivory-100);">Visit us in <em style="color: var(--champagne-200);">Charlton</em></h2>

            <address style="font-style: normal; color: var(--ivory-100); margin-bottom: 20px;">
                Charlton Exclusive Club<br>
                Unit 6, Block 3 Woolwich<br>
                Dockyard Industrial Estate, Woolwich Church St<br>
                Charlton, London SE18 5PQ
            </address>

            <p style="margin-bottom: 8px;"><a href="tel:+442032474747" class="link-gold">+44 (0) 2032 474747</a></p>
            <p style="margin-bottom: 24px;"><a href="mailto:info@charltonexclusiveclub.co.uk" class="link-gold">info@charltonexclusiveclub.co.uk</a></p>

            <p style="color: var(--mist-300); margin-bottom: 28px;">
                {{ \App\Models\AppConfig::get('travel_notes', '[Nearest station and walking time] · [Bus routes] · [Parking]') }}
            </p>

            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('Charlton Exclusive Club, Woolwich Church St, London SE18 5PQ') }}"
               class="btn btn-line" rel="noopener" target="_blank">Get directions</a>
        </div>

        <div style="flex: 1 1 440px; min-height: 340px;">
            <iframe
                title="Map showing Charlton Exclusive Club"
                src="https://www.google.com/maps?q=Woolwich+Church+St,+London+SE18+5PQ&output=embed"
                width="100%" height="340" style="border: 1px solid rgba(201,169,97,.3); border-radius: 4px; filter: grayscale(.25);"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>

{{-- 8. Final CTA --}}
<section id="apply" style="background: var(--gold-500); color: var(--navy-900); padding-block: clamp(48px, 6vw, 72px); text-align: center;">
    <div class="container-x">
        <h2 style="font-size: clamp(30px, 4vw, 42px);">Ready to belong?</h2>
        <p style="margin: 12px auto 28px; max-width: 520px;">Begin your application in a few minutes. We will guide you through every step.</p>
        <a href="{{ route('account.applications.start') }}" class="btn btn-navy">Start your application</a>
    </div>
</section>

@endsection
