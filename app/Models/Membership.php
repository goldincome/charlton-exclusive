<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Membership extends Model
{
    public const ANNUAL = 'annual';

    public const MONTHLY = 'monthly';

    public const PENDING = 'pending';

    public const ACTIVE = 'active';

    public const PAST_DUE = 'past_due';

    public const SUSPENDED = 'suspended';

    public const TERMINATED = 'terminated';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id', 'membership_category_id', 'plan_id', 'billing_frequency', 'status',
        'starts_at', 'renews_at', 'ends_at', 'stripe_subscription_id',
        'cancelled_at', 'cancelled_by', 'cancellation_reason',
    ];

    protected $casts = [
        'starts_at' => 'date:Y-m-d',
        'renews_at' => 'date:Y-m-d',
        'ends_at' => 'date:Y-m-d',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /** @deprecated Use plan() */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function persons(): HasMany
    {
        return $this->hasMany(MembershipPerson::class);
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isAnnual(): bool
    {
        return $this->billing_frequency === self::ANNUAL;
    }

    public function isMonthly(): bool
    {
        return $this->billing_frequency === self::MONTHLY;
    }

    public function hasStripeSubscription(): bool
    {
        return ! empty($this->stripe_subscription_id);
    }
}
