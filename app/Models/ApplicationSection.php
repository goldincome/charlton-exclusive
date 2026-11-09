<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSection extends Model
{
    protected $table = 'application_sections';
    protected $fillable = ['application_id', 'section_id'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}