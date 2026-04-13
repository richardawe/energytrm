<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerCommission extends Model
{
    protected $fillable = [
        'broker_id', 'name', 'commission_rate', 'rate_unit', 'currency_id',
        'payment_frequency', 'min_fee', 'max_fee', 'index_group', 'effective_date', 'is_default',
    ];
    protected $casts = [
        'is_default' => 'boolean',
        'effective_date' => 'date',
        'commission_rate' => 'decimal:6',
        'min_fee' => 'decimal:2',
        'max_fee' => 'decimal:2',
    ];

    public function broker(): BelongsTo   { return $this->belongsTo(Broker::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
}
