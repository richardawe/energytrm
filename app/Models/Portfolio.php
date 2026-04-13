<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    protected $fillable = ['name', 'business_unit_id', 'is_restricted', 'status', 'version'];
    protected $casts = ['is_restricted' => 'boolean'];

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'business_unit_id');
    }
}
