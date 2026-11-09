<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'bookable_item_id', 'section_id', 'name', 'unit_price', 'quantity',
        'original_amount', 'discount_amount', 'final_amount',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'original_amount' => 'integer',
        'discount_amount' => 'integer',
        'final_amount' => 'integer',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function item(): BelongsTo { return $this->belongsTo(BookableItem::class, 'bookable_item_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
}
