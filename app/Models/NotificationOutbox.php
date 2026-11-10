<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';

    protected $fillable = ['user_id', 'to_email', 'subject', 'template', 'status', 'error', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
