@extends('layouts.public')

@section('content')
<section style="padding-block: clamp(24px, 4vw, 48px);">
    <div class="container-x">
        <p class="eyebrow" style="color: var(--gold-500);">Dining</p>
        <h1 style="font-size: clamp(38px, 5vw, 60px); margin: 18px 0 22px; max-width: 760px;" class="text-balance">
            A menu made <em style="color: var(--champagne-200);">for the table</em>.
        </h1>
        <p style="font-size: 19px; color: var(--mist-300); max-width: 640px;">
            Order for a party or for yourself. Book a meeting room and receive a discount on food orders. Prices include VAT at 20%.
        </p>
    </div>
</section>

<section style="background: var(--ivory-100); color: var(--ink-900); padding-block: clamp(56px, 7vw, 88px);">
    <div class="container-x" style="max-width: 920px;">
        @forelse ($categories as $category)
            <div style="margin-bottom: 56px;">
                <h2 style="font-size: 32px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid var(--claret-700);">
                    {{ $category->name }}
                </h2>

                <div style="display: grid; gap: 20px;">
                    @foreach ($category->items as $item)
                        <article style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid #E0D5B8;">
                            <div style="flex: 1 1 320px;">
                                <h3 style="font-size: 20px; margin-bottom: 8px;">{{ $item->name }}</h3>
                                @if ($item->description)
                                    <p style="color: var(--slate-600); margin-bottom: 10px;">{{ $item->description }}</p>
                                @endif
                                @if ($item->allergenFlags->isNotEmpty())
                                    <p style="font-size: 13px; color: var(--slate-500);">
                                        Contains: {{ $item->allergenFlags->pluck('allergen')->map(fn($a) => ucfirst(str_replace('_', ' ', $a)))->join(', ') }}
                                    </p>
                                @endif
                            </div>
                            <div style="flex: 0 0 auto; text-align: right;">
                                <p style="font-size: 22px; font-weight: 600; color: var(--claret-700);">£{{ number_format($item->price / 100, 2) }}</p>
                                @if ($item->discount_exempt)
                                    <p style="font-size: 12px; color: var(--slate-500); margin-top: 4px;">Discount exempt</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card-light">
                <p>Our dining menu is being prepared for publication. <a href="{{ route('contact') }}" class="link-claret">Ask about current offerings.</a></p>
            </div>
        @endforelse

        @if ($allergens)
            <div style="margin-top: 48px; padding-top: 32px; border-top: 1px solid #D8CBAA;">
                <h3 style="font-size: 18px; margin-bottom: 12px;">Allergen information</h3>
                <p style="font-size: 14px; color: var(--slate-600); margin-bottom: 12px;">
                    We identify the 14 major allergens recognised in UK food law. If you have a food allergy or intolerance, please speak to a member of staff before ordering.
                </p>
                <ul style="display: flex; flex-wrap: wrap; gap: 8px 16px; font-size: 13px; color: var(--slate-600); margin: 0; padding: 0; list-style: none;">
                    @foreach ($allergens as $allergen)
                        <li>· {{ ucfirst(str_replace('_', ' ', $allergen)) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>

<section style="padding-block: clamp(48px, 6vw, 72px);">
    <div class="container-x" style="text-align: center;">
        <h2 style="font-size: clamp(28px, 3.4vw, 40px); margin-bottom: 16px;">Order for your next event</h2>
        <p style="margin: 0 auto 28px; max-width: 540px; color: var(--mist-300);">
            Members can place food orders online. Hire a meeting room and receive {{ \App\Models\AppConfig::int('hall_food_discount_value', 20) }}% off food orders.
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 14px; justify-content: center;">
            @auth
                <a href="{{ route('account.food-orders.create') }}" class="btn btn-gold">Place an order</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-gold">Create an account</a>
            @endauth
            <a href="{{ route('rooms.index') }}" class="btn btn-line">View meeting rooms</a>
        </div>
    </div>
</section>
@endsection