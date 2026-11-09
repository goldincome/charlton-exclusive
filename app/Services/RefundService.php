<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\FoodOrder;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Carbon;

class RefundService
{
    /**
     * Refund percentage for a booking according to the configured tier table.
     * Club-initiated cancellation is always 100%.
     */
    public function bookingRefundPercent(Booking $booking, bool $clubInitiated = false): int
    {
        if ($clubInitiated) {
            return 100;
        }

        $hours = now()->diffInHours($booking->start_time, false);
        $type = $booking->bookingable_type;

        if ($type === \App\Models\Hall::class) {
            if ($hours >= 24 * 7) {
                return 100;
            }

            if ($hours >= 48) {
                return 50;
            }

            return 0;
        }

        if ($type === \App\Models\DiningTable::class) {
            return $hours >= 24 ? 100 : 0;
        }

        if ($type === \App\Models\Event::class) {
            return $hours >= 24 * 7 ? 100 : 0;
        }

        return 0;
    }

    public function foodOrderRefundPercent(FoodOrder $order): int
    {
        $hours = now()->diffInHours($order->requested_for, false);

        if ($order->type === 'personal') {
            return $hours >= 24 ? 100 : 0;
        }

        if ($hours >= 72) {
            return 100;
        }

        if ($hours >= 24) {
            return 50;
        }

        return 0;
    }

    public function refundAmount(Payment $payment, int $percent): int
    {
        return (int) round($payment->amount * ($percent / 100));
    }

    /**
     * Record a refund (full or partial) with a mandatory reason, audited.
     */
    public function record(Payment $payment, int $amount, string $reason, User $admin, ?string $stripeRefundId = null): Refund
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be greater than zero.');
        }

        if ($amount > $payment->amount - $payment->refundedTotal()) {
            throw new \InvalidArgumentException('Refund exceeds the refundable balance.');
        }

        $refund = Refund::query()->create([
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'amount' => $amount,
            'reason' => $reason,
            'stripe_refund_id' => $stripeRefundId,
            'status' => $stripeRefundId ? 'succeeded' : 'pending',
        ]);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'refund.recorded',
            'auditable_type' => Refund::class,
            'auditable_id' => $refund->id,
            'new_values' => ['amount' => $amount, 'reason' => $reason],
            'ip_address' => request()->ip(),
        ]);

        return $refund;
    }
}