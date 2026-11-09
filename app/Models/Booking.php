<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const REQUESTED = 'requested';
    public const APPROVED = 'approved';
    public const CONFIRMED = 'confirmed';
    public const CHECKED_IN = 'checked_in';
    public const COMPLETED = 'completed';
    public const REJECTED = 'rejected';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';
    public const NO_SHOW = 'no_show';

    protected $fillable = [
        'user_id', 'bookingable_type', 'bookingable_id', 'status', 'start_time', 'end_time',
        'guests', 'price_locked', 'discount_applied', 'discount_type', 'notes',
        'approved_at', 'confirmed_at', 'checked_in_at', 'reference', 'cancelled_at',
        'cancellation_reason', 'rejected_at', 'rejection_reason', 'expires_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'guests' => 'integer',
        'price_locked' => 'integer',
        'discount_applied' => 'decimal:2',
        'approved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $booking) {
            $booking->reference ??= 'BK-'.strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookingable(): MorphTo
    {
        return $this->morphTo();
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'invoiceable_id')->where('invoiceable_type', self::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::REQUESTED, self::APPROVED, self::CONFIRMED, self::CHECKED_IN]);
    }
}