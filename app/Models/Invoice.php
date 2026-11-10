<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    public const UNPAID = 'unpaid';

    public const PAID = 'paid';

    public const REFUNDED = 'refunded';

    public const VOID = 'void';

    protected $fillable = [
        'number', 'user_id', 'invoiceable_type', 'invoiceable_id', 'issued_at',
        'subtotal', 'vat_amount', 'total', 'status',
        'stripe_invoice_id', 'stripe_hosted_invoice_url',
    ];

    protected $casts = [
        'issued_at' => 'date:Y-m-d',
        'subtotal' => 'integer',
        'vat_amount' => 'integer',
        'total' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function grossFormatted(): string
    {
        return '£'.number_format($this->total / 100, 2);
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }
}
