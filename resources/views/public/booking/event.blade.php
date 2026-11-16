@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(48px, 6vw, 80px);">
    <div class="container-x">
        <nav aria-label="Breadcrumb" style="font-size: 14px; color: var(--mist-300); margin-bottom: 20px;">
            <a href="{{ route('home') }}">Home</a> <span aria-hidden="true">/</span>
            <a href="{{ route('events.index') }}">Events</a> <span aria-hidden="true">/</span>
            <a href="{{ route('events.show', $event->slug) }}">{{ $event->name }}</a> <span aria-hidden="true">/</span>
            <span aria-current="page" style="color: var(--ivory-100);">Request a place</span>
        </nav>

        <div style="display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start;">
            <div style="flex: 1 1 460px;">
                <p class="eyebrow" style="color: var(--gold-500);">Event booking</p>
                <h1 style="font-size: clamp(34px, 4.4vw, 52px); margin: 14px 0 20px;">{{ $event->name }}</h1>
                <p style="font-size: 17px; color: var(--mist-300); margin-bottom: 28px;">
                    <time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('l j F Y, H:i') }}</time>
                </p>

                <div class="card-dark" style="margin-bottom: 28px;">
                    <p style="font-size: 15px; color: var(--mist-300); margin-bottom: 10px;">{{ $event->description }}</p>
                    <dl style="display: grid; gap: 10px; font-size: 15px; margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(201,169,97,.25); padding-top: 10px;">
                            <dt style="color: var(--mist-300);">Venue</dt>
                            <dd style="margin: 0;">{{ $event->venue ?: 'The club' }}</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Duration</dt>
                            <dd style="margin: 0;">{{ $event->starts_at->format('H:i') }}–{{ $event->ends_at->format('H:i') }}</dd>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <dt style="color: var(--mist-300);">Tickets per booking</dt>
                            <dd style="margin: 0;">Up to {{ $event->tickets_per_booking }}</dd>
                        </div>
                    </dl>
                </div>

                <p style="font-size: 15px; color: var(--mist-300);">
                    The club will confirm your place request within 72 hours. Payment is taken once your place is confirmed.
                </p>
            </div>

            <aside style="flex: 1 1 400px;">
                <div class="card-dark">
                    <h2 style="font-size: 26px; margin-bottom: 20px;">Request tickets</h2>

                    @if ($errors->any())
                        <div role="alert" style="border: 1px solid #F0A7B2; background: rgba(158,43,60,.16); color: #F0A7B2; padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            Please check the highlighted fields and try again.
                        </div>
                    @endif

                    @guest
                        <div role="alert" style="border: 1px solid #C9A961; background: rgba(201,169,97,.12); color: var(--champagne-200); padding: 14px 16px; border-radius: 4px; margin-bottom: 20px;">
                            <p style="margin: 0 0 6px;"><strong>Add to cart as guest</strong></p>
                            <p style="margin: 0; font-size: 14px; color: var(--mist-300);">You can add tickets to your cart now. You will be asked to sign in or register when you proceed to checkout.</p>
                        </div>
                    @endguest

                    <form method="POST" action="{{ route('cart.add.event', $event) }}" style="display: grid; gap: 18px;">
                        @csrf
                        <div>
                            <label class="field-label" for="tickets">Number of tickets</label>
                            <input class="field-dark" id="tickets" name="tickets" type="number" min="1" max="{{ $event->tickets_per_booking }}" value="{{ old('tickets', 1) }}" required>
                            @error('tickets')<p class="error-text">{{ $message }}</p>@enderror
                        </div>

                        <div style="padding: 16px; background: rgba(201,169,97,.08); border: 1px solid rgba(201,169,97,.3); border-radius: 4px;">
                            <p style="font-size: 15px; margin-bottom: 8px;">Price per ticket</p>
                            <p style="font-size: 28px; font-family: var(--font-display); color: var(--champagne-200); margin: 0;">
                                £{{ number_format($event->price_per_ticket / 100, 2) }}
                            </p>
                            @auth
                                @if (auth()->user()->canGetMemberRates())
                                    <p style="font-size: 13px; color: var(--mist-300); margin-top: 8px;">
                                        Your member price: £{{ number_format(app(\App\Services\PricingService::class)->applyDiscount((int) $event->price_per_ticket, (string) $event->member_discount_type, (int) $event->member_discount_value) / 100, 2) }}
                                    </p>
                                @endif
                            @endauth
                        </div>

                        <button type="submit" class="btn btn-gold" style="width: 100%;">Add to cart</button>
                        <p style="font-size: 13px; color: var(--mist-300); margin-top: -8px;">Add to your cart and check out securely via Stripe.</p>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection