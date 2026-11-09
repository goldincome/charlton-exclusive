<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'user_id', 'stripe_payment_intent_id', 'amount',
        'currency', 'method', 'status', 'paid_at',
    ];

    protected $casts = ['amount' => 'integer', 'paid_at' => 'datetime'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function refundedTotal(): int
    {
        return (int) $this->refunds()->where('status', 'succeeded')->sum('amount');
    }
}