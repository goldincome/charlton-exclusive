<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #1A202C; margin: 0; padding: 0; }
        h1 { font-size: 22pt; margin: 0 0 8px; }
        h2 { font-size: 14pt; margin: 24px 0 12px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 8px 0; border-bottom: 1px solid #E2E8F0; }
        th { font-weight: bold; border-bottom: 2px solid #7A1F2E; }
        .text-right { text-align: right; }
        .total-row { border-top: 2px solid #7A1F2E; font-weight: bold; padding-top: 8px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 9pt; }
        .badge-green { background: #E8F5E9; color: #2E7D32; }
        .badge-gold { background: #FFF8E1; color: #F57F17; }
    </style>
</head>
<body style="padding: 40px 50px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 32px;">
        <div>
            <h1 style="font-family: 'Georgia', serif; color: #7A1F2E;">Charlton Exclusive Club</h1>
            <p style="font-size: 9pt; color: #64748B; margin-top: 4px;">
                Unit 6, Block 3 Woolwich<br>
                Dockyard Industrial Estate<br>
                Woolwich Church St, Charlton<br>
                London SE18 5PQ
            </p>
        </div>
        <div style="text-align: right;">
            <h2 style="margin: 0 0 8px;">INVOICE</h2>
            <p><strong>{{ $invoice->number }}</strong></p>
            <p style="font-size: 9pt; color: #64748B;">Issued: {{ $invoice->issued_at->format('j F Y') }}</p>
            <p style="margin-top: 8px;">
                <span class="badge badge-{{ $invoice->status === 'paid' ? 'green' : 'gold' }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </p>
        </div>
    </div>

    <div style="margin-bottom: 32px; padding: 16px; background: #F7FAFC; border-left: 3px solid #C9A961;">
        <p style="font-size: 9pt; color: #64748B; margin-bottom: 4px;">INVOICE TO</p>
        <p style="font-weight: bold;">{{ $invoice->user->name }}</p>
        <p style="font-size: 9pt; color: #64748B;">{{ $invoice->user->email }}</p>
    </div>

    <h2>Invoice items</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 80px;">Unit price</th>
                <th class="text-right" style="width: 80px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="text-right">{{ $line->quantity }}</td>
                    <td class="text-right">£{{ number_format($line->unit_price / 100, 2) }}</td>
                    <td class="text-right">£{{ number_format($line->gross / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 24px; text-align: right;">
        <table style="width: 240px; margin-left: auto; border: none;">
            <tr>
                <td style="border: none; padding: 4px 0;">Subtotal</td>
                <td class="text-right" style="border: none; padding: 4px 0;">£{{ number_format($invoice->subtotal / 100, 2) }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 4px 0;">VAT</td>
                <td class="text-right" style="border: none; padding: 4px 0;">£{{ number_format($invoice->vat_amount / 100, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td style="border: none; padding: 8px 0 0; font-size: 14pt;">Total</td>
                <td class="text-right" style="border: none; padding: 8px 0 0; font-size: 14pt; color: #7A1F2E;">£{{ number_format($invoice->total / 100, 2) }}</td>
            </tr>
        </table>
    </div>

    @if ($invoice->payments->isNotEmpty())
        <h2>Payment history</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('j M Y, H:i') ?? 'Pending' }}</td>
                        <td>{{ ucfirst($payment->method) }}</td>
                        <td class="text-right">£{{ number_format($payment->amount / 100, 2) }}</td>
                        <td><span class="badge badge-{{ $payment->status === 'succeeded' ? 'green' : 'gold' }}">{{ strtoupper($payment->status) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top: 48px; padding-top: 16px; border-top: 1px solid #E2E8F0; font-size: 8pt; color: #64748B;">
        <p>
            Charlton Exclusive Club · Company no. {{ \App\Models\AppConfig::get('company_number', '[NUMBER]') }} · 
            VAT no. {{ \App\Models\AppConfig::get('vat_number', '[NUMBER]') }}
        </p>
        <p style="margin-top: 8px;">
            @if ($invoice->status === 'unpaid')
                Payment is due within 72 hours of approval. For queries, contact info@charltonexclusiveclub.co.uk.
            @else
                Thank you for your payment.
            @endif
        </p>
    </div>
</body>
</html>