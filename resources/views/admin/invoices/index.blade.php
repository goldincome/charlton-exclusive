@extends('layouts.panel', ['title' => 'Invoices Management'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Invoices</h1>
    <p style="color: var(--slate-600);">Review club invoices and payments.</p>
</header>

<div style="display: grid; gap: 16px;">
    @forelse ($invoices as $invoice)
        <div class="card-light">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 20px; margin: 0 0 4px;">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" style="text-decoration: none; color: inherit;">
                            {{ $invoice->number }}
                        </a>
                    </h3>
                    <p style="color: var(--slate-600); font-size: 14px; margin: 0;">Issued: {{ $invoice->issued_at->format('j M Y') }}</p>
                </div>
                <div style="text-align: right;">
                    <p style="font-weight: 600; margin: 0 0 4px;">£{{ number_format($invoice->total / 100, 2) }}</p>
                    <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : 'gold' }}">{{ ucfirst($invoice->status) }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="card-light">
            <p style="margin: 0; color: var(--slate-600);">No invoices found.</p>
        </div>
    @endforelse
</div>

@if ($invoices->hasPages())
    <div style="margin-top: 32px;">{{ $invoices->links() }}</div>
@endif
@endsection
