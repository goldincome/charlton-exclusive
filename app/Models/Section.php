<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Section extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'fee', 'recurring_annually', 'active', 'seo_title', 'seo_description', 'og_image', 'icon'];

    protected $casts = [
        'fee' => 'integer',
        'recurring_annually' => 'boolean',
        'active' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(MembershipCategory::class, 'category_section');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}