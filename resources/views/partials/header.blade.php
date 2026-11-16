@php
    $nav = [
        ['label' => 'Membership', 'route' => 'membership.index'],
        ['label' => 'Meeting Rooms', 'route' => 'rooms.index'],
        ['label' => 'Events', 'route' => 'events.index'],
        ['label' => 'Dining', 'route' => 'dining'],
        ['label' => 'Gallery', 'route' => 'gallery.index'],
        ['label' => 'Journal', 'route' => 'journal.index'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
    $current = request()->route()?->getName();
    $cartCount = auth()->check()
        ? \App\Models\CartItem::query()->where('user_id', auth()->id())->sum('quantity')
        : collect(session('cart_items', []))->sum('quantity');
@endphp

<header style="background: var(--navy-900); border-bottom: 1px solid rgba(201,169,97,.3); position: sticky; top: 0; z-index: 50;">
    <div class="container-x" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding-block: 18px;">
        <a href="{{ route('home') }}" aria-label="Charlton Exclusive Club — home" style="display: flex; flex-direction: column; line-height: 1.1;">
            <span style="font-family: var(--font-display); font-size: 28px; font-weight: 600; color: var(--ivory-100);">Charlton</span>
            <span class="eyebrow" style="font-size: 11px; color: var(--gold-500);">Exclusive Club</span>
        </a>

        <nav aria-label="Main" class="desktop-nav" style="display: none; flex-wrap: wrap; gap: 12px 26px;">
            @foreach ($nav as $item)
                @php $isActive = $current === $item['route']; @endphp
                <a href="{{ route($item['route']) }}"
                   style="padding: 12px 2px; font-size: 15px; color: {{ $isActive ? 'var(--champagne-200)' : 'inherit' }}; border-bottom: 1px solid {{ $isActive ? 'var(--gold-500)' : 'transparent' }};"
                   @if ($isActive) aria-current="page" @endif>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="{{ route('cart.index') }}" style="position: relative; display: flex; align-items: center; gap: 6px; font-size: 15px; color: var(--champagne-200); text-decoration: none;">
                <span>🛒 Cart</span>
                @if ($cartCount > 0)
                    <span style="background: var(--gold-500); color: var(--navy-900); font-size: 11px; font-weight: 700; border-radius: 50%; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center;">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('account.dashboard') }}" style="font-size: 15px; color: var(--champagne-200);">My account</a>
            @else
                <a href="{{ route('login') }}" style="font-size: 15px; color: var(--champagne-200);">Member login</a>
            @endauth
            <a href="{{ route('account.applications.start') }}" class="btn btn-gold btn-sm">Request an invitation</a>

            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" class="mobile-menu-button"
                    style="background: transparent; border: 1px solid rgba(201,169,97,.5); color: var(--ivory-100); border-radius: 4px; min-height: 44px; min-width: 44px; padding: 0 12px; font: inherit; cursor: pointer;">
                <span aria-hidden="true">Menu</span><span class="sr-only">Toggle navigation</span>
            </button>
        </div>
    </div>

    <div id="mobile-menu" data-menu-drawer hidden style="border-top: 1px solid rgba(201,169,97,.3);">
        <nav aria-label="Mobile" class="container-x" style="display: grid; gap: 4px; padding-block: 16px 24px;">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" style="padding: 12px 0; font-size: 17px;">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('cart.index') }}" style="padding: 12px 0; font-size: 17px; color: var(--champagne-200);">Cart ({{ $cartCount }})</a>
            @guest
                <a href="{{ route('login') }}" style="padding: 12px 0; font-size: 17px; color: var(--champagne-200);">Member login</a>
            @endguest
            <a href="{{ route('account.applications.start') }}" class="btn btn-gold" style="margin-top: 12px;">Request an invitation</a>
        </nav>
    </div>
</header>

<style>
    @media (min-width: 1024px) {
        .desktop-nav { display: flex !important; }
        .mobile-menu-button { display: none !important; }
    }
</style>
