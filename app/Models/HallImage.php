<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallImage extends Model
{
    protected $fillable = ['hall_id', 'path', 'alt_text', 'order'];

    protected $casts = ['order' => 'integer'];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }
}
