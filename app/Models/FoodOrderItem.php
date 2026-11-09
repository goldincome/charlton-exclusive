<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodOrderItem extends Model
{
    protected $fillable = ['food_order_id', 'menu_item_id', 'quantity', 'unit_price'];
    protected $casts = ['quantity' => 'integer', 'unit_price' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}