<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;

class MembershipBillingService
{
    public function membershipForSubscription(?string $subscriptionId): ?Membership
    {
        if (! $subscriptionId) {
            return null;
        }

        return Membership::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->latest('id')
            ->first();
    }

    /**
     * Mirror a paid Stripe subscription invoice locally and keep the membership
     * renewal date in sync.
     */
    public function recordPaidInvoice(object $stripeInvoice): void
    {
        $invoice = $this->linkOrCreateInvoice($stripeInvoice, Invoice::PAID);

        $subscriptionId = $stripeInvoice->subscription ?? null;
        $membership = $this->membershipForSubscription($subscriptionId);

        if ($invoice) {
            $invoice->update([
                'stripe_invoice_id' => $stripeInvoice->id,
                'stripe_hosted_invoice_url' => $stripeInvoice->hosted_invoice_url ?? null,
                'status' => Invoice::PAID,
            ]);

            app(InvoicePaymentService::class)->fulfill(
                $invoice,
                $stripeInvoice->payment_intent ?? null,
                null,
                $subscriptionId,
                $membership?->billing_frequency,
            );
        }

        if ($membership) {
            $periodEnd = $this->periodEndFromInvoice($stripeInvoice);

            $membership->update([
                'status' => $membership->status === Membership::CANCELLED
                    ? Membership::CANCELLED
                    : Membership::ACTIVE,
                'renews_at' => $periodEnd?->toDateString() ?? $membership->renews_at,
                'ends_at' => null,
            ]);

            $membership->user?->memberProfile?->update([
                'is_financial' => true,
                'is_suspended' => false,
                'suspended_at' => null,
            ]);
        }
    }

    /**
     * Mirror a failed Stripe invoice, flag the membership as past due and email
     * the member an invoice plus hosted payment link.
     */
    public function recordFailedInvoice(object $stripeInvoice): void
    {
        $invoice = $this->linkOrCreateInvoice($stripeInvoice, Invoice::UNPAID);

        if ($invoice) {
            $amount = (int) ($stripeInvoice->amount_due ?? $invoice->total);

            $invoice->update([
                'stripe_invoice_id' => $stripeInvoice->id,
                'stripe_hosted_invoice_url' => $stripeInvoice->hosted_invoice_url ?? null,
                'status' => Invoice::UNPAID,
                'subtotal' => $amount,
                'vat_amount' => 0,
                'total' => $amount,
            ]);
        }

        $subscriptionId = $stripeInvoice->subscription ?? null;
        $membership = $this->membershipForSubscription($subscriptionId);

        if (! $membership) {
            return;
        }

        $membership->update(['status' => Membership::PAST_DUE]);
        $membership->loadMissing(['user', 'plan']);

        if (! $membership->user) {
            return;
        }

        try {
            app(MailService::class)->send(
                'renewal_payment_failed',
                $membership->user,
                'Action required — membership renewal payment failed',
                [
                    'greeting' => 'Hello '.($membership->user->name ?: 'there').',',
                    'planName' => $membership->plan?->name ?? 'Membership',
                    'invoiceNumber' => $invoice?->number ?? ($stripeInvoice->number ?? '—'),
                    'amountDue' => $this->money((int) ($stripeInvoice->amount_due ?? 0)),
                    'hostedInvoiceUrl' => $stripeInvoice->hosted_invoice_url ?? null,
                    'billingUrl' => route('account.invoices.index'),
                ] + $this->clubData()
            );
        } catch (\Throwable $e) {
            Log::warning('Renewal payment failed email failed', [
                'membership_id' => $membership->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Keep local membership state in sync with a Stripe subscription update.
     */
    public function syncSubscription(object $subscription): void
    {
        $membership = $this->membershipForSubscription($subscription->id ?? null);

        if (! $membership) {
            return;
        }

        $frequency = $subscription->metadata->billing_frequency ?? null;
        if (! in_array($frequency, [Membership::MONTHLY, Membership::ANNUAL], true)) {
            $frequency = $membership->billing_frequency;
        }

        $periodEnd = $this->periodEndFromSubscription($subscription);
        $cancellingAtPeriodEnd = (bool) ($subscription->cancel_at_period_end ?? false);

        $status = match ($subscription->status ?? null) {
            'active', 'trialing' => Membership::ACTIVE,
            'past_due', 'unpaid', 'incomplete', 'incomplete_expired' => Membership::PAST_DUE,
            'canceled' => Membership::CANCELLED,
            default => $membership->status,
        };

        $membership->update([
            'billing_frequency' => $frequency,
            'status' => $status,
            'renews_at' => $cancellingAtPeriodEnd ? $membership->renews_at : ($periodEnd?->toDateString() ?? $membership->renews_at),
            'ends_at' => $cancellingAtPeriodEnd ? ($periodEnd?->toDateString() ?? $membership->ends_at) : $membership->ends_at,
        ]);
    }

    /**
     * A subscription was deleted (either immediately or at period end).
     */
    public function subscriptionDeleted(object $subscription): void
    {
        $membership = $this->membershipForSubscription($subscription->id ?? null);

        if (! $membership) {
            return;
        }

        $membership->update([
            'status' => Membership::CANCELLED,
            'ends_at' => now()->toDateString(),
            'cancelled_at' => $membership->cancelled_at ?? now(),
            'cancellation_reason' => $membership->cancellation_reason ?? 'Subscription ended in Stripe.',
        ]);

        $membership->user?->memberProfile?->update([
            'is_financial' => false,
            'is_suspended' => true,
            'suspended_at' => now(),
        ]);
    }

    public function periodEndFromSubscription(object $subscription): ?Carbon
    {
        $timestamp = $subscription->current_period_end
            ?? $subscription->items->data[0]->current_period_end
            ?? null;

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    public function periodEndFromInvoice(object $stripeInvoice): ?Carbon
    {
        $lines = $stripeInvoice->lines->data ?? [];
        $timestamp = null;

        foreach ($lines as $line) {
            if (! empty($line->period->end)) {
                $timestamp = max((int) $timestamp, (int) $line->period->end);
            }
        }

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    /**
     * Find the local invoice for this Stripe invoice, or create a mirror row.
     */
    private function linkOrCreateInvoice(object $stripeInvoice, string $status): ?Invoice
    {
        $existing = Invoice::query()->where('stripe_invoice_id', $stripeInvoice->id)->first();

        if ($existing) {
            return $existing;
        }

        $subscriptionId = $stripeInvoice->subscription ?? null;
        $membership = $this->membershipForSubscription($subscriptionId);

        $candidate = null;

        if (($stripeInvoice->billing_reason ?? null) === 'subscription_create') {
            $candidate = Invoice::query()
                ->whereNull('stripe_invoice_id')
                ->where('status', Invoice::UNPAID)
                ->where('invoiceable_type', Application::class)
                ->when($membership?->user_id, fn ($q, $userId) => $q->where('user_id', $userId))
                ->latest('id')
                ->first();
        }

        if ($candidate) {
            return $candidate;
        }

        $userId = $membership?->user_id ?? $this->resolveUserIdFromInvoice($stripeInvoice);

        if (! $userId) {
            return null;
        }

        $amount = (int) ($status === Invoice::PAID
            ? ($stripeInvoice->amount_paid ?? 0)
            : ($stripeInvoice->amount_due ?? 0));

        $number = $stripeInvoice->number ?: 'STRIPE-'.str_replace(['in_', '_'], ['', '-'], (string) $stripeInvoice->id);

        $invoice = Invoice::query()->firstOrCreate(
            ['number' => $number],
            [
                'user_id' => $userId,
                'invoiceable_type' => $membership ? Membership::class : null,
                'invoiceable_id' => $membership?->id,
                'issued_at' => now()->toDateString(),
                'subtotal' => $amount,
                'vat_amount' => 0,
                'total' => $amount,
                'status' => $status,
            ]
        );

        if ($invoice->wasRecentlyCreated) {
            InvoiceLineItem::query()->create([
                'invoice_id' => $invoice->id,
                'description' => 'Membership subscription — '.($stripeInvoice->lines->data[0]->description ?? 'renewal'),
                'quantity' => 1,
                'unit_price' => $amount,
                'net' => $amount,
                'vat_rate' => 0,
                'vat' => 0,
                'gross' => $amount,
            ]);
        }

        return $invoice;
    }

    private function resolveUserIdFromInvoice(object $stripeInvoice): ?int
    {
        $subscriptionId = $stripeInvoice->subscription ?? null;

        if (! $subscriptionId || ! config('cashier.secret')) {
            return null;
        }

        try {
            $subscription = Cashier::stripe()->subscriptions->retrieve($subscriptionId);
            $applicationId = $subscription->metadata->application_id ?? null;

            if ($applicationId) {
                return Application::query()->whereKey($applicationId)->value('user_id');
            }

            $stripeCustomerId = $stripeInvoice->customer ?? null;

            if ($stripeCustomerId) {
                return User::query()->where('stripe_id', $stripeCustomerId)->value('id');
            }
        } catch (\Throwable $e) {
            Log::warning('Could not resolve user for Stripe invoice', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function money(int $pence): string
    {
        return '£'.number_format($pence / 100, 2);
    }

    private function clubData(): array
    {
        return [
            'clubName' => config('club.name'),
            'clubAddress' => config('club.address'),
            'clubPhone' => config('club.phone'),
            'clubEmail' => config('club.email'),
        ];
    }
}
