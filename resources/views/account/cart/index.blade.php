@extends('layouts.panel', ['title' => 'Section cart'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Section cart</h1>
    <p style="color: var(--slate-600);">Bookable section services. Member plan discounts are calculated at checkout for eligible members; final amounts are snapshot on the order.</p>
</header>

<div class="card-light" style="margin-bottom:24px;">
    @forelse ($items as $line)
        <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; padding:12px 0; border-bottom:1px solid #E0D5B8;">
            <div>
                <strong>{{ $line->item->name }}</strong><br>
                <span style="color:var(--slate-600); font-size:13px;">{{ $line->item->section?->name }} · {{ $line->item->section?->access_type === 'open' ? 'Open' : 'Members only' }}</span>
            </div>
            <div style="text-align:right;">
                <div>Qty {{ $line->quantity }} · £{{ number_format(((int)$line->item->price * (int)$line->quantity)/100, 2) }}</div>
                <form method="POST" action="{{ route('account.cart.remove', $line->item) }}" style="margin-top:6px;">@csrf<button type="submit" class="link-claret" style="background:none;border:none;padding:0;cursor:pointer;">Remove</button></form>
            </div>
        </div>
    @empty
        <p style="color:var(--slate-600); margin:0;">Your cart is empty. Add active bookable items from a section service.</p>
    @endforelse
</div>

@if ($items->isNotEmpty())
    <div class="card-light">
        <h2 style="font-size:22px; margin-bottom:12px;">Estimated total</h2>
        @foreach ($quotes as $sectionId => $quote)
            @if(($quote['discount_amount'] ?? 0) > 0)
                <p style="font-size:14px; color:var(--slate-600); margin:0 0 6px;">Section discount: £{{ number_format($quote['discount_amount']/100,2) }} off ({{ $quote['discount_type']==='fixed' ? 'fixed' : ((int)($quote['discount_value'] ?? 0).'%') }}).</p>
            @endif
        @endforeach
        <p style="font-size:28px; font-family:var(--font-display); color:var(--claret-700); margin:8px 0 16px;">£{{ number_format($total/100, 2) }}</p>
        <form method="POST" action="{{ route('account.cart.checkout') }}">@csrf<button type="submit" class="btn btn-navy">Checkout (Stripe)</button></form>
        <p style="font-size:13px; color:var(--slate-600); margin-top:10px;">Prices are recalculated server-side; no frontend price is trusted.</p>
    </div>
@endif
@endsection
