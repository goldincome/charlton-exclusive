@extends('layouts.panel', ['title' => 'Section services'])

@section('content')
<header style="margin-bottom: 32px; display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:center;">
    <div>
        <h1 style="font-size: 32px; margin: 0 0 8px;">Section services</h1>
        <p style="color: var(--slate-600);">Open services are available to everyone. Members-only services require an active membership; plan discounts are applied per use at checkout.</p>
    </div>
    <a href="{{ route('account.cart.index') }}" class="btn btn-line-dark btn-sm">View cart</a>
</header>

@forelse ($items as $sectionName => $sectionItems)
    <div class="card-light" style="margin-bottom:20px;">
        <h2 style="font-size:22px; margin-bottom:14px;">{{ $sectionName ?: 'Section' }}</h2>
        <div style="display:grid; gap:12px;">
            @foreach ($sectionItems as $item)
                <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; padding:12px 0; border-bottom:1px solid #E0D5B8;">
                    <div>
                        <strong>{{ $item->name }}</strong>
                        <div style="color:var(--slate-600); font-size:14px;">{{ $item->description }} · £{{ number_format($item->price/100,2) }}@if($item->pricing_unit && $item->pricing_unit!=='flat') / {{ $item->pricing_unit }}@endif</div>
                    </div>
                    <form method="POST" action="{{ route('account.cart.add', $item) }}" style="display:flex; gap:8px; align-items:center;">
                        @csrf
                        <input type="number" name="quantity" value="1" min="1" max="50" style="width:72px;" class="field-light">
                        <button type="submit" class="btn btn-navy btn-sm">Add</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card-light"><p style="color:var(--slate-600); margin:0;">No active bookable services are available for your account. If you are setting up, run <code>php artisan sections:sync-bookable-items</code> and create active bookable items in the admin/data layer.</p></div>
@endforelse
@endsection
