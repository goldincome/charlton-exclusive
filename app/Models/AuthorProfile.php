<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorProfile extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'bio', 'photo_path'];

    public function posts(): HasMany
    {
        return $this->hasMany(JournalPost::class);
    }
}