<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    public const DRAFT = 'draft';
    public const FEE_PENDING = 'fee_pending';
    public const SUBMITTED = 'submitted';
    public const AWAITING_SPONSORS = 'awaiting_sponsors';
    public const UNDER_REVIEW = 'under_review';
    public const INTERVIEW_SCHEDULED = 'interview_scheduled';
    public const INTERVIEW_COMPLETED = 'interview_completed';
    public const APPROVED = 'approved';
    public const FEES_NOTIFIED = 'fees_notified';
    public const PAYMENT_RECEIVED = 'payment_received';
    public const ACTIVE = 'active';
    public const REJECTED = 'rejected';
    public const WITHDRAWN = 'withdrawn';
    public const EXPIRED = 'expired';

    protected $fillable = [
        'user_id', 'membership_category_id', 'status', 'application_fee_active',
        'application_fee', 'reference', 'submitted_at', 'approved_at', 'rejected_at',
        'rejection_reason', 'expires_at', 'refusal_basis', 'is_reapplication',
    ];

    protected $casts = [
        'application_fee_active' => 'boolean',
        'application_fee' => 'integer',
        'is_reapplication' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'application_sections');
    }

    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function decisionLetter(): HasOne
    {
        return $this->hasOne(DecisionLetter::class);
    }

    public function acceptedSponsors(): HasMany
    {
        return $this->sponsorships()->where('status', Sponsorship::ACCEPTED);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [self::ACTIVE, self::REJECTED, self::WITHDRAWN, self::EXPIRED]);
    }
}