<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallHour extends Model
{
    protected $fillable = ['hall_id', 'weekday', 'opens_at', 'closes_at'];

    protected $casts = ['weekday' => 'integer'];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }
}
