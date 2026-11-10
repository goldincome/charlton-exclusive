<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class BankHoliday extends Model
{
    protected $fillable = ['date', 'name'];

    protected $casts = ['date' => 'date:Y-m-d'];

    public static function isBankHoliday(CarbonInterface $date): bool
    {
        return static::query()->whereDate('date', $date->toDateString())->exists();
    }
}
