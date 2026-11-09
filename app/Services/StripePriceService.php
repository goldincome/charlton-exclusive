<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use Laravel\Cashier\Cashier;
use RuntimeException;

class StripePriceService
{
    /**
     * Ensure a recurring Stripe price exists for the plan at the given billing frequency.
     * Returns the Stripe price id.
     */
    public function ensure(Plan $plan, string $frequency): string
    {
        if (! config('cashier.secret')) {
            throw new RuntimeException('Stripe is not configured in this environment.');
        }

        $frequency = $frequency === Membership::MONTHLY ? Membership::MONTHLY : Membership::ANNUAL;
        $interval = $frequency === Membership::ANNUAL ? 'year' : 'month';
        $amount = $plan->feeFor($frequency);

        if ($amount <= 0) {
            throw new RuntimeException("The {$plan->name} plan has no {$frequency} fee configured.");
        }

        $stripe = Cashier::stripe();
        $currency = strtolower((string) config('cashier.currency', 'gbp'));

        $productId = $plan->stripe_product_id;
        if ($productId) {
            $stripe->products->update($productId, [
                'name' => $plan->name,
                'active' => (bool) $plan->is_active,
                'metadata' => ['plan_id' => (string) $plan->id, 'slug' => $plan->slug],
            ]);
        } else {
            $product = $stripe->products->create([
                'name' => $plan->name,
                'active' => (bool) $plan->is_active,
                'metadata' => ['plan_id' => (string) $plan->id, 'slug' => $plan->slug],
            ]);
            $productId = $product->id;
            $plan->update(['stripe_product_id' => $productId]);
            $plan->refresh();
        }

        $column = $frequency === Membership::ANNUAL ? 'stripe_annual_price_id' : 'stripe_monthly_price_id';
        $cached = $plan->{$column};
        if (! $cached && $frequency === Membership::ANNUAL) {
            $cached = $plan->stripe_price_id;
        }

        if ($cached) {
            try {
                $existing = $stripe->prices->retrieve($cached);
                $matches = (int) $existing->unit_amount === $amount
                    && strtolower((string) $existing->currency) === $currency
                    && (string) ($existing->product ?? '') === (string) $productId
                    && (($existing->recurring->interval ?? null) === $interval)
                    && ($existing->active ?? true);

                if ($matches) {
                    return $cached;
                }
            } catch (\Throwable) {
                // Fall through and create a fresh price.
            }
        }

        $price = $stripe->prices->create([
            'product' => $productId,
            'unit_amount' => $amount,
            'currency' => $currency,
            'recurring' => ['interval' => $interval],
            'metadata' => ['plan_id' => (string) $plan->id, 'frequency' => $frequency],
        ], [
            'idempotency_key' => 'plan_'.$plan->id.'_'.$frequency.'_'.md5($productId.$amount.$currency.$interval),
        ]);

        $updates = ['stripe_product_id' => $productId, $column => $price->id];
        if ($frequency === Membership::ANNUAL) {
            $updates['stripe_price_id'] = $price->id;
        }
        $plan->update($updates);

        return $price->id;
    }

    /**
     * Sync every configured recurring price for a plan.
     */
    public function sync(Plan $plan): void
    {
        $frequencies = [Membership::ANNUAL];

        if ($plan->monthlyAvailable()) {
            $frequencies[] = Membership::MONTHLY;
        }

        foreach ($frequencies as $frequency) {
            $this->ensure($plan, $frequency);
        }

        $plan->update([
            'stripe_sync_status' => 'synced',
            'stripe_sync_error' => null,
            'stripe_synced_at' => now(),
        ]);
    }
}
