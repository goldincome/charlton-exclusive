<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'author_profile_id',
        'seo_title', 'seo_description', 'og_image', 'published_at',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(AuthorProfile::class, 'author_profile_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
