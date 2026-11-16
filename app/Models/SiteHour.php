<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class SiteHour extends Model
{
    protected $fillable = [
        'weekday',
        'opens_at',
        'closes_at',
        'is_open',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'is_open' => 'boolean',
    ];

    public static function openingHoursFor(CarbonInterface $date): ?self
    {
        return static::query()->where('weekday', (int) $date->dayOfWeek)->first();
    }
}
