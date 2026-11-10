<?php

namespace App\Models;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRate extends Model
{
    protected $fillable = [
        'hall_id', 'day_type', 'standard_price_per_hour',
        'member_discount_type', 'member_discount_value',
    ];

    protected $casts = [
        'standard_price_per_hour' => 'integer',
        'member_discount_value' => 'integer', // percent points or pence (fixed)
    ];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function memberPricePerHour(): int
    {
        return app(PricingService::class)->applyDiscount(
            (int) $this->standard_price_per_hour,
            (string) $this->member_discount_type,
            (int) $this->member_discount_value
        );
    }
}
