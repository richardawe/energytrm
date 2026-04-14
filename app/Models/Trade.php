<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Trade extends Model
{
    protected $fillable = [
        'deal_number', 'transaction_number', 'instrument_number', 'version',
        'trade_status', 'trade_date', 'buy_sell', 'pay_rec',
        'start_date', 'end_date',
        'internal_bu_id', 'portfolio_id', 'counterparty_id',
        'product_id', 'quantity', 'volume_type', 'uom_id',
        'fixed_float', 'index_id', 'fixed_price', 'spread',
        'currency_id', 'payment_terms_id',
        'incoterm_code', 'load_port', 'discharge_port',
        'broker_id', 'agreement_id', 'comments',
        'created_by', 'validated_by', 'validated_at',
    ];

    protected $casts = [
        'trade_date'   => 'date',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'validated_at' => 'datetime',
        'quantity'     => 'decimal:4',
        'fixed_price'  => 'decimal:6',
        'spread'       => 'decimal:6',
    ];

    // ── Auto-derive pay_rec from buy_sell ─────────────────────────────────────
    public static function derivePayRec(string $buySell): string
    {
        return $buySell === 'Buy' ? 'Pay' : 'Receive';
    }

    // ── ID generators ─────────────────────────────────────────────────────────
    public static function nextDealNumber(): string
    {
        $year = now()->year;
        $last = static::where('deal_number', 'like', "DL-{$year}-%")
            ->lockForUpdate()->max('deal_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('DL-%d-%04d', $year, $seq);
    }

    public static function nextTransactionNumber(): string
    {
        $year = now()->year;
        $last = static::where('transaction_number', 'like', "TXN-{$year}-%")
            ->lockForUpdate()->max('transaction_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('TXN-%d-%04d', $year, $seq);
    }

    public static function nextInstrumentNumber(): string
    {
        $year = now()->year;
        $last = static::where('instrument_number', 'like', "INST-{$year}-%")
            ->lockForUpdate()->max('instrument_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('INST-%d-%04d', $year, $seq);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopePending($q)   { return $q->where('trade_status', 'Pending'); }
    public function scopeValidated($q) { return $q->where('trade_status', 'Validated'); }
    public function scopeSettled($q)   { return $q->where('trade_status', 'Settled'); }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function internalBu(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'internal_bu_id');
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'counterparty_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function index(): BelongsTo
    {
        return $this->belongsTo(IndexDefinition::class, 'index_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function shipments(): HasMany    { return $this->hasMany(Shipment::class); }
    public function invoices(): HasMany     { return $this->hasMany(Invoice::class); }
    public function nominations(): HasMany  { return $this->hasMany(Nomination::class); }
    public function latestInvoice(): HasOne { return $this->hasOne(Invoice::class)->latestOfMany(); }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}
