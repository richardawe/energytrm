<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    protected $fillable = [
        'code', 'name', 'commodity_type', 'operator', 'country', 'status', 'version',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(PipelineZone::class);
    }

    public function scopeAuthorized($q)
    {
        return $q->where('status', 'Authorized');
    }
}
