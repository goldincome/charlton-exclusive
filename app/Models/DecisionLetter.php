<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionLetter extends Model
{
    protected $fillable = ['application_id', 'path', 'reason', 'sent_at'];
    protected $casts = ['sent_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}