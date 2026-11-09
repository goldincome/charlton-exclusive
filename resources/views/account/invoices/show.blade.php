@extends('layouts.panel', ['title' => 'Invoice'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('account.invoices.index') }}" class="link-claret" style="font-size: 14px;">← Back to invoices</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $invoice->number }}</h1>
    <p style="color: var(--slate-600);">Issued {{ $invoice->issued_at->format('j F Y') }}</p>
</header>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
    <div>
        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Invoice items</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--claret-700);">
                        <th style="text-align: left; padding: 10px 0; font-weight: 600;">Description</th>
                        <th style="text-align: right; padding: 10px 0; font-weight: 600;">Qty</th>
                        <th style="text-align: right; padding: 10px 0; font-weight: 600;">Unit</th>
                        <th style="text-align: right; padding: 10px 0; font-weight: 600;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->lines as $line)
                        <tr style="border-bottom: 1px solid #E0D5B8;">
                            <td style="padding: 12px 0;">{{ $line->description }}</td>
                            <td style="text-align: right; padding: 12px 0;">{{ $line->quantity }}</td>
                            <td style="text-align: right; padding: 12px 0;">£{{ number_format($line->unit_price / 100, 2) }}</td>
                            <td style="text-align: right; padding: 12px 0; font-weight: 600;">£{{ number_format($line->gross / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--claret-700);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Subtotal</span>
                    <strong>£{{ number_format($invoice->subtotal / 100, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span>VAT</span>
                    <strong>£{{ number_format($invoice->vat_amount / 100, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 22px;">
                    <strong>Total</strong>
                    <strong style="color: var(--claret-700);">£{{ number_format($invoice->total / 100, 2) }}</strong>
                </div>
            </div>
        </div>

        @if ($invoice->payments->isNotEmpty())
            <div class="card-light">
                <h2 style="font-size: 22px; margin-bottom: 16px;">Payment history</h2>
                <div style="display: grid; gap: 12px;">
                    @foreach ($invoice->payments as $payment)
                        <div style="padding: 12px; background: #fff; border: 1px solid #E0D5B8; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <strong>£{{ number_format($payment->amount / 100, 2) }}</strong>
                                <span class="badge badge-{{ $payment->status === 'succeeded' ? 'green' : 'neutral' }}">{{ ucfirst($payment->status) }}</span>
                            </div>
                            <p style="font-size: 14px; color: var(--slate-600); margin: 0;">
                                {{ ucfirst($payment->method) }} · {{ $payment->paid_at?->format('j M Y, H:i') ?? 'Pending' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 22px; margin-bottom: 12px;">Status</h2>
            <p style="font-size: 32px; font-family: var(--font-display); color: var(--champagne-200); margin: 0 0 8px;">
                £{{ number_format($invoice->total / 100, 2) }}
            </p>
            <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : 'gold' }}">{{ ucfirst($invoice->status) }}</span>

            @if ($invoice->status === 'unpaid')
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(201,169,97,.25);">
                    <form method="POST" action="{{ route('checkout.invoice', $invoice) }}">
                        @csrf
                        <button type="submit" class="btn btn-gold" style="width: 100%;">Pay now</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="card-light">
            <h3 style="font-size: 18px; margin-bottom: 12px;">Download</h3>
            <a href="{{ route('account.invoices.pdf', $invoice) }}" class="btn btn-line-dark btn-sm" style="width: 100%;">Download PDF</a>
        </div>
    </aside>
</div>
@endsection