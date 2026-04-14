<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'report_type', 'reporting_date', 'parameters', 'file_format', 'generated_by',
    ];

    protected $casts = [
        'reporting_date' => 'date',
    ];

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'portfolio_analysis'    => 'Portfolio Analysis',
            'pnl'                   => 'P&L Summary',
            'counterparty_exposure' => 'Counterparty Exposure',
            'var'                   => 'VaR & Stress Tests',
            default                 => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
