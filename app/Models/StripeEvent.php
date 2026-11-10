<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeEvent extends Model
{
    protected $fillable = ['stripe_event_id', 'type', 'payload', 'processed_at'];

    protected $casts = ['processed_at' => 'datetime'];
}
