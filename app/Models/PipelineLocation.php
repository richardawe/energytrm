<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineLocation extends Model
{
    protected $fillable = ['zone_id', 'location_code', 'location_name', 'location_type', 'status'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(PipelineZone::class, 'zone_id');
    }

    public function scopeAuthorized($q)
    {
        return $q->where('status', 'Authorized');
    }
}
