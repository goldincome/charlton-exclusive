<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['quote', 'author_name', 'author_context', 'consent_given', 'active', 'order'];
    protected $casts = ['consent_given' => 'boolean', 'active' => 'boolean', 'order' => 'integer'];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('active', true)->where('consent_given', true)->orderBy('order');
    }
}