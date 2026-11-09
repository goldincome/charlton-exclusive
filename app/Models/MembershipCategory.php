<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'benefits', 'entrance_fee', 'annual_fee',
        'monthly_fee', 'min_sections', 'nominee_limit', 'active', 'seo_title', 'seo_description',
    ];

    protected $casts = [
        'benefits' => 'array',
        'entrance_fee' => 'integer',
        'annual_fee' => 'integer',
        'monthly_fee' => 'integer',
        'min_sections' => 'integer',
        'nominee_limit' => 'integer',
        'active' => 'boolean',
    ];

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'category_section');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function monthlyAvailable(): bool
    {
        return ! is_null($this->monthly_fee) && $this->monthly_fee > 0;
    }
}