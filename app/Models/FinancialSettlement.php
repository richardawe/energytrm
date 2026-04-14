<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialSettlement extends Model
{
    protected $fillable = [
        'settlement_number', 'financial_trade_id',
        'settlement_type', 'period_start', 'period_end',
        'fixed_leg_amount', 'float_leg_amount', 'net_amount',
        'settlement_date', 'settlement_status',
        'fx_rate', 'bank_ref', 'comments', 'created_by',
    ];

    protected $casts = [
        'period_start'      => 'date',
        'period_end'        => 'date',
        'settlement_date'   => 'date',
        'fixed_leg_amount'  => 'decimal:2',
        'float_leg_amount'  => 'decimal:2',
        'net_amount'        => 'decimal:2',
        'fx_rate'           => 'decimal:6',
    ];

    public static function nextSettlementNumber(): string
    {
        $year = now()->year;
        $last = static::where('settlement_number', 'like', "FSET-{$year}-%")->max('settlement_number');
        $seq  = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('FSET-%d-%04d', $year, $seq);
    }

    public function financialTrade(): BelongsTo { return $this->belongsTo(FinancialTrade::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, 'created_by'); }
}
