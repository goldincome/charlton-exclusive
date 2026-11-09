<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\BookingService;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class StripeController extends Controller
{
    /**
     * Create a Stripe Checkout session for an unpaid invoice.
     */
    public function checkout(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        abort_unless($invoice->status === Invoice::UNPAID, 422, 'This invoice is not payable.');

        if (! config('cashier.secret')) {
            return back()->with('status', 'Stripe is not configured in this environment yet.');
        }

        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $request->user()->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => 'Charlton Exclusive Club — Invoice '.$invoice->number],
                    'unit_amount' => $invoice->total,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancelled'),
            'metadata' => ['invoice_id' => $invoice->id],
        ]);

        return redirect($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        return redirect()->route('account.invoices.index')
            ->with('status', 'Thank you — your payment is being confirmed.');
    }

    public function cancelled(): RedirectResponse
    {
        return redirect()->route('account.invoices.index')->with('status', 'Payment cancelled. Your invoice is still unpaid.');
    }

    /**
     * Stripe webhook with signature verification and idempotency.
     */
    public function webhook(Request $request)
    {
        $secret = config('cashier.webhook.secret');
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if ($secret) {
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            } catch (\Throwable $e) {
                Log::warning('Stripe webhook signature failed: '.$e->getMessage());

                return response('Invalid signature', 400);
            }
        } else {
            $event = json_decode($payload);
        }

        $eventId = $event->id ?? null;

        if (! $eventId) {
            return response('Malformed event', 400);
        }

        if (\App\Models\StripeEvent::query()->where('stripe_event_id', $eventId)->exists()) {
            return response('Already processed', 200);
        }

        \App\Models\StripeEvent::query()->create([
            'stripe_event_id' => $eventId,
            'type' => $event->type,
            'payload' => $payload,
        ]);

        $this->handle($event);

        return response('OK', 200);
    }

    private function handle(object $event): void
    {
        $type = $event->type;

        if ($type === 'checkout.session.completed') {
            $session = $event->data->object;
            $invoiceId = $session->metadata->invoice_id ?? null;

            if ($invoiceId && ($invoice = Invoice::query()->find($invoiceId))) {
                Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'amount' => $invoice->total,
                    'currency' => 'gbp',
                    'method' => 'card',
                    'status' => 'succeeded',
                    'paid_at' => now(),
                ]);

                $invoice->update(['status' => Invoice::PAID]);

                if ($invoice->invoiceable_type === \App\Models\Booking::class) {
                    app(BookingService::class)->confirm($invoice->invoiceable);
                }
            }
        }

        if ($type === 'charge.refunded') {
            $charge = $event->data->object;
            Log::info('Stripe refund received', ['charge' => $charge->id]);
        }

        if ($type === 'invoice.payment_failed' || $type === 'customer.subscription.deleted') {
            Log::warning('Stripe subscription event', ['type' => $type]);
        }

        if ($type === 'charge.dispute.created') {
            Log::critical('Stripe dispute created', ['event' => $event->id]);
        }

        \App\Models\StripeEvent::query()->where('stripe_event_id', $event->id)->update(['processed_at' => now()]);
    }
}
