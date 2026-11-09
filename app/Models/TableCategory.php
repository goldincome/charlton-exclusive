<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableCategory extends Model
{
    protected $fillable = ['name', 'standard_price', 'member_discount_type', 'member_discount_value', 'active'];
    protected $casts = ['standard_price' => 'integer', 'member_discount_value' => 'integer', 'active' => 'boolean'];

    public function tables(): HasMany
    {
        return $this->hasMany(DiningTable::class);
    }
}