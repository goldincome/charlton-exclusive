<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'event_id', 'seo_title', 'seo_description', 'og_image'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('order');
    }

    public function publicPhotos(): HasMany
    {
        return $this->photos()->where('visibility', GalleryPhoto::PUBLIC);
    }
}