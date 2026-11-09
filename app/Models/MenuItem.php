<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    public const ALLERGENS = [
        'celery', 'cereals_containing_gluten', 'crustaceans', 'eggs', 'fish', 'lupin',
        'milk', 'molluscs', 'mustard', 'nuts', 'peanuts', 'sesame', 'soya', 'sulphur_dioxide',
    ];

    protected $fillable = ['menu_category_id', 'name', 'description', 'price', 'image_path', 'discount_exempt', 'active'];
    protected $casts = ['price' => 'integer', 'discount_exempt' => 'boolean', 'active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function allergenFlags(): HasMany
    {
        return $this->hasMany(MenuItemAllergen::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}