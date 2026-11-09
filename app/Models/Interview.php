<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Interview extends Model
{
    public const ELIGIBLE = 'eligible';
    public const INELIGIBLE = 'ineligible';

    protected $fillable = ['application_id', 'scheduled_at', 'location', 'notes', 'recommendation'];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'interview_attendees')->withTimestamps();
    }
}