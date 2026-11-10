<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\Booking;
use App\Models\Event;
use App\Models\FoodOrder;
use App\Models\Hall;
use App\Models\PlanSectionDiscount;
use App\Models\Section;
use App\Models\User;
use App\Models\VatRate;
use Carbon\CarbonInterface;

class PricingService
{
    /**
     * Apply a percentage or fixed discount to an amount in pence.
     *
     * Canonical money/discount representation (Phase 1):
     * - $amountPence: integer pence
     * - percent: $value is integer percent points (0-100)
     * - fixed/flat: $value is integer pence
     *
     * Returns the final amount in pence (never negative).
     */
    public function applyDiscount(int $amountPence, string $type, int $value): int
    {
        if ($amountPence <= 0 || $value <= 0) {
            return max(0, $amountPence);
        }

        $type = $this->normalizeDiscountType($type);

        if ($type === 'fixed') {
            $discount = min($value, $amountPence);

            return $amountPence - $discount;
        }

        $value = min(100, $value);
        $discount = (int) round($amountPence * ($value / 100));
        $discount = min($discount, $amountPence);

        return $amountPence - $discount;
    }

    public function normalizeDiscountType(?string $type): string
    {
        return in_array($type, ['fixed', 'flat'], true) ? 'fixed' : 'percent';
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
            return $this->applyDiscount(
                (int) $rate->standard_price_per_hour,
                (string) $rate->member_discount_type,
                (int) $rate->member_discount_value
            );
        }

        return (int) $rate->standard_price_per_hour;
    }

    /**
     * Total hall booking price. Minimum 3 hours enforced by BookingService; price uses
     * half-hour increments.
     */
    /**
     * @return array{original_amount:int, final_amount:int, discount_amount:int, discount_type:?string, discount_value:int, hourly_rate:?int}
     */
    public function hallQuote(Hall $hall, CarbonInterface $start, CarbonInterface $end, bool $isMemberFinanciallyActive): array
    {
        $hours = $start->diffInMinutes($end) / 60;
        $rate = $hall->rateFor($start);

        if (! $rate) {
            return [
                'original_amount' => 0,
                'final_amount' => 0,
                'discount_amount' => 0,
                'discount_type' => null,
                'discount_value' => 0,
                'hourly_rate' => null,
            ];
        }

        $originalHourly = (int) $rate->standard_price_per_hour;
        $originalAmount = (int) round($originalHourly * $hours);
        $discountType = $this->normalizeDiscountType((string) $rate->member_discount_type);
        $discountValue = (int) $rate->member_discount_value;

        if (! $isMemberFinanciallyActive) {
            return [
                'original_amount' => $originalAmount,
                'final_amount' => $originalAmount,
                'discount_amount' => 0,
                'discount_type' => null,
                'discount_value' => 0,
                'hourly_rate' => $originalHourly,
            ];
        }

        $finalHourly = $this->applyDiscount($originalHourly, $discountType, $discountValue);
        $finalAmount = (int) round($finalHourly * $hours);
        $discountAmount = max(0, $originalAmount - $finalAmount);

        return [
            'original_amount' => $originalAmount,
            'final_amount' => $finalAmount,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountAmount > 0 ? $discountType : null,
            'discount_value' => $discountAmount > 0 ? $discountValue : 0,
            'hourly_rate' => $finalHourly,
        ];
    }

    public function hallTotal(Hall $hall, CarbonInterface $start, CarbonInterface $end, bool $isMemberFinanciallyActive): int
    {
        return $this->hallQuote($hall, $start, $end, $isMemberFinanciallyActive)['final_amount'];
    }

    public function tablePrice(int $standardPrice, string $discountType, int $discountValue, bool $isMemberFinanciallyActive): int
    {
        if (! $isMemberFinanciallyActive) {
            return $standardPrice;
        }

        return $this->applyDiscount($standardPrice, $discountType, $discountValue);
    }

    /**
     * @return array{original_amount:int, final_amount:int, discount_amount:int, discount_type:?string, discount_value:int}
     */
    public function eventQuote(Event $event, bool $isMemberFinanciallyActive, int $tickets = 1): array
    {
        $tickets = max(1, $tickets);
        $unit = (int) $event->price_per_ticket;
        $originalAmount = $unit * $tickets;
        $discountType = $this->normalizeDiscountType((string) $event->member_discount_type);
        $discountValue = (int) $event->member_discount_value;

        if (! $isMemberFinanciallyActive) {
            return [
                'original_amount' => $originalAmount,
                'final_amount' => $originalAmount,
                'discount_amount' => 0,
                'discount_type' => null,
                'discount_value' => 0,
            ];
        }

        $finalUnit = $this->applyDiscount($unit, $discountType, $discountValue);
        $finalAmount = $finalUnit * $tickets;
        $discountAmount = max(0, $originalAmount - $finalAmount);

        return [
            'original_amount' => $originalAmount,
            'final_amount' => $finalAmount,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountAmount > 0 ? $discountType : null,
            'discount_value' => $discountAmount > 0 ? $discountValue : 0,
        ];
    }

    public function eventTicketPrice(Event $event, bool $isMemberFinanciallyActive, int $tickets = 1): int
    {
        return $this->eventQuote($event, $isMemberFinanciallyActive, $tickets)['final_amount'];
    }

    /**
     * Food order total after the hall-linked food discount (config percent|flat).
     * Beverage items flagged discount_exempt are never discounted.
     */
    /**
     * @return array{original_amount:int, final_amount:int, discount_amount:int, discount_type:?string, discount_value:int}
     */
    public function foodOrderQuote(FoodOrder $order, ?Booking $linkedHallBooking): array
    {
        $original = 0;
        $legacyFinal = 0;
        $discountType = (string) AppConfig::get('hall_food_discount_type', 'percent');
        $discountValue = (int) AppConfig::get('hall_food_discount_value', 20);

        $order->loadMissing(['items.menuItem', 'user.membership']);

        foreach ($order->items as $item) {
            $line = (int) $item->unit_price * (int) $item->quantity;
            $original += $line;
            $discountedLine = $line;
            $applies = $linkedHallBooking
                && $linkedHallBooking->status === Booking::CONFIRMED
                && ! $item->menuItem?->discount_exempt;
            if ($applies) {
                $discountedLine = $this->applyDiscount($line, $discountType, $discountValue);
            }
            $legacyFinal += $discountedLine;
        }

        // Section plan discount (Dining) on use: authoritative when a rule exists for the member's plan.
        $user = $order->user;
        $section = Section::query()->where('slug', 'dining')->first();
        $planId = $user?->membership?->plan_id ?: $user?->membership?->membership_category_id;
        if ($user && $section && $planId) {
            $rule = PlanSectionDiscount::query()
                ->where('plan_id', $planId)->where('section_id', $section->id)->where('is_active', true)->first();
            if ($rule && $user->isAnnualMember() && ($section->isOpenToPublic() || $user->hasMemberAccess())) {
                $type = $this->normalizeDiscountType($rule->type);
                $value = (int) $rule->value;
                $final = $this->applyDiscount($original, $type, $value);
                $amount = max(0, $original - $final);
                if ($amount >= max(0, $original - $legacyFinal)) {
                    return [
                        'original_amount' => $original,
                        'final_amount' => $final,
                        'discount_amount' => $amount,
                        'discount_type' => $amount > 0 ? $type : null,
                        'discount_value' => $amount > 0 ? $value : 0,
                    ];
                }
            }
        }

        $legacyAmount = max(0, $original - $legacyFinal);

        return [
            'original_amount' => $original,
            'final_amount' => $legacyFinal,
            'discount_amount' => $legacyAmount,
            'discount_type' => $legacyAmount > 0 ? $this->normalizeDiscountType($discountType) : null,
            'discount_value' => $legacyAmount > 0 ? $discountValue : 0,
        ];
    }

    public function foodOrderTotal(FoodOrder $order, ?Booking $linkedHallBooking): int
    {
        return $this->foodOrderQuote($order, $linkedHallBooking)['final_amount'];
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
