<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TradingLocation extends Model
{
    protected $fillable = ['name', 'city', 'country', 'timezone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_trading_locations')->withPivot('is_default');
    }
}
