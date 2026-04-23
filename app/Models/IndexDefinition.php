<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndexDefinition extends Model
{
    protected $fillable = [
        'version', 'index_name', 'label', 'market', 'index_group', 'index_subgroup', 'format',
        'class', 'base_currency_id', 'uom_id', 'status', 'version_status', 'rec_status',
        'delivery_unit', 'date_sequence', 'payment_convention', 'coverage_end_date',
        'interpolation', 'inheritance', 'discount_index_id', 'reference_source',
        'projection_method', 'day_start_time', 'holiday_schedule', 'index_type',
    ];

    protected $casts = [
        'inheritance'       => 'boolean',
        'coverage_end_date' => 'date',
        'discount_index_id' => 'integer',
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

    public function discountIndex(): BelongsTo
    {
        return $this->belongsTo(IndexDefinition::class, 'discount_index_id');
    }
}
