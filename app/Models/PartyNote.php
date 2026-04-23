<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyNote extends Model
{
    protected $fillable = [
        'party_id', 'note_type', 'title', 'body',
        'note_date', 'version', 'created_by',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
