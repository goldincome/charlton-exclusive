<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'address_line_1', 'address_line_2', 'city',
        'postcode', 'date_of_birth', 'committee_member',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'committee_member' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}