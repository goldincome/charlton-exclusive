@extends('layouts.panel', ['title' => 'Invoice ' . $invoice->number])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.invoices.index') }}" class="link-claret" style="font-size: 14px;">← Back to invoices</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">Invoice {{ $invoice->number }}</h1>
    <p style="color: var(--slate-600);">Total: £{{ number_format($invoice->total / 100, 2) }} · Status: {{ ucfirst($invoice->status) }}</p>
</header>

<div class="card-light">
    <h2 style="font-size: 22px; margin-bottom: 16px;">Invoice Details</h2>
    <p style="color: var(--slate-600);">Invoice management and payment logging.</p>
</div>
@endsection
