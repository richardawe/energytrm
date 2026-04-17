<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class FinancialTrade extends Model
{
    protected $fillable = [
        'deal_number', 'transaction_number', 'instrument_number', 'version',
        'instrument_type', 'trade_status', 'trade_date',
        'internal_bu_id', 'portfolio_id', 'counterparty_id', 'currency_id',
        'product_id', 'buy_sell', 'pay_rec', 'broker_id', 'agreement_id', 'comments',
        'hedges_physical_trade_id',
        // Swap
        'swap_type', 'fixed_rate', 'float_index_id', 'second_index_id',
        'notional_quantity', 'uom_id', 'spread', 'payment_frequency',
        'start_date', 'end_date',
        // Futures
        'exchange', 'contract_code', 'expiry_date', 'num_contracts',
        'contract_size', 'futures_price', 'margin_requirement', 'futures_index_id',
        // Options
        'option_type', 'exercise_style', 'strike_price', 'option_expiry_date',
        'premium', 'underlying_index_id', 'volatility',
        // Audit
        'created_by', 'validated_by', 'validated_at',
    ];

    protected $casts = [
        'trade_date'         => 'date',
        'start_date'         => 'date',
        'end_date'           => 'date',
        'expiry_date'        => 'date',
        'option_expiry_date' => 'date',
        'validated_at'       => 'datetime',
        'fixed_rate'         => 'decimal:6',
        'notional_quantity'  => 'decimal:4',
        'spread'             => 'decimal:6',
        'futures_price'      => 'decimal:6',
        'contract_size'      => 'decimal:4',
        'margin_requirement' => 'decimal:2',
        'strike_price'       => 'decimal:6',
        'premium'            => 'decimal:6',
        'volatility'         => 'decimal:6',
    ];

    // Valid post-validation statuses per instrument type
    public const VALIDATED_STATUS = [
        'swap'    => 'Active',
        'futures' => 'Open',
        'options' => 'Open',
    ];

    public const TERMINAL_STATUSES = ['Settled', 'Closed', 'Expired', 'Exercised'];

    // ── ID Generators ─────────────────────────────────────────────────────────

    public static function nextDealNumber(): string
    {
        $year = now()->year;
        $last = static::where('deal_number', 'like', "FIN-{$year}-%")
            ->lockForUpdate()->max('deal_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('FIN-%d-%04d', $year, $seq);
    }

    /** Shared sequence across both trades tables */
    public static function nextTransactionNumber(): string
    {
        $year = now()->year;
        $last = DB::table('trades')->select('transaction_number')
            ->union(DB::table('financial_trades')->select('transaction_number'))
            ->where('transaction_number', 'like', "TXN-{$year}-%")
            ->orderByDesc('transaction_number')
            ->value('transaction_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('TXN-%d-%04d', $year, $seq);
    }

    /** Shared sequence across both trades tables */
    public static function nextInstrumentNumber(): string
    {
        $year = now()->year;
        $last = DB::table('trades')->select('instrument_number')
            ->union(DB::table('financial_trades')->select('instrument_number'))
            ->where('instrument_number', 'like', "INST-{$year}-%")
            ->orderByDesc('instrument_number')
            ->value('instrument_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('INST-%d-%04d', $year, $seq);
    }

    public static function derivePayRec(string $buySell): string
    {
        return $buySell === 'Buy' ? 'Pay' : 'Receive';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($q)    { return $q->where('trade_status', 'Pending'); }
    public function scopeValidated($q)  { return $q->where('trade_status', 'Validated'); }
    public function scopeActive($q)     { return $q->where('trade_status', 'Active'); }
    public function scopeOpen($q)       { return $q->where('trade_status', 'Open'); }
    public function scopeSwaps($q)      { return $q->where('instrument_type', 'swap'); }
    public function scopeFutures($q)    { return $q->where('instrument_type', 'futures'); }
    public function scopeOptions($q)    { return $q->where('instrument_type', 'options'); }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function internalBu(): BelongsTo    { return $this->belongsTo(Party::class, 'internal_bu_id'); }
    public function portfolio(): BelongsTo     { return $this->belongsTo(Portfolio::class); }
    public function counterparty(): BelongsTo  { return $this->belongsTo(Party::class, 'counterparty_id'); }
    public function currency(): BelongsTo      { return $this->belongsTo(Currency::class); }
    public function product(): BelongsTo       { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo           { return $this->belongsTo(Uom::class); }
    public function broker(): BelongsTo        { return $this->belongsTo(Broker::class); }
    public function agreement(): BelongsTo     { return $this->belongsTo(Agreement::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }
    public function validatedBy(): BelongsTo   { return $this->belongsTo(User::class, 'validated_by'); }

    public function floatIndex(): BelongsTo      { return $this->belongsTo(IndexDefinition::class, 'float_index_id'); }
    public function secondIndex(): BelongsTo     { return $this->belongsTo(IndexDefinition::class, 'second_index_id'); }
    public function futuresIndex(): BelongsTo    { return $this->belongsTo(IndexDefinition::class, 'futures_index_id'); }
    public function underlyingIndex(): BelongsTo { return $this->belongsTo(IndexDefinition::class, 'underlying_index_id'); }

    public function hedgesPhysicalTrade(): BelongsTo
    {
        return $this->belongsTo(Trade::class, 'hedges_physical_trade_id');
    }

    public function settlements(): HasMany { return $this->hasMany(FinancialSettlement::class); }
    public function auditLogs(): MorphMany { return $this->morphMany(AuditLog::class, 'auditable')->latest(); }

    // ── Swap Analytics ────────────────────────────────────────────────────────

    public function fixedLegValue(): float
    {
        return (float) $this->fixed_rate * (float) $this->notional_quantity;
    }

    public function floatLegValue(): float
    {
        $floatPrice = (float) ($this->floatIndex?->latestPrice?->price ?? 0);
        if ($this->swap_type === 'basis') {
            $secondPrice = (float) ($this->secondIndex?->latestPrice?->price ?? 0);
            $floatPrice  = $floatPrice - $secondPrice;
        }
        return ((float) $this->notional_quantity) * ($floatPrice + (float) $this->spread);
    }

    public function swapMtm(): float
    {
        $direction = $this->buy_sell === 'Buy' ? 1 : -1;
        // Receiver of float (Buy) gains when float > fixed
        return ($this->floatLegValue() - $this->fixedLegValue()) * $direction;
    }

    // ── Futures Analytics ─────────────────────────────────────────────────────

    public function currentFuturesPrice(): float
    {
        return (float) ($this->futuresIndex?->latestPrice?->price ?? $this->futures_price ?? 0);
    }

    public function futuresUnrealisedPnl(): float
    {
        $direction = $this->buy_sell === 'Buy' ? 1 : -1;
        return ($this->currentFuturesPrice() - (float) $this->futures_price)
            * (int) $this->num_contracts
            * (float) $this->contract_size
            * $direction;
    }

    public function totalMarginRequirement(): float
    {
        return (float) $this->margin_requirement * (int) $this->num_contracts;
    }

    // ── Options Analytics (Black-Scholes) ─────────────────────────────────────

    public function optionSpotPrice(): float
    {
        return (float) ($this->underlyingIndex?->latestPrice?->price ?? 0);
    }

    public function intrinsicValue(): float
    {
        $spot   = $this->optionSpotPrice();
        $strike = (float) $this->strike_price;
        if ($this->option_type === 'call') return max(0, $spot - $strike);
        return max(0, $strike - $spot);
    }

    public function timeValue(): float
    {
        return max(0, (float) $this->premium - $this->intrinsicValue());
    }

    public function blackScholesDelta(): float
    {
        $p = $this->bsParams();
        if ($p === null) return 0.0;
        return $this->option_type === 'call'
            ? $this->normalCdf($p['d1'])
            : $this->normalCdf($p['d1']) - 1;
    }

    public function blackScholesGamma(): float
    {
        $p = $this->bsParams();
        if ($p === null) return 0.0;
        return $this->normalPdf($p['d1']) / ($p['S'] * $p['sigma'] * sqrt($p['T']));
    }

    public function blackScholesVega(): float
    {
        // Vega per 1% change in volatility
        $p = $this->bsParams();
        if ($p === null) return 0.0;
        return $p['S'] * $this->normalPdf($p['d1']) * sqrt($p['T']) * 0.01;
    }

    public function blackScholesTheta(): float
    {
        // Theta per calendar day
        $p = $this->bsParams();
        if ($p === null) return 0.0;
        $term1 = -($p['S'] * $this->normalPdf($p['d1']) * $p['sigma']) / (2 * sqrt($p['T']));
        if ($this->option_type === 'call') {
            return ($term1 - $p['r'] * $p['K'] * exp(-$p['r'] * $p['T']) * $this->normalCdf($p['d2'])) / 365;
        }
        return ($term1 + $p['r'] * $p['K'] * exp(-$p['r'] * $p['T']) * $this->normalCdf(-$p['d2'])) / 365;
    }

    // ── Black-Scholes Helpers ─────────────────────────────────────────────────

    private function bsParams(): ?array
    {
        $S     = $this->optionSpotPrice();
        $K     = (float) $this->strike_price;
        $sigma = (float) $this->volatility;
        $r     = 0.05; // risk-free rate (training constant — 5%)
        $T     = max(0, now()->diffInDays($this->option_expiry_date) / 365.0);

        if ($T <= 0 || $S <= 0 || $K <= 0 || $sigma <= 0) return null;

        $d1 = (log($S / $K) + ($r + 0.5 * $sigma * $sigma) * $T) / ($sigma * sqrt($T));
        $d2 = $d1 - $sigma * sqrt($T);

        return compact('S', 'K', 'sigma', 'r', 'T', 'd1', 'd2');
    }

    private function normalCdf(float $x): float
    {
        // Abramowitz & Stegun approximation — accurate to ~7.5e-8
        $t   = 1.0 / (1.0 + 0.2316419 * abs($x));
        $poly = $t * (0.319381530 + $t * (-0.356563782 + $t * (1.781477937
              + $t * (-1.821255978 + $t * 1.330274429))));
        $pdf = exp(-0.5 * $x * $x) / sqrt(2 * M_PI);
        $cdf = 1 - $pdf * $poly;
        return $x >= 0 ? $cdf : 1 - $cdf;
    }

    private function normalPdf(float $x): float
    {
        return exp(-0.5 * $x * $x) / sqrt(2 * M_PI);
    }
}
