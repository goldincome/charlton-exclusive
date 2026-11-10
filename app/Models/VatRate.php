<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    protected $fillable = ['product_type', 'rate'];

    protected $casts = ['rate' => 'decimal:2'];

    public static function rateFor(string $productType): float
    {
        return (float) (static::query()->where('product_type', $productType)->value('rate') ?? 0);
    }
}
