<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Model
{
    protected $fillable = ['name', 'short_name', 'broker_type', 'status', 'lei', 'is_regulated', 'version'];
    protected $casts = ['is_regulated' => 'boolean'];

    public function commissions(): HasMany
    {
        return $this->hasMany(BrokerCommission::class);
    }

    public function defaultCommission()
    {
        return $this->hasOne(BrokerCommission::class)->where('is_default', true);
    }
}
