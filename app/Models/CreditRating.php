<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditRating extends Model
{
    protected $fillable = [
        'party_id', 'source', 'rating', 'effective_date', 'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }
}
