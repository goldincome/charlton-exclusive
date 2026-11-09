@extends('layouts.panel', ['title' => 'Membership payment'])

@section('content')
<header style="margin-bottom: 32px;">
    <h1 style="font-size: 32px; margin: 0 0 8px;">Application received</h1>
    <p style="color: var(--slate-600);">Thank you. Your membership payment is being confirmed.</p>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

<div class="card-light" style="margin-bottom: 24px;">
    <h2 style="font-size: 22px; margin-bottom: 12px;">What happens next</h2>
    @if ($application->status === 'active')
        <p style="color: var(--slate-600); margin-bottom: 12px;">Your payment has been received and your membership is active.</p>
        <a href="{{ route('account.applications.index') }}" class="btn btn-navy btn-sm">View my membership</a>
    @else
        <p style="color: var(--slate-600); margin-bottom: 12px;">
            We are confirming your payment with Stripe. This usually takes a few seconds. If your membership does not activate straight away, refresh this page or check “My membership” shortly.
        </p>
        <a href="{{ route('account.applications.index') }}" class="btn btn-line-dark btn-sm">Go to my membership</a>
    @endif
</div>

@if ($invoice)
    <div class="card-light">
        <h2 style="font-size: 18px; margin-bottom: 10px;">Payment summary</h2>
        <p style="margin: 0 0 6px;"><strong>Invoice:</strong> {{ $invoice->number }}</p>
        <p style="margin: 0;"><strong>Amount:</strong> {{ $invoice->grossFormatted() }} · {{ ucfirst($invoice->status) }}</p>
    </div>
@endif
@endsection
