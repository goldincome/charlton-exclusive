<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AreaPage extends Model
{
    protected $fillable = ['name', 'slug', 'body', 'seo_title', 'seo_description', 'og_image', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
