<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocument extends Model
{
    protected $fillable = ['key', 'title', 'version', 'body', 'effective_at', 'active'];

    protected $casts = ['effective_at' => 'date:Y-m-d', 'active' => 'boolean'];

    public function acceptances(): HasMany
    {
        return $this->hasMany(LegalAcceptance::class);
    }
}
