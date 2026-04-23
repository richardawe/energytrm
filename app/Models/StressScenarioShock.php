<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StressScenarioShock extends Model
{
    protected $fillable = ['stress_scenario_id', 'index_id', 'price_shock_pct'];

    protected $casts = ['price_shock_pct' => 'decimal:4'];

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(StressScenario::class, 'stress_scenario_id');
    }

    public function index(): BelongsTo
    {
        return $this->belongsTo(IndexDefinition::class, 'index_id');
    }
}
