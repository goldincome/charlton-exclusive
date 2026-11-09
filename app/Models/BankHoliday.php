<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankHoliday extends Model
{
    protected $fillable = ['date', 'name'];
    protected $casts = ['date' => 'date:Y-m-d'];

    public static function isBankHoliday(\Carbon\CarbonInterface $date): bool
    {
        return static::query()->whereDate('date', $date->toDateString())->exists();
    }
}