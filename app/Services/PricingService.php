<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\Booking;
use App\Models\Event;
use App\Models\FoodOrder;
use App\Models\Hall;
use App\Models\RoomRate;
use App\Models\User;
use App\Models\VatRate;
use Carbon\CarbonInterface;

class PricingService
{
    /**
     * Apply a percentage or flat discount to an amount in pence.
     */
    public function applyDiscount(int $amount, string $type, float $value): float
    {
        if ($amount <= 0 || $value <= 0) {
            return (float) $amount;
        }

        if ($type === 'flat') {
            return max(0, $amount - ($value * 100));
        }

        return $amount - ($amount * ($value / 100));
    }

    /**
     * Hourly hall rate for a user, including member discount when applicable.
     */
    public function hallHourlyRate(Hall $hall, CarbonInterface $date, bool $isMemberFinanciallyActive): ?int
    {
        $rate = $hall->rateFor($date);

        if (! $rate) {
            return null;
        }

        if ($isMemberFinanciallyActive) {
            return (int) round($this->applyDiscount(
                $rate->standard_price_per_hour,
                $rate->member_discount_type,
                (float) $rate->member_discount_value
            ));
        }

        return $rate->standard_price_per_hour;
    }

    /**
     * Total hall booking price. Minimum 3 hours enforced by BookingService; price uses
     * half-hour increments.
     */
    public function hallTotal(Hall $hall, CarbonInterface $start, CarbonInterface $end, bool $isMemberFinanciallyActive): int
    {
        $hours = $start->diffInMinutes($end) / 60;
        $hourly = $this->hallHourlyRate($hall, $start, $isMemberFinanciallyActive);

        if ($hourly === null) {
            return 0;
        }

        return (int) round($hourly * $hours);
    }

    public function tablePrice(int $standardPrice, string $discountType, float $discountValue, bool $isMemberFinanciallyActive): int
    {
        if (! $isMemberFinanciallyActive) {
            return $standardPrice;
        }

        return (int) round($this->applyDiscount($standardPrice, $discountType, $discountValue));
    }

    public function eventTicketPrice(Event $event, bool $isMemberFinanciallyActive, int $tickets = 1): int
    {
        $unit = $event->price_per_ticket;

        if ($isMemberFinanciallyActive) {
            $unit = (int) round($this->applyDiscount(
                $event->price_per_ticket,
                $event->member_discount_type,
                (float) $event->member_discount_value
            ));
        }

        return $unit * max(1, $tickets);
    }

    /**
     * Food order total after the hall-linked food discount (config percent|flat).
     * Beverage items flagged discount_exempt are never discounted.
     */
    public function foodOrderTotal(FoodOrder $order, ?Booking $linkedHallBooking): int
    {
        $total = 0;
        $discountType = AppConfig::get('hall_food_discount_type', 'percent');
        $discountValue = (float) AppConfig::get('hall_food_discount_value', 20);

        $order->loadMissing('items.menuItem');

        foreach ($order->items as $item) {
            $line = $item->unit_price * $item->quantity;

            $applies = $linkedHallBooking
                && $linkedHallBooking->status === Booking::CONFIRMED
                && ! $item->menuItem?->discount_exempt;

            if ($applies) {
                $line = (int) round($this->applyDiscount($line, $discountType, $discountValue));
            }

            $total += $line;
        }

        return $total;
    }

    /**
     * Split a VAT-inclusive gross amount into net and VAT at the configured rate.
     *
     * @return array{net:int, vat:int, gross:int, rate:float}
     */
    public function vatInclusiveSplit(int $gross, string $productType): array
    {
        $rate = VatRate::rateFor($productType);

        if ($rate <= 0) {
            return ['net' => $gross, 'vat' => 0, 'gross' => $gross, 'rate' => 0.0];
        }

        $net = (int) round($gross / (1 + ($rate / 100)));
        $vat = $gross - $net;

        return ['net' => $net, 'vat' => $vat, 'gross' => $gross, 'rate' => $rate];
    }

    /**
     * Membership line items are outside the scope of VAT.
     *
     * @return array{net:int, vat:int, gross:int, rate:float}
     */
    public function membershipSplit(int $gross): array
    {
        return ['net' => $gross, 'vat' => 0, 'gross' => $gross, 'rate' => 0.0];
    }

    public function formatPence(int $pence): string
    {
        return '£'.number_format($pence / 100, 2);
    }
}