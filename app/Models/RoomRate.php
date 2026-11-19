<?php

namespace App\Models;

use App\Services\PricingService;
use App\Services\SectionPricingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRate extends Model
{
    protected $fillable = [
        'hall_id', 'day_type', 'standard_price_per_hour',
    ];

    protected $casts = [
        'standard_price_per_hour' => 'integer',
    ];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function memberPricePerHour(): int
    {
        $standard = (int) $this->standard_price_per_hour;
        $meetingSection = Section::query()->where('slug', 'meeting-rooms')->first();
        if (! $meetingSection) {
            return $standard;
        }

        if (auth()->check() && auth()->user()->canGetMemberRates()) {
            $quote = app(SectionPricingService::class)->quote(auth()->user(), $meetingSection, $standard);

            return $quote['final_amount'];
        }

        $discountRule = PlanSectionDiscount::query()
            ->where('section_id', $meetingSection->id)
            ->where('is_active', true)
            ->first();

        if (! $discountRule) {
            return $standard;
        }

        $type = app(PricingService::class)->normalizeDiscountType($discountRule->type);
        $value = (int) $discountRule->value;

        return app(PricingService::class)->applyDiscount($standard, $type, $value);
    }
}
