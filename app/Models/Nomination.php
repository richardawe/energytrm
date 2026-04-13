<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nomination extends Model
{
    protected $fillable = [
        'nomination_number', 'trade_id',
        'gas_day', 'pipeline_operator', 'delivery_point',
        'nominated_volume', 'confirmed_volume', 'uom_id',
        'nomination_status', 'comments', 'created_by',
    ];

    protected $casts = [
        'gas_day'           => 'date',
        'nominated_volume'  => 'decimal:4',
        'confirmed_volume'  => 'decimal:4',
    ];

    public static function nextNominationNumber(): string
    {
        $year = now()->year;
        $last = static::where('nomination_number', 'like', "NOM-{$year}-%")->max('nomination_number');
        $seq  = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('NOM-%d-%04d', $year, $seq);
    }

    public function trade(): BelongsTo     { return $this->belongsTo(Trade::class); }
    public function uom(): BelongsTo       { return $this->belongsTo(Uom::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
