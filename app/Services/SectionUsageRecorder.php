<?php

namespace App\Services;

use App\Models\BookableItem;
use App\Models\Booking;
use App\Models\DiningTable;
use App\Models\Event;
use App\Models\FoodOrder;
use App\Models\Hall;
use App\Models\PlanSectionDiscount;
use App\Models\Section;
use App\Models\SectionUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SectionUsageRecorder
{
    public function __construct(private PricingService $pricing)
    {
    }

    public function resolveForBooking(Booking $booking): array
    {
        $type = $booking->bookingable_type;
        $model = $booking->bookingable;
        if ($type === Hall::class || $model instanceof Hall) {
            $section = Section::query()->where('slug', 'meeting-rooms')->first();
            return [$section, $this->bookableFor($section, $model ?: Hall::query()->find($booking->bookingable_id), 'hour')];
        }
        if ($type === Event::class || $model instanceof Event) {
            $section = Section::query()->where('slug', 'events')->first();
            return [$section, $this->bookableFor($section, $model ?: Event::query()->find($booking->bookingable_id), 'flat')];
        }
        if ($type === DiningTable::class || $model instanceof DiningTable) {
            $section = Section::query()->where('slug', 'dining')->first();
            return [$section, $this->bookableFor($section, $model ?: DiningTable::query()->find($booking->bookingable_id), 'flat')];
        }
        return [null, null];
    }

    public function quoteForBooking(User $user, Booking $booking, int $standardAmountPence): array
    {
        [$section, $item] = $this->resolveForBooking($booking);
        $planId = $user->membership?->plan_id ?: $user->membership?->membership_category_id;
        $result = [
            'section_id' => $section?->id,
            'bookable_item_id' => $item?->id,
            'plan_id' => $planId,
            'original_amount' => $standardAmountPence,
            'final_amount' => $standardAmountPence,
            'discount_amount' => 0,
            'discount_type' => null,
            'discount_value' => 0,
        ];

        if (! $section) {
            return $result;
        }
        if ($section->isMembersOnly() && ! $user->hasMemberAccess()) {
            return $result; // access enforced elsewhere; keep price calc safe
        }
        if (! $planId) {
            return $result;
        }
        // Only yearly paying members receive discounts.
        if (! $user->isAnnualMember()) {
            return $result;
        }
        $rule = PlanSectionDiscount::query()->where('plan_id', $planId)->where('section_id', $section->id)->where('is_active', true)->first();
        if (! $rule) {
            return $result;
        }

        $type = $this->pricing->normalizeDiscountType($rule->type);
        $value = (int) $rule->value;
        $final = $this->pricing->applyDiscount($standardAmountPence, $type, $value);
        $discount = max(0, $standardAmountPence - $final);
        $result['final_amount'] = $final;
        $result['discount_amount'] = $discount;
        $result['discount_type'] = $discount > 0 ? $type : null;
        $result['discount_value'] = $discount > 0 ? $value : 0;
        return $result;
    }

    public function recordBooking(Booking $booking): ?SectionUsage
    {
        $booking->loadMissing(['user.membership', 'bookingable']);
        [$section] = $this->resolveForBooking($booking);
        if (! $section || ! $booking->user) {
            return null;
        }
        $original = (int) ($booking->original_amount ?? $booking->price_locked ?? 0);
        $final = (int) ($booking->final_amount ?? $booking->price_locked ?? $original);
        $discount = max(0, $original - $final);

        return SectionUsage::query()->updateOrCreate(
            ['usable_type' => Booking::class, 'usable_id' => $booking->id, 'section_id' => $section->id],
            [
                'user_id' => $booking->user_id,
                'plan_id' => $booking->plan_id ?: ($booking->user->membership?->plan_id ?: $booking->user->membership?->membership_category_id),
                'original_amount' => $original,
                'discount_amount' => (int) ($booking->discount_amount ?: $discount),
                'final_amount' => $final,
                'discount_type' => $booking->discount_type,
                'discount_value' => (int) ($booking->discount_value ?? 0),
                'used_at' => $booking->confirmed_at ?: $booking->checked_in_at ?: now(),
                'metadata' => ['booking_reference' => $booking->reference, 'bookingable_type' => $booking->bookingable_type],
            ]
        );
    }

    public function recordFoodOrder(FoodOrder $order, ?array $quote = null): ?SectionUsage
    {
        $section = Section::query()->where('slug', 'dining')->first();
        if (! $section) return null;
        $order->loadMissing('user.membership');
        $original = (int) ($quote['original_amount'] ?? $order->price_locked ?? 0);
        $final = (int) ($quote['final_amount'] ?? $order->price_locked ?? 0);
        return SectionUsage::query()->updateOrCreate(
            ['usable_type' => FoodOrder::class, 'usable_id' => $order->id, 'section_id' => $section->id],
            [
                'user_id' => $order->user_id,
                'plan_id' => $order->user?->membership?->plan_id ?: $order->user?->membership?->membership_category_id,
                'original_amount' => $original,
                'discount_amount' => (int) ($quote['discount_amount'] ?? max(0, $original - $final)),
                'final_amount' => $final,
                'discount_type' => $quote['discount_type'] ?? null,
                'discount_value' => (int) ($quote['discount_value'] ?? 0),
                'used_at' => now(),
                'metadata' => ['reference' => $order->reference ?? null],
            ]
        );
    }

    private function bookableFor(?Section $section, ?Model $source, string $pricingUnit): ?BookableItem
    {
        if (! $section || ! $source) return null;
        $price = 0;
        if ($source instanceof Event) $price = (int) $source->price_per_ticket;
        $name = $source->name ?? ($source->number ?? null) ?? (class_basename($source).' #'.$source->getKey());
        if ($source instanceof \App\Models\DiningTable) {
            $name = 'Table '.($source->number ?? $source->getKey());
        }

        return BookableItem::query()->firstOrCreate(
            ['source_type' => $source::class, 'source_id' => $source->getKey()],
            [
                'section_id' => $section->id,
                'name' => $name,
                'description' => $source->description ?? null,
                'price' => $price,
                'pricing_unit' => $pricingUnit,
                'capacity' => $source->capacity ?? null,
                'is_active' => (bool) ($source->active ?? true),
                'metadata' => ['mapped' => true],
            ]
        );
    }
}
