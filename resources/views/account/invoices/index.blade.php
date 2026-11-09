@extends('layouts.panel', ['title' => 'Invoices'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Invoices</h1>
    <p style="color: var(--slate-600);">View and pay your invoices.</p>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($invoices as $invoice)
        <a href="{{ route('account.invoices.show', $invoice) }}" class="card-light" style="display: block; text-decoration: none; color: inherit;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                <div style="flex: 1;">
                    <h3 style="font-size: 22px; margin: 0 0 6px;">{{ $invoice->number }}</h3>
                    <p style="color: var(--slate-600); font-size: 15px;">
                        Issued {{ $invoice->issued_at->format('j M Y') }} · 
                        {{ $invoice->lines->count() }} {{ Str::plural('item', $invoice->lines->count()) }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <p style="font-size: 24px; font-weight: 600; margin: 0 0 6px;">£{{ number_format($invoice->total / 100, 2) }}</p>
                    <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : ($invoice->status === 'unpaid' ? 'gold' : 'neutral') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>
        </a>
    @empty
        <div class="card-light">
            <p>You have no invoices yet.</p>
        </div>
    @endforelse
</div>

@if ($invoices->hasPages())
    <div style="margin-top: 32px;">{{ $invoices->links() }}</div>
@endif
@endsection