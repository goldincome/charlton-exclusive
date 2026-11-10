<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Plan;
use App\Models\SectionUsage;
use App\Models\StripeEvent;
use App\Services\InvoicePaymentService;
use App\Services\MembershipBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Create a Stripe Checkout session for an unpaid invoice.
     */
    public function checkout(Request $request, Invoice $invoice)
    {
        abort_unless($request->user()?->can('view', $invoice), 403);

        abort_unless($invoice->status === Invoice::UNPAID, 422, 'This invoice is not payable.');

        if (! config('cashier.secret')) {
            return back()->with('status', 'Stripe is not configured in this environment yet.');
        }

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()->with('status', 'Could not connect to Stripe right now. Please try again. ('.get_class($e).')');
        }

        return redirect($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if ($sessionId && config('cashier.secret')) {
            try {
                $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
                $invoiceId = (string) ($session->metadata->invoice_id ?? '');

                if ($invoiceId && ($session->payment_status ?? null) === 'paid'
                    && ($invoice = Invoice::query()->find($invoiceId))) {
                    app(InvoicePaymentService::class)->fulfill(
                        $invoice,
                        $session->payment_intent ?? null,
                        $session->id ?? null,
                    );
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

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
                $event = Webhook::constructEvent($payload, $signature, $secret);
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

        if (StripeEvent::query()->where('stripe_event_id', $eventId)->exists()) {
            return response('Already processed', 200);
        }

        StripeEvent::query()->create([
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
        $billing = app(MembershipBillingService::class);

        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $session = $event->data->object;
            $metadata = (array) ($session->metadata ?? []);
            $invoiceId = $metadata['invoice_id'] ?? null;
            $orderId = $metadata['order_id'] ?? null;

            if ($orderId && ($order = Order::query()->with('items')->find($orderId)) && $order->status !== 'paid') {
                $order->update(['status' => 'paid', 'stripe_session_id' => $session->id ?? $order->stripe_session_id]);
                CartItem::query()->where('user_id', $order->user_id)->delete();
                try {
                    foreach ($order->items->groupBy('section_id') as $sectionId => $items) {
                        if (! $sectionId) {
                            continue;
                        }
                        SectionUsage::query()->updateOrCreate(
                            ['usable_type' => Order::class, 'usable_id' => $order->id, 'section_id' => (int) $sectionId],
                            [
                                'user_id' => $order->user_id,
                                'plan_id' => $order->plan_id,
                                'original_amount' => (int) $items->sum('original_amount'),
                                'discount_amount' => (int) $items->sum('discount_amount'),
                                'final_amount' => (int) $items->sum('final_amount'),
                                'discount_type' => $order->discount_type,
                                'discount_value' => (int) $order->discount_value,
                                'used_at' => now(),
                                'metadata' => ['reference' => $order->reference],
                            ]
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('Order section usage recording failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                }
            }

            if ($invoiceId && ($invoice = Invoice::query()->with('user', 'invoiceable')->find($invoiceId))) {
                $customerId = $session->customer ?? null;

                if ($customerId && $invoice->user && ! $invoice->user->stripe_id) {
                    $invoice->user->forceFill(['stripe_id' => $customerId])->save();
                }

                app(InvoicePaymentService::class)->fulfill(
                    $invoice,
                    $session->payment_intent ?? null,
                    $session->id ?? null,
                    $session->subscription ?? null,
                    $metadata['billing_frequency'] ?? null,
                );
            }
        }

        if ($type === 'invoice.paid') {
            $billing->recordPaidInvoice($event->data->object);
        }

        if ($type === 'invoice.payment_failed') {
            $billing->recordFailedInvoice($event->data->object);
        }

        if (in_array($type, ['customer.subscription.created', 'customer.subscription.updated'], true)) {
            $billing->syncSubscription($event->data->object);
        }

        if ($type === 'customer.subscription.deleted') {
            $billing->subscriptionDeleted($event->data->object);
        }

        if ($type === 'charge.refunded') {
            $charge = $event->data->object;
            Log::info('Stripe refund received', ['charge' => $charge->id]);
        }

        if (in_array($type, ['product.updated', 'price.created', 'price.updated'], true)) {
            $object = $event->data->object;
            $planId = $object->metadata->plan_id ?? null;
            if ($planId && ($plan = Plan::query()->find($planId))) {
                $updates = ['stripe_sync_status' => 'synced', 'stripe_synced_at' => now(), 'stripe_sync_error' => null];
                if ($type === 'product.updated') {
                    $updates['stripe_product_id'] = $object->id;
                } else {
                    $frequency = $object->metadata->frequency ?? null;
                    if ($frequency === Membership::MONTHLY) {
                        $updates['stripe_monthly_price_id'] = $object->id;
                    } else {
                        $updates['stripe_annual_price_id'] = $object->id;
                        $updates['stripe_price_id'] = $object->id;
                    }
                    $updates['stripe_product_id'] = $object->product ?? $plan->stripe_product_id;
                }
                $plan->update($updates);
            }
        }

        if ($type === 'charge.dispute.created') {
            Log::critical('Stripe dispute created', ['event' => $event->id]);
        }

        StripeEvent::query()->where('stripe_event_id', $event->id)->update(['processed_at' => now()]);
    }
}
