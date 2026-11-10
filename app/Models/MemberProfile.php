<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id', 'member_number', 'joined_at', 'sponsor_cap',
        'is_financial', 'is_suspended', 'suspended_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'joined_at' => 'date:Y-m-d',
        'sponsor_cap' => 'integer',
        'is_financial' => 'boolean',
        'is_suspended' => 'boolean',
        'suspended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function joinedYearsAgo(): ?float
    {
        return $this->joined_at ? $this->joined_at->diffInYears(now()) : null;
    }
}
