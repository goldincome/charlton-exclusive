<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'benefits', 'entrance_fee', 'annual_fee', 'monthly_fee',
        'price', 'stripe_product_id', 'stripe_price_id', 'stripe_monthly_price_id', 'stripe_annual_price_id',
        'stripe_sync_status', 'stripe_sync_error', 'stripe_synced_at',
        'is_active', 'seo_title', 'seo_description',
    ];

    protected $casts = [
        'benefits' => 'array',
        'entrance_fee' => 'integer',
        'annual_fee' => 'integer',
        'monthly_fee' => 'integer',
        'price' => 'integer',
        'is_active' => 'boolean',
        'stripe_synced_at' => 'datetime',
    ];

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'plan_section_discounts', 'plan_id', 'section_id', 'id', 'id')
            ->withPivot(['type', 'value', 'is_active'])
            ->withTimestamps();
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(PlanSectionDiscount::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getActiveAttribute(): bool
    {
        return (bool) $this->is_active;
    }

    public function monthlyAvailable(): bool
    {
        return ! is_null($this->monthly_fee) && $this->monthly_fee > 0;
    }

    /**
     * The fee for a billing frequency (annual or monthly), in pence.
     */
    public function feeFor(string $frequency): int
    {
        return $frequency === Membership::MONTHLY
            ? (int) $this->monthly_fee
            : (int) $this->annual_fee;
    }

    /**
     * The cached Stripe price id for a billing frequency, if one has been synced.
     */
    public function stripePriceIdFor(string $frequency): ?string
    {
        if ($frequency === Membership::MONTHLY) {
            return $this->stripe_monthly_price_id;
        }

        return $this->stripe_annual_price_id ?: $this->stripe_price_id;
    }

    public function discountForSection(Section|int $section): ?PlanSectionDiscount
    {
        $sectionId = $section instanceof Section ? $section->id : $section;

        return $this->discounts()->where('section_id', $sectionId)->where('is_active', true)->first();
    }
}
