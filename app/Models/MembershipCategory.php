<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Compatibility shim during migration to plans (Phase 2).
 * Source of truth is now the `plans` table via Plan.
 */
class MembershipCategory extends Plan
{
    protected $table = 'plans';

    public function getAttribute($key)
    {
        if ($key === 'active') {
            return $this->attributes['is_active'] ?? parent::getAttribute('is_active');
        }

        return parent::getAttribute($key);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'plan_section_discounts', 'plan_id', 'section_id', 'id', 'id')
            ->withPivot(['type', 'value', 'is_active'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
