@php
    $nav = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Membership', 'route' => 'membership.index'],
        ['label' => 'Meeting Rooms', 'route' => 'rooms.index'],
        ['label' => 'Events', 'route' => 'events.index'],
        ['label' => 'Dining', 'route' => 'dining'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
    $current = request()->route()?->getName();
    $cartCount = auth()->check()
        ? \App\Models\CartItem::query()->where('user_id', auth()->id())->sum('quantity')
        : collect(session('cart_items', []))->sum('quantity');
@endphp

<header style="background: var(--navy-900); border-bottom: 1px solid rgba(201,169,97,.3); position: sticky; top: 0; z-index: 50;">
    <div class="container-x" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-block: 14px;">
        <a href="{{ route('home') }}" aria-label="Charlton Exclusive Club — home" style="display: flex; flex-direction: column; line-height: 1.1; flex-shrink: 0;">
            <span style="font-family: var(--font-display); font-size: 24px; font-weight: 600; color: var(--ivory-100);">Charlton</span>
            <span class="eyebrow" style="font-size: 10px; color: var(--gold-500);">Exclusive Club</span>
        </a>

        <nav aria-label="Main" class="desktop-nav" style="display: none; align-items: center; gap: 18px; flex-wrap: nowrap;">
            @foreach ($nav as $item)
                @php $isActive = $current === $item['route']; @endphp
                <a href="{{ route($item['route']) }}"
                   style="padding: 8px 2px; font-size: 14px; color: {{ $isActive ? 'var(--champagne-200)' : 'inherit' }}; border-bottom: 1px solid {{ $isActive ? 'var(--gold-500)' : 'transparent' }}; white-space: nowrap;"
                   @if ($isActive) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <span style="color: rgba(201,169,97,0.3); margin-inline: 4px;">|</span>

            <a href="{{ route('cart.index') }}" style="position: relative; display: flex; align-items: center; gap: 4px; font-size: 14px; color: var(--champagne-200); text-decoration: none; white-space: nowrap;">
                <span>🛒 Cart</span>
                @if ($cartCount > 0)
                    <span style="background: var(--gold-500); color: var(--navy-900); font-size: 11px; font-weight: 700; border-radius: 50%; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center;">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('account.dashboard') }}" style="font-size: 14px; color: var(--champagne-200); white-space: nowrap;">My account</a>
            @else
                <a href="{{ route('login') }}" style="font-size: 14px; color: var(--champagne-200); white-space: nowrap;">Member login</a>
            @endauth
            <a href="{{ route('account.applications.start') }}" class="btn btn-gold btn-sm" style="white-space: nowrap; padding: 6px 12px; font-size: 13px;">Request an invitation</a>
        </nav>

        <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" class="mobile-menu-button"
                style="background: transparent; border: 1px solid rgba(201,169,97,.5); color: var(--ivory-100); border-radius: 4px; min-height: 40px; min-width: 40px; padding: 0 10px; font: inherit; cursor: pointer;">
            <span aria-hidden="true">Menu</span>
        </button>
    </div>

    <div id="mobile-menu" data-menu-drawer hidden style="border-top: 1px solid rgba(201,169,97,.3);">
        <nav aria-label="Mobile" class="container-x" style="display: grid; gap: 4px; padding-block: 16px 24px;">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" style="padding: 12px 0; font-size: 17px;">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('cart.index') }}" style="padding: 12px 0; font-size: 17px; color: var(--champagne-200);">Cart ({{ $cartCount }})</a>
            @guest
                <a href="{{ route('login') }}" style="padding: 12px 0; font-size: 17px; color: var(--champagne-200);">Member login</a>
            @else
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('account.dashboard') }}" style="padding: 12px 0; font-size: 17px; color: var(--champagne-200);">My account</a>
            @endguest
            <a href="{{ route('account.applications.start') }}" class="btn btn-gold" style="margin-top: 12px;">Request an invitation</a>
        </nav>
    </div>
</header>

<style>
    @media (min-width: 1100px) {
        .desktop-nav { display: flex !important; }
        .mobile-menu-button { display: none !important; }
    }
</style>
