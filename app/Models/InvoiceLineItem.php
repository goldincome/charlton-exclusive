<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLineItem extends Model
{
    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit_price', 'net', 'vat_rate', 'vat', 'gross'];
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'net' => 'integer',
        'vat_rate' => 'decimal:2',
        'vat' => 'integer',
        'gross' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}