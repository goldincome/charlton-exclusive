<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SectionUsage extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'section_id', 'usable_type', 'usable_id',
        'original_amount', 'discount_amount', 'final_amount', 'discount_type', 'discount_value', 'used_at', 'metadata',
    ];

    protected $casts = [
        'original_amount' => 'integer',
        'discount_amount' => 'integer',
        'final_amount' => 'integer',
        'discount_value' => 'integer',
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function usable(): MorphTo { return $this->morphTo(); }
}
