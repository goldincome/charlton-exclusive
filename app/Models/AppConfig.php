<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppConfig extends Model
{
    protected $table = 'app_config';
    protected $fillable = ['key', 'value', 'type', 'description'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('app_config.all'));
        static::deleted(fn () => Cache::forget('app_config.all'));
    }

    public static function all_cached(): array
    {
        return Cache::rememberForever('app_config.all', function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_cached()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'type' => $type, 'description' => $description]
        );
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key);
        return is_null($value) ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = static::get($key);
        return is_null($value) ? $default : (int) $value;
    }
}