<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'section_id', 'original_amount', 'discount_amount', 'final_amount',
        'discount_type', 'discount_value', 'status', 'notes', 'stripe_session_id', 'reference',
    ];

    protected $casts = [
        'original_amount' => 'integer',
        'discount_amount' => 'integer',
        'final_amount' => 'integer',
        'discount_value' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->reference ??= 'ORD-'.strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
