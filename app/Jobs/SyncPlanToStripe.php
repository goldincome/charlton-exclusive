<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Services\StripePriceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPlanToStripe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $planId) {}

    public function handle(StripePriceService $prices): void
    {
        $plan = Plan::query()->find($this->planId);
        if (! $plan) {
            return;
        }

        if (! config('cashier.secret')) {
            $plan->update(['stripe_sync_status' => 'failed', 'stripe_sync_error' => 'Stripe is not configured.']);

            return;
        }

        $plan->update(['stripe_sync_status' => 'pending', 'stripe_sync_error' => null]);

        try {
            $prices->sync($plan);
        } catch (\Throwable $e) {
            Log::warning('SyncPlanToStripe failed', ['plan_id' => $plan->id, 'error' => $e->getMessage()]);
            $plan->update([
                'stripe_sync_status' => 'failed',
                'stripe_sync_error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
