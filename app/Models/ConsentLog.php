<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLog extends Model
{
    protected $fillable = ['user_id', 'session_id', 'categories', 'ip_address'];
    protected $casts = ['categories' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}