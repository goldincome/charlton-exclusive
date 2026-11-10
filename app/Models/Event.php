<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'starts_at', 'ends_at', 'capacity',
        'tickets_per_booking', 'price_per_ticket', 'member_discount_type',
        'member_discount_value', 'active', 'cancelled_at', 'seo_title',
        'seo_description', 'og_image', 'venue', 'members_only',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'capacity' => 'integer',
        'tickets_per_booking' => 'integer',
        'price_per_ticket' => 'integer',
        'member_discount_value' => 'integer', // percent points or pence (fixed)
        'active' => 'boolean',
        'cancelled_at' => 'datetime',
        'members_only' => 'boolean',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'bookingable_id')->where('bookingable_type', self::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(GalleryAlbum::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->whereNull('cancelled_at');
    }
}
