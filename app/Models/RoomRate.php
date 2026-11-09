<?php

namespace App\Models;

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
        'member_discount_value' => 'decimal:2',
    ];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function memberPricePerHour(): int
    {
        return (int) round(app(\App\Services\PricingService::class)->applyDiscount(
            $this->standard_price_per_hour,
            $this->member_discount_type,
            (float) $this->member_discount_value
        ));
    }
}