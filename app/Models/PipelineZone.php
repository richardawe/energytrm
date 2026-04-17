<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineZone extends Model
{
    protected $fillable = ['pipeline_id', 'zone_code', 'zone_name', 'status'];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(PipelineLocation::class, 'zone_id');
    }

    public function scopeAuthorized($q)
    {
        return $q->where('status', 'Authorized');
    }
}
