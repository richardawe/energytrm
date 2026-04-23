<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditWarningThreshold extends Model
{
    protected $fillable = [
        'party_id',
        'warning_threshold_pct',
        'breach_threshold_pct',
        'is_active',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'warning_threshold_pct' => 'decimal:2',
        'breach_threshold_pct'  => 'decimal:2',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }
}
