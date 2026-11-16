@extends(auth()->check() ? 'layouts.panel' : 'layouts.public', ['title' => 'Cart'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Your booking cart</h1>
    <p style="color: var(--slate-600);">Review your selected meeting rooms and events before checking out securely via Stripe.</p>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

<div class="card-light" style="margin-bottom:24px;">
    @forelse ($items as $line)
        @php
            $meta = $line->metadata ?? [];
            $linePrice = isset($meta['price']) ? (int) $meta['price'] : (int) $line->item->price * (int) $line->quantity;
            $start = isset($meta['start_time']) ? \Carbon\Carbon::parse($meta['start_time']) : null;
            $end = isset($meta['end_time']) ? \Carbon\Carbon::parse($meta['end_time']) : null;
            $slotSelections = $meta['slot_selections'] ?? [];
            $hours = !empty($slotSelections) ? count($slotSelections) : (($start && $end) ? $start->diffInMinutes($end) / 60 : (int) $line->quantity);
            $unitPrice = ($hours > 0 && isset($meta['price'])) ? (int) round($meta['price'] / $hours) : (int) ($line->item->price ?? 0);
        @endphp
        <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; padding:16px 0; border-bottom:1px solid #E0D5B8;">
            <div>
                <h3 style="font-size: 18px; margin: 0 0 4px;">{{ $line->item->name }}</h3>
                <p style="color:var(--slate-600); font-size:14px; margin: 0 0 6px;">{{ $line->item->section?->name }}</p>
                @if (($meta['type'] ?? '') === 'hall')
                    <div style="color: var(--ink-900); font-size: 13px; display: grid; gap: 3px;">
                        <div>Date: <strong>{{ isset($meta['booking_date']) ? \Carbon\Carbon::parse($meta['booking_date'])->format('D, j F Y') : '—' }}</strong></div>
                        @if (!empty($slotSelections))
                            <div>Selected Times: <strong>{{ implode(', ', $slotSelections) }}</strong></div>
                        @else
                            <div>Time: <strong>{{ $start ? $start->format('g:i A') : '' }} – {{ $end ? $end->format('g:i A') : '' }}</strong></div>
                        @endif
                        <div>Total Duration: <strong>{{ $hours }} {{ Str::plural('hour', $hours) }}</strong></div>
                        <div>Guests: <strong>{{ $meta['guests'] ?? $line->quantity }}</strong></div>
                        @if (!empty($meta['notes']))
                            <div style="color: var(--slate-600); font-style: italic;">Notes: "{{ $meta['notes'] }}"</div>
                        @endif
                    </div>
                @elseif (($meta['type'] ?? '') === 'event')
                    <div style="color: var(--ink-900); font-size: 13px; display: grid; gap: 3px;">
                        <div>Event Date: <strong>{{ isset($meta['starts_at']) ? \Carbon\Carbon::parse($meta['starts_at'])->format('D, j F Y, g:i A') : '—' }}</strong></div>
                        <div>Tickets: <strong>{{ $meta['tickets'] ?? $line->quantity }}</strong></div>
                    </div>
                @else
                    <p style="color: var(--slate-600); font-size: 13px; margin: 0;">Quantity: {{ $line->quantity }}</p>
                @endif
            </div>
            <div style="text-align:right;">
                <div style="font-size: 13px; color: var(--slate-600); margin-bottom: 2px;">
                    £{{ number_format($unitPrice / 100, 2) }} × {{ $hours }} {{ (($meta['type'] ?? '') === 'hall') ? Str::plural('hr', $hours) : Str::plural('ticket', $hours) }}
                </div>
                <div style="font-size: 18px; font-weight: 600; color: var(--claret-700); margin-bottom: 8px;">£{{ number_format($linePrice / 100, 2) }}</div>
                <form method="POST" action="{{ route('cart.remove', $line->item) }}">
                    @csrf
                    <button type="submit" class="link-claret" style="background:none;border:none;padding:0;cursor:pointer;font-size: 14px;">Remove</button>
                </form>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 32px 0;">
            <p style="color:var(--slate-600); margin: 0 0 16px;">Your cart is empty.</p>
            <div style="display: flex; justify-content: center; gap: 12px;">
                <a href="{{ route('rooms.index') }}" class="btn btn-navy">Browse meeting rooms</a>
                <a href="{{ route('events.index') }}" class="btn btn-line-dark">Browse events</a>
            </div>
        </div>
    @endforelse
</div>

@if ($items->isNotEmpty())
    <div class="card-light">
        <h2 style="font-size:22px; margin-bottom:12px;">Order Summary</h2>
        @foreach ($quotes as $sectionId => $quote)
            @if(($quote['discount_amount'] ?? 0) > 0)
                <p style="font-size:14px; color:var(--slate-600); margin:0 0 6px;">Section discount: £{{ number_format($quote['discount_amount']/100,2) }} off ({{ $quote['discount_type']==='fixed' ? 'fixed' : ((int)($quote['discount_value'] ?? 0).'%') }}).</p>
            @endif
        @endforeach
        <p style="font-size:28px; font-family:var(--font-display); color:var(--claret-700); margin:8px 0 16px;">£{{ number_format($total/100, 2) }}</p>
        <form method="POST" action="{{ route('cart.checkout') }}">
            @csrf
            <button type="submit" class="btn btn-navy" style="width: 100%; padding: 14px; font-size: 16px;">Proceed to Checkout & Pay via Stripe</button>
        </form>
        <p style="font-size:13px; color:var(--slate-600); margin-top:10px; text-align: center;">Secure payments powered by Stripe.</p>
    </div>
@endif
@endsection
