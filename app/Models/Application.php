<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public const PAYMENT_RECEIVED = 'payment_received'; // paid; must nominate sponsors before activation

    public const AWAITING_SPONSORS_POST_PAYMENT = 'awaiting_sponsors_post_payment';

    public const ACTIVE = 'active';

    public const REJECTED = 'rejected';

    public const WITHDRAWN = 'withdrawn';

    public const EXPIRED = 'expired';

    protected $fillable = [
        'user_id', 'membership_category_id', 'plan_id', 'status', 'application_fee_active',
        'application_fee', 'reference', 'submitted_at', 'approved_at', 'rejected_at',
        'rejection_reason', 'expires_at', 'refusal_basis', 'is_reapplication',
        'profession', 'id_type', 'cv_path', 'id_path', 'application_method',
        'documents_verified_at', 'documents_verified_by', 'billing_frequency',
    ];

    protected $casts = [
        'application_fee_active' => 'boolean',
        'application_fee' => 'integer',
        'is_reapplication' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'documents_verified_at' => 'datetime',
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

    public function resolvedPlan(): ?Plan
    {
        if ($this->relationLoaded('plan') && $this->plan) {
            return $this->plan;
        }
        if ($this->plan_id) {
            return $this->plan ?: Plan::query()->find($this->plan_id);
        }
        if ($this->membership_category_id) {
            return Plan::query()->find($this->membership_category_id);
        }

        return null;
    }

    public function getCategoryAttribute()
    {
        return $this->resolvedPlan();
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

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function latestUnpaidInvoice(): ?Invoice
    {
        if ($this->relationLoaded('invoices')) {
            return $this->invoices->where('status', Invoice::UNPAID)->sortByDesc('id')->first();
        }

        return $this->invoices()->where('status', Invoice::UNPAID)->latest('id')->first();
    }

    public function acceptedSponsors(): HasMany
    {
        return $this->sponsorships()->where('status', Sponsorship::ACCEPTED);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [self::ACTIVE, self::REJECTED, self::WITHDRAWN, self::EXPIRED]);
    }

    public function isPendingReview(): bool
    {
        return $this->status === self::SUBMITTED;
    }

    public function awaitsPayment(): bool
    {
        return $this->status === self::FEES_NOTIFIED;
    }

    public function documentsVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'documents_verified_by');
    }

    public function hasVerifiedDocuments(): bool
    {
        return $this->documents_verified_at !== null;
    }

    public function billingFrequency(): string
    {
        return $this->billing_frequency === Membership::MONTHLY
            ? Membership::MONTHLY
            : Membership::ANNUAL;
    }
}
