<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CheckIn extends Model
{
    protected $fillable = ['checkable_type', 'checkable_id', 'checked_in_by', 'checked_in_at'];
    protected $casts = ['checked_in_at' => 'datetime'];

    public function checkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}