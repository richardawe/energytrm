<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    protected $fillable = [
        'settlement_number', 'invoice_id',
        'payment_amount', 'payment_date', 'fx_rate', 'bank_ref',
        'settlement_status', 'comments', 'created_by',
    ];

    protected $casts = [
        'payment_date'   => 'date',
        'payment_amount' => 'decimal:2',
        'fx_rate'        => 'decimal:6',
    ];

    public static function nextSettlementNumber(): string
    {
        $year = now()->year;
        $last = static::where('settlement_number', 'like', "SET-{$year}-%")->max('settlement_number');
        $seq  = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('SET-%d-%04d', $year, $seq);
    }

    public function invoice(): BelongsTo   { return $this->belongsTo(Invoice::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
