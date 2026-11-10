<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamProfile extends Model
{
    protected $fillable = ['name', 'role', 'bio', 'photo_path', 'consent_given', 'order'];

    protected $casts = ['consent_given' => 'boolean', 'order' => 'integer'];
}
