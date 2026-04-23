<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementInstruction extends Model
{
    protected $fillable = [
        'si_number', 'party_id', 'si_name', 'settler', 'status',
        'advice', 'payment_method', 'account_name', 'description',
        'start_date', 'end_date', 'is_dvp', 'link_settle_id',
        'version', 'created_by',
    ];

    protected $casts = [
        'is_dvp'     => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public static function nextSiNumber(): string
    {
        $year = now()->year;
        $last = static::where('si_number', 'like', "SI-{$year}-%")
            ->orderByDesc('si_number')
            ->value('si_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return sprintf('SI-%d-%04d', $year, $seq);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function linkedSettlement(): BelongsTo
    {
        return $this->belongsTo(SettlementInstruction::class, 'link_settle_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
