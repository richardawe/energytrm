<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StressScenario extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function shocks(): HasMany
    {
        return $this->hasMany(StressScenarioShock::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
