<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugHistory extends Model
{
    protected $table = 'slug_history';
    protected $fillable = ['model_type', 'model_id', 'old_slug'];
}