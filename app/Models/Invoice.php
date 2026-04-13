<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'trade_id', 'counterparty_id',
        'invoice_date', 'due_date', 'invoice_amount',
        'currency_id', 'payment_terms_id',
        'invoice_status', 'comments', 'created_by',
    ];

    protected $casts = [
        'invoice_date'   => 'date',
        'due_date'       => 'date',
        'invoice_amount' => 'decimal:2',
    ];

    public static function nextInvoiceNumber(): string
    {
        $year = now()->year;
        $last = static::where('invoice_number', 'like', "INV-{$year}-%")->max('invoice_number');
        $seq  = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('INV-%d-%04d', $year, $seq);
    }

    public static function calculateAmount(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->quantity * (float) $trade->fixed_price;
        }
        // Float: use latest index price + spread
        $latestPrice = $trade->index?->latestPrice?->price ?? 0;
        return (float) $trade->quantity * ((float) $latestPrice + (float) $trade->spread);
    }

    public function trade(): BelongsTo        { return $this->belongsTo(Trade::class); }
    public function counterparty(): BelongsTo { return $this->belongsTo(Party::class, 'counterparty_id'); }
    public function currency(): BelongsTo     { return $this->belongsTo(Currency::class); }
    public function paymentTerms(): BelongsTo { return $this->belongsTo(PaymentTerm::class, 'payment_terms_id'); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function settlements(): HasMany    { return $this->hasMany(Settlement::class); }

    public function totalPaid(): float
    {
        return (float) $this->settlements()->where('settlement_status', 'Confirmed')->sum('payment_amount');
    }

    public function outstandingAmount(): float
    {
        return (float) $this->invoice_amount - $this->totalPaid();
    }
}
