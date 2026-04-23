<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyAddress extends Model
{
    protected $fillable = [
        'party_id', 'is_default', 'address_type',
        'address_line1', 'address_line2', 'city', 'state', 'country',
        'phone', 'description', 'contact_user_id', 'effective_date',
    ];

    protected $casts = [
        'is_default'     => 'boolean',
        'effective_date' => 'date',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function contactUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'contact_user_id');
    }
}
