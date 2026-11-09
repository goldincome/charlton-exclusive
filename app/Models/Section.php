<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Section extends Model
{
    public const TYPE_SLOT = 'slot';
    public const TYPE_CART = 'cart';
    public const ACCESS_OPEN = 'open';
    public const ACCESS_MEMBERS_ONLY = 'members_only';

    protected $fillable = [
        'name', 'slug', 'description', 'type', 'access_type', 'fee', 'recurring_annually', 'active',
        'seo_title', 'seo_description', 'og_image', 'icon',
    ];

    protected $casts = [
        'fee' => 'integer',
        'recurring_annually' => 'boolean',
        'active' => 'boolean',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_section_discounts', 'section_id', 'plan_id', 'id', 'id')
            ->withPivot(['type', 'value', 'is_active'])
            ->withTimestamps();
    }

    public function discounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlanSectionDiscount::class);
    }

    /** @deprecated Use plans() */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(MembershipCategory::class, 'category_section');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopePaused(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    public function isOpenToPublic(): bool
    {
        return $this->access_type === self::ACCESS_OPEN;
    }

    public function isMembersOnly(): bool
    {
        return $this->access_type === self::ACCESS_MEMBERS_ONLY;
    }
}