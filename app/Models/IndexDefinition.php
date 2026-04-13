<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndexDefinition extends Model
{
    protected $fillable = [
        'version', 'index_name', 'market', 'index_group', 'format',
        'class', 'base_currency_id', 'uom_id', 'status', 'rec_status',
    ];

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function gridPoints(): HasMany
    {
        return $this->hasMany(IndexGridPoint::class, 'index_id')->orderBy('price_date');
    }

    public function latestPrice()
    {
        return $this->hasOne(IndexGridPoint::class, 'index_id')->latestOfMany('price_date');
    }
}
