<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['name', 'commodity_type', 'default_uom_id', 'status', 'version'];

    public function defaultUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'default_uom_id');
    }
}
