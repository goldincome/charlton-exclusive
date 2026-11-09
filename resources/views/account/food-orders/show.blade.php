@extends('layouts.panel', ['title' => 'Order details'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('account.food-orders.index') }}" class="link-claret" style="font-size: 14px;">← Back to orders</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ ucfirst($order->type) }} food order</h1>
    <p style="color: var(--slate-600);">Ref: {{ $order->reference }}</p>
</header>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Order items</h2>
            <div style="display: grid; gap: 12px;">
                @foreach ($order->items as $item)
                    <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #E0D5B8;">
                        <div>
                            <p style="font-weight: 600; margin-bottom: 4px;">{{ $item->menuItem->name }}</p>
                            <p style="font-size: 14px; color: var(--slate-600);">Quantity: {{ $item->quantity }}</p>
                        </div>
                        <p style="font-weight: 600;">£{{ number_format(($item->unit_price * $item->quantity) / 100, 2) }}</p>
                    </div>
                @endforeach
            </div>

            @if ($order->price_locked)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--claret-700);">
                    <div style="display: flex; justify-content: space-between; font-size: 20px;">
                        <strong>Total</strong>
                        <strong style="color: var(--claret-700);">£{{ number_format($order->price_locked / 100, 2) }}</strong>
                    </div>
                </div>
            @endif
        </div>

        @if ($order->dietary_notes)
            <div class="card-light">
                <h3 style="font-size: 18px; margin-bottom: 8px;">Dietary notes</h3>
                <p style="color: var(--slate-600);">{{ $order->dietary_notes }}</p>
            </div>
        @endif
    </div>

    <aside>
        <div class="card-light">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Details</h2>
            <dl style="display: grid; gap: 12px;">
                <div>
                    <dt style="font-size: 13px; color: var(--slate-600); margin-bottom: 4px;">Requested for</dt>
                    <dd style="margin: 0; font-weight: 600;">{{ $order->requested_for->format('D j M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt style="font-size: 13px; color: var(--slate-600); margin-bottom: 4px;">Status</dt>
                    <dd style="margin: 0;"><span class="badge badge-{{ $order->status === 'confirmed' ? 'green' : 'gold' }}">{{ ucfirst($order->status) }}</span></dd>
                </div>
                @if ($order->location)
                    <div>
                        <dt style="font-size: 13px; color: var(--slate-600); margin-bottom: 4px;">Location</dt>
                        <dd style="margin: 0;">{{ $order->location }}</dd>
                    </div>
                @endif
                @if ($order->booking)
                    <div>
                        <dt style="font-size: 13px; color: var(--slate-600); margin-bottom: 4px;">Linked booking</dt>
                        <dd style="margin: 0;"><a href="{{ route('account.bookings.show', $order->booking) }}" class="link-claret">{{ $order->booking->reference }}</a></dd>
                    </div>
                @endif
            </dl>
        </div>
    </aside>
</div>
@endsection