<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryPhoto extends Model
{
    public const PUBLIC = 'public';
    public const MEMBERS_ONLY = 'members_only';

    protected $fillable = ['gallery_album_id', 'title', 'path', 'thumbnail_path', 'visibility', 'alt_text', 'order'];
    protected $casts = ['order' => 'integer'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::PUBLIC);
    }

    public function isMembersOnly(): bool
    {
        return $this->visibility === self::MEMBERS_ONLY;
    }
}