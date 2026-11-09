@extends('layouts.panel', ['title' => 'Food orders'])

@section('content')
<header style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Food orders</h1>
        <p style="color: var(--slate-600);">View and create food orders for events and meetings.</p>
    </div>
    <a href="{{ route('account.food-orders.create') }}" class="btn btn-navy">Place an order</a>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($orders as $order)
        <a href="{{ route('account.food-orders.show', $order) }}" class="card-light" style="display: block; text-decoration: none; color: inherit;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <p class="eyebrow" style="font-size: 12px; color: var(--claret-700);">
                        Requested for {{ $order->requested_for->format('D j M Y') }}
                    </p>
                    <h3 style="font-size: 22px; margin: 6px 0 4px;">{{ ucfirst($order->type) }} order</h3>
                    <p style="color: var(--slate-600); font-size: 15px;">
                        {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }} · 
                        Ref: {{ $order->reference }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-{{ $order->status === 'confirmed' ? 'green' : ($order->status === 'requested' ? 'gold' : 'neutral') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    @if ($order->price_locked)
                        <p style="margin: 8px 0 0; font-weight: 600;">£{{ number_format($order->price_locked / 100, 2) }}</p>
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="card-light">
            <p>You have no food orders yet.</p>
            <a href="{{ route('account.food-orders.create') }}" class="btn btn-navy btn-sm" style="margin-top: 16px;">Place an order</a>
        </div>
    @endforelse
</div>

@if ($orders->hasPages())
    <div style="margin-top: 32px;">{{ $orders->links() }}</div>
@endif
@endsection