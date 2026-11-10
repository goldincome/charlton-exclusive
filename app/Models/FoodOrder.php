<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FoodOrder extends Model
{
    public const REQUESTED = 'requested';

    public const APPROVED = 'approved';

    public const CONFIRMED = 'confirmed';

    public const FULFILLED = 'fulfilled';

    public const REJECTED = 'rejected';

    public const EXPIRED = 'expired';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id', 'booking_id', 'type', 'status', 'requested_for', 'dietary_notes',
        'location', 'contact_phone', 'price_locked', 'reference', 'approved_at', 'expires_at',
    ];

    protected $casts = [
        'requested_for' => 'datetime',
        'price_locked' => 'integer',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->reference ??= 'FO-'.strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FoodOrderItem::class);
    }
}
