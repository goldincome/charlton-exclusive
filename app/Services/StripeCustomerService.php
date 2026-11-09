<?php

namespace App\Services;

use App\Models\User;
use Laravel\Cashier\Cashier;
use RuntimeException;

class StripeCustomerService
{
    /**
     * Return a Stripe customer id for the user, creating or linking one as needed.
     */
    public function ensureFor(User $user): string
    {
        if (! config('cashier.secret')) {
            throw new RuntimeException('Stripe is not configured in this environment.');
        }

        if ($user->stripe_id) {
            return $user->stripe_id;
        }

        $stripe = Cashier::stripe();

        $existing = $stripe->customers->all(['email' => $user->email, 'limit' => 1])->data[0] ?? null;

        $customer = $existing ?: $stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => (string) $user->id],
        ]);

        $user->forceFill(['stripe_id' => $customer->id])->save();

        return $customer->id;
    }
}
