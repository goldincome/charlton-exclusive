<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use RuntimeException;

class MembershipUpgradeService
{
    public function __construct(
        private StripePriceService $prices,
        private MembershipBillingService $billing,
    ) {
    }

    /**
     * Plans a member may upgrade to. Upgrades are ordered by annual fee and
     * only plans above the member's current plan are offered.
     */
    public function upgradePlans(User $user): \Illuminate\Support\Collection
    {
        $membership = $user->membership()->with('plan')->first();
        $currentFee = (int) ($membership?->plan?->annual_fee ?? 0);

        return Plan::query()
            ->active()
            ->where(function ($query) use ($membership, $currentFee) {
                $query->where('annual_fee', '>', $currentFee);

                // Monthly members may switch their current plan to yearly billing.
                if ($membership?->isMonthly() && $membership->plan_id) {
                    $query->orWhere('id', $membership->plan_id);
                }
            })
            ->orderBy('annual_fee')
            ->get();
    }

    /**
     * Preview the prorated amount Stripe will charge today for the change.
     *
     * @return array{amount_due:int, currency:string}
     */
    public function quote(Membership $membership, Plan $target, string $frequency): array
    {
        $subscriptionId = $this->requireSubscription($membership);

        $priceId = $this->prices->ensure($target, $frequency);
        $stripe = Cashier::stripe();
        $subscription = $stripe->subscriptions->retrieve($subscriptionId);
        $itemId = $subscription->items->data[0]->id ?? null;

        if (! $itemId) {
            throw new RuntimeException('This membership does not have a billable subscription item.');
        }

        try {
            $preview = $stripe->invoices->upcoming([
                'customer' => $subscription->customer,
                'subscription' => $subscriptionId,
                'subscription_items' => [[
                    'id' => $itemId,
                    'price' => $priceId,
                ]],
            ]);

            return [
                'amount_due' => (int) ($preview->amount_due ?? 0),
                'currency' => strtoupper((string) ($preview->currency ?? config('cashier.currency', 'gbp'))),
            ];
        } catch (\Throwable $e) {
            Log::warning('Stripe upgrade preview failed', [
                'membership_id' => $membership->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('We could not calculate the upgrade price right now. Please try again later.');
        }
    }

    /**
     * Upgrade the membership immediately with a prorated Stripe charge.
     * The change is atomic: a failed payment changes nothing locally.
     *
     * @return array{amount_due:int, currency:string}
     */
    public function upgrade(User $actor, Membership $membership, Plan $target, string $frequency): array
    {
        $subscriptionId = $this->requireSubscription($membership);

        $frequency = $frequency === Membership::MONTHLY ? Membership::MONTHLY : Membership::ANNUAL;
        $priceId = $this->prices->ensure($target, $frequency);
        $stripe = Cashier::stripe();
        $subscription = $stripe->subscriptions->retrieve($subscriptionId);
        $itemId = $subscription->items->data[0]->id ?? null;

        if (! $itemId) {
            throw new RuntimeException('This membership does not have a billable subscription item.');
        }

        $preview = $this->quote($membership, $target, $frequency);

        try {
            $updated = $stripe->subscriptions->update($subscriptionId, [
                'items' => [[
                    'id' => $itemId,
                    'price' => $priceId,
                ]],
                'proration_behavior' => 'always_invoice',
                'payment_behavior' => 'error_if_incomplete',
                'metadata' => [
                    'user_id' => (string) $membership->user_id,
                    'plan_id' => (string) $target->id,
                    'billing_frequency' => $frequency,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Stripe membership upgrade failed', [
                'membership_id' => $membership->id,
                'target_plan_id' => $target->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('The upgrade payment was declined or could not be completed. Your membership has not been changed.');
        }

        DB::transaction(function () use ($actor, $membership, $target, $frequency, $updated) {
            $periodEnd = $this->billing->periodEndFromSubscription($updated);

            $membership->update([
                'membership_category_id' => $target->id,
                'plan_id' => $target->id,
                'billing_frequency' => $frequency,
                'status' => Membership::ACTIVE,
                'renews_at' => $periodEnd?->toDateString() ?? $membership->renews_at,
                'ends_at' => null,
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
            ]);

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'membership.upgraded',
                'auditable_type' => Membership::class,
                'auditable_id' => $membership->id,
                'new_values' => [
                    'plan_id' => $target->id,
                    'plan_name' => $target->name,
                    'billing_frequency' => $frequency,
                    'prorated_amount' => $preview['amount_due'],
                ],
                'ip_address' => request()->ip(),
            ]);
        });

        $membership->loadMissing(['user', 'plan']);

        if ($membership->user) {
            try {
                app(MailService::class)->send(
                    'membership_upgraded',
                    $membership->user,
                    'Your membership has been upgraded to '.$target->name,
                    [
                        'greeting' => 'Hello '.($membership->user->name ?: 'there').',',
                        'planName' => $target->name,
                        'billingFrequency' => $frequency === Membership::MONTHLY ? 'Monthly' : 'Yearly',
                        'proratedAmount' => '£'.number_format($preview['amount_due'] / 100, 2),
                        'renewsAt' => $membership->renews_at?->format('j F Y'),
                        'membershipUrl' => route('account.applications.index'),
                    ] + [
                        'clubName' => config('club.name'),
                        'clubAddress' => config('club.address'),
                        'clubPhone' => config('club.phone'),
                        'clubEmail' => config('club.email'),
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Membership upgraded email failed', [
                    'membership_id' => $membership->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $preview;
    }

    private function requireSubscription(Membership $membership): string
    {
        if (! $membership->hasStripeSubscription() || ! config('cashier.secret')) {
            throw new RuntimeException('Online plan changes are not available for this membership. Please contact the club.');
        }

        return $membership->stripe_subscription_id;
    }
}
