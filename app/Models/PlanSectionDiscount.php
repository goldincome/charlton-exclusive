<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanSectionDiscount extends Model
{
    protected $fillable = ['plan_id', 'section_id', 'type', 'value', 'is_active'];

    protected $casts = [
        'value' => 'integer', // percent points or fixed pence
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
