<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'capacity', 'capacity_seated', 'area', 'amenities',
        'buffer_minutes', 'min_hours', 'active', 'seo_title', 'seo_description', 'og_image', 'meta_alt',
    ];

    protected $casts = [
        'amenities' => 'array',
        'capacity' => 'integer',
        'capacity_seated' => 'integer',
        'buffer_minutes' => 'integer',
        'min_hours' => 'integer',
        'active' => 'boolean',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(HallImage::class)->orderBy('order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(HallFeature::class);
    }

    public function hours(): HasMany
    {
        return $this->hasMany(HallHour::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(RoomRate::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'bookingable_id')->where('bookingable_type', self::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function rateFor(\Carbon\CarbonInterface $date): ?RoomRate
    {
        $dayType = BankHoliday::isBankHoliday($date) ? 'BankHoliday' : $date->format('D');

        return $this->rates->firstWhere('day_type', $dayType);
    }

    public function openingHoursFor(\Carbon\CarbonInterface $date): ?HallHour
    {
        return $this->hours->firstWhere('weekday', (int) $date->dayOfWeek);
    }

    public function heroImage(): ?HallImage
    {
        return $this->images->first();
    }
}