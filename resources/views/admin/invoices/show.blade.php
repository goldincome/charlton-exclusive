@extends('layouts.panel', ['title' => 'Invoice ' . $invoice->number])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.invoices.index') }}" class="link-claret" style="font-size: 14px;">← Back to invoices</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">Invoice {{ $invoice->number }}</h1>
    <p style="color: var(--slate-600);">
        Issued: {{ $invoice->issued_at?->format('j F Y') ?? '—' }} ·
        Status: {{ ucfirst($invoice->status) }} ·
        Total: £{{ number_format($invoice->total / 100, 2) }}
    </p>
</header>

@if (session('status'))
    <div role="status" style="background: #E8F5E9; color: #1B5E20; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #43A047;">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" style="background: #FEEBEE; color: #C62828; padding: 14px 18px; border-radius: 4px; margin-bottom: 24px; border-left: 4px solid #E53935;">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: start; margin-bottom: 32px;">
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
                    <span style="color: var(--slate-600);">Subtotal</span>
                    <strong>£{{ number_format($invoice->subtotal / 100, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span style="color: var(--slate-600);">VAT</span>
                    <strong>£{{ number_format($invoice->vat_amount / 100, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 22px;">
                    <strong>Total</strong>
                    <strong style="color: var(--claret-700);">£{{ number_format($invoice->total / 100, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="card-light" style="margin-bottom: 24px;">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Member / Billed to</h2>
            @if ($invoice->user)
                <dl style="display: grid; gap: 10px; margin: 0;">
                    <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                        <dt style="color: var(--slate-600);">Name</dt>
                        <dd style="margin: 0; font-weight: 600;">
                            <a href="{{ route('admin.members.show', $invoice->user) }}" class="link-claret">{{ $invoice->user->name }}</a>
                        </dd>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                        <dt style="color: var(--slate-600);">Email</dt>
                        <dd style="margin: 0;">{{ $invoice->user->email }}</dd>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 1px solid #E0D5B8;">
                        <dt style="color: var(--slate-600);">Phone</dt>
                        <dd style="margin: 0;">{{ $invoice->user->phone ?? '—' }}</dd>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 16px;">
                        <dt style="color: var(--slate-600);">Address</dt>
                        <dd style="margin: 0; text-align: right;">
                            {{ $invoice->user->address_line_1 ?? '—' }}<br>
                            @if ($invoice->user->address_line_2){{ $invoice->user->address_line_2 }}<br>@endif
                            {{ $invoice->user->city ?? '' }} {{ $invoice->user->postcode ?? '' }}
                        </dd>
                    </div>
                </dl>
            @else
                <p style="margin: 0; color: var(--slate-600);">No user associated with this invoice.</p>
            @endif
        </div>

        @if ($invoice->invoiceable)
            <div class="card-light" style="margin-bottom: 24px;">
                <h2 style="font-size: 20px; margin-bottom: 12px;">Related record</h2>
                @php
                    $item = $invoice->invoiceable;
                    $type = class_basename(get_class($item));
                @endphp
                <p style="margin: 0; color: var(--slate-600);">
                    Type: <strong>{{ $type }}</strong>
                    @if ($type === 'Application')
                        · Reference: <a href="{{ route('admin.applications.show', $item) }}" class="link-claret">{{ $item->reference }}</a>
                        · Status: {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                    @elseif ($type === 'Booking')
                        · Reference: <a href="{{ route('admin.bookings.show', $item) }}" class="link-claret">Booking #{{ $item->id }}</a>
                        · Status: {{ ucfirst($item->status) }}
                    @else
                        · ID: {{ $item->id ?? '—' }}
                    @endif
                </p>
            </div>
        @endif

        <div class="card-light">
            <h2 style="font-size: 22px; margin-bottom: 16px;">Payment history</h2>
            @if ($invoice->payments->isEmpty())
                <p style="margin: 0; color: var(--slate-600);">No payments recorded yet for this invoice.</p>
            @else
                <div style="display: grid; gap: 12px;">
                    @foreach ($invoice->payments as $payment)
                        <div style="padding: 14px; background: #fff; border: 1px solid #E0D5B8; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <strong style="font-size: 16px;">£{{ number_format($payment->amount / 100, 2) }}</strong>
                                <span class="badge badge-{{ $payment->status === 'succeeded' ? 'green' : 'neutral' }}">{{ ucfirst($payment->status) }}</span>
                            </div>
                            <p style="font-size: 14px; color: var(--slate-600); margin: 0 0 8px;">
                                Method: <strong>{{ ucfirst($payment->method) }}</strong> · Paid: {{ $payment->paid_at?->format('j M Y, H:i') ?? 'Pending' }}
                                @if ($payment->stripe_payment_intent_id)
                                    · Ref/PI: <code style="font-size: 12px;">{{ $payment->stripe_payment_intent_id }}</code>
                                @endif
                            </p>

                            @if ($payment->refunds->isNotEmpty())
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #E0D5B8;">
                                    <p style="font-size: 13px; font-weight: 600; color: #C62828; margin: 0 0 4px;">Refunds:</p>
                                    @foreach ($payment->refunds as $refund)
                                        <p style="font-size: 13px; color: var(--slate-600); margin: 0;">
                                            - £{{ number_format($refund->amount / 100, 2) }} on {{ $refund->created_at->format('j M Y') }} (Reason: {{ $refund->reason }})
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <aside>
        <div class="card-light" style="background: var(--navy-900); color: var(--ivory-100); margin-bottom: 20px;">
            <h2 style="font-size: 22px; margin-bottom: 12px;">Status &amp; payment</h2>
            <p style="font-size: 32px; font-family: var(--font-display); color: var(--champagne-200); margin: 0 0 8px;">
                £{{ number_format($invoice->total / 100, 2) }}
            </p>
            <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : 'gold' }}" style="margin-bottom: 20px; display: inline-block;">{{ ucfirst($invoice->status) }}</span>

            @if ($invoice->status !== 'paid')
                <div style="padding-top: 16px; border-top: 1px solid rgba(201,169,97,.25);">
                    <h3 style="font-size: 16px; color: var(--ivory-100); margin-bottom: 10px;">Record manual payment</h3>
                    <form method="POST" action="{{ route('admin.invoices.payment', $invoice) }}">
                        @csrf
                        <div style="margin-bottom: 12px;">
                            <label class="field-label" for="amount" style="color: var(--ivory-100);">Amount (£)</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="field-dark" value="{{ number_format(($invoice->total - $invoice->payments->sum('amount')) / 100, 2, '.', '') }}" required>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="field-label" for="reference" style="color: var(--ivory-100);">Reference / Stripe PI (optional)</label>
                            <input type="text" name="reference" id="reference" class="field-dark" placeholder="e.g. pi_... or bank ref">
                        </div>
                        <button type="submit" class="btn btn-gold" style="width: 100%;">Record payment</button>
                    </form>
                </div>
            @else
                <p style="margin: 16px 0 0; padding-top: 16px; border-top: 1px solid rgba(201,169,97,.25); font-size: 13px; color: var(--champagne-200);">
                    This invoice has been fully paid.
                </p>
            @endif
        </div>

        <div class="card-light">
            <h3 style="font-size: 18px; margin-bottom: 12px;">Download</h3>
            <a href="{{ route('account.invoices.pdf', $invoice) }}" class="btn btn-line-dark btn-sm" style="width: 100%;">Download PDF Invoice</a>
        </div>
    </aside>
</div>
@endsection
