<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    public const ANNUAL = 'annual';
    public const MONTHLY = 'monthly';

    public const PENDING = 'pending';
    public const ACTIVE = 'active';
    public const SUSPENDED = 'suspended';
    public const TERMINATED = 'terminated';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id', 'membership_category_id', 'billing_frequency', 'status',
        'starts_at', 'renews_at', 'ends_at', 'stripe_subscription_id',
    ];

    protected $casts = [
        'starts_at' => 'date:Y-m-d',
        'renews_at' => 'date:Y-m-d',
        'ends_at' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function persons(): HasMany
    {
        return $this->hasMany(MembershipPerson::class);
    }
}