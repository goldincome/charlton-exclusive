@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(24px, 4vw, 48px);">
    <div class="container-x" style="display: flex; flex-wrap: wrap; gap: 48px; align-items: center;">
        <div style="flex: 1 1 480px;">
            <p class="eyebrow" style="color: var(--gold-500);">Meeting rooms</p>
            <h1 style="font-size: clamp(38px, 5vw, 60px); margin: 18px 0 22px; max-width: 780px;" class="text-balance">
                Rooms made for <em style="color: var(--champagne-200);">serious conversation</em>.
            </h1>
            <p style="font-size: 19px; color: var(--mist-300); max-width: 640px;">
                Minimum three hours, in thirty-minute increments. All room prices include VAT at 20%. Members receive their published discount.
            </p>
        </div>
        <div style="flex: 1 1 420px; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); background: #1B2A4A;">
            @php $heroHall = $halls->first()?->heroImage(); @endphp
            @if ($heroHall)
                <img src="{{ route('media.public', $heroHall->path) }}" alt="Meeting rooms" width="800" height="600" style="width: 100%; height: 360px; object-fit: cover; display: block;">
            @else
                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80" alt="Executive meeting room" width="800" height="600" style="width: 100%; height: 360px; object-fit: cover; display: block;">
            @endif
        </div>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x" style="display: grid; gap: 32px;">
        @forelse ($halls as $hall)
            <article class="card-light" style="display: flex; flex-wrap: wrap; gap: 32px; padding: 0; overflow: hidden;">
                <div style="flex: 1 1 320px; min-height: 280px; background: #E9E1CB;">
                    @if ($hall->heroImage())
                        <img src="{{ route('media.public', $hall->heroImage()->path) }}" alt="{{ $hall->heroImage()->alt_text ?: $hall->name }}"
                             width="800" height="600" loading="lazy" style="width: 100%; height: 100%; min-height: 280px; object-fit: cover;">
                    @else
                        <div class="placeholder" style="height: 100%; min-height: 280px; border: 0; background: #E9E1CB; color: var(--slate-600);">[Photograph: {{ $hall->name }}]</div>
                    @endif
                </div>

                <div style="flex: 1 1 380px; padding: 32px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                        <span class="badge badge-neutral" style="background: rgba(122,31,46,.08); color: var(--claret-700); border-color: rgba(122,31,46,.3);">Up to {{ $hall->capacity }} guests</span>
                        @if ($hall->capacity_seated)
                            <span class="badge badge-neutral" style="background: rgba(122,31,46,.08); color: var(--claret-700); border-color: rgba(122,31,46,.3);">{{ $hall->capacity_seated }} seated</span>
                        @endif
                        <span class="badge badge-neutral" style="background: rgba(122,31,46,.08); color: var(--claret-700); border-color: rgba(122,31,46,.3);">Minimum {{ $hall->min_hours }} hours</span>
                    </div>

                    <h2 style="font-size: 30px;">{{ $hall->name }}</h2>
                    <p style="margin: 12px 0 18px; color: var(--slate-600);">{{ $hall->description }}</p>

                    @if ($hall->features->isNotEmpty())
                        <ul style="display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 0 0 20px; padding: 0; list-style: none; font-size: 15px; color: var(--slate-600);">
                            @foreach ($hall->features as $feature)
                                <li>· {{ $feature->name }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @php $weekday = $hall->rates->firstWhere('day_type', 'Mon'); @endphp
                    @if ($weekday)
                        <p style="font-size: 17px; margin-bottom: 20px;">
                            <strong>£{{ number_format($weekday->standard_price_per_hour / 100, 0) }}</strong>/hour or
                            <strong style="color: var(--claret-700);">£{{ number_format($weekday->memberPricePerHour() / 100, 0) }}</strong>/hour for members (<a href="{{ route('membership.index') }}" class="link-claret">Become a member</a>)
                        </p>
                    @else
                        <p style="font-size: 15px; color: var(--slate-600); margin-bottom: 20px;">Rates for this room are being confirmed.</p>
                    @endif

                    <div style="display: flex; flex-wrap: wrap; gap: 14px;">
                        <a href="{{ route('rooms.show', $hall->slug) }}" class="btn btn-navy btn-sm">View room</a>
                        <a href="{{ route('rooms.show', $hall->slug) }}" class="btn btn-line-dark btn-sm">Request a booking</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="card-light">
                <p>Our rooms are being prepared for publication. Please <a href="{{ route('contact') }}" class="link-claret">contact the club</a> to discuss hire.</p>
            </div>
        @endforelse
    </div>
</section>

<section style="padding-block: clamp(48px, 6vw, 72px);">
    <div class="container-x" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
        <div class="card-dark">
            <h2 style="font-size: 22px; margin-bottom: 10px;">How booking works</h2>
            <p style="color: var(--mist-300);">Request a date and time. The club approves it, then you have 72 hours to pay. Your booking is confirmed when payment clears.</p>
        </div>
        <div class="card-dark">
            <h2 style="font-size: 22px; margin-bottom: 10px;">Cancellations</h2>
            <p style="color: var(--mist-300);">Seven days or more before the start: full refund. Between 48 hours and seven days: 50%. Less than 48 hours: no refund.</p>
        </div>
        <div class="card-dark">
            <h2 style="font-size: 22px; margin-bottom: 10px;">Food and drink</h2>
            <p style="color: var(--mist-300);">Hire a meeting room and receive {{ \App\Models\AppConfig::int('hall_food_discount_value', 20) }}% off food. Beverages are excluded.</p>
        </div>
    </div>
</section>
@endsection