<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VarConfiguration extends Model
{
    protected $fillable = [
        'name',
        'lookback_period_days',
        'holding_period_days',
        'var_method',
        'confidence_level',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'confidence_level' => 'decimal:4',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
