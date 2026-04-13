<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EobChecklist extends Model
{
    protected $table = 'eob_checklists';

    protected $fillable = [
        'checklist_date', 'business_unit_id',
        'all_trades_validated', 'all_invoices_issued',
        'all_settlements_confirmed', 'all_nominations_matched',
        'signed_off', 'signed_off_by', 'signed_off_at', 'comments',
    ];

    protected $casts = [
        'checklist_date'            => 'date',
        'signed_off_at'             => 'datetime',
        'all_trades_validated'      => 'boolean',
        'all_invoices_issued'       => 'boolean',
        'all_settlements_confirmed' => 'boolean',
        'all_nominations_matched'   => 'boolean',
        'signed_off'                => 'boolean',
    ];

    public function businessUnit(): BelongsTo  { return $this->belongsTo(Party::class, 'business_unit_id'); }
    public function signedOffBy(): BelongsTo   { return $this->belongsTo(User::class, 'signed_off_by'); }

    public function refreshItems(): void
    {
        $buId = $this->business_unit_id;
        $date = $this->checklist_date;

        $pendingTrades = Trade::where('internal_bu_id', $buId)
            ->where('trade_status', 'Pending')
            ->whereDate('trade_date', '<=', $date)
            ->exists();

        $draftInvoices = Invoice::whereHas('trade', fn($q) => $q->where('internal_bu_id', $buId))
            ->whereIn('invoice_status', ['Draft'])
            ->whereDate('invoice_date', '<=', $date)
            ->exists();

        $pendingSettlements = Settlement::whereHas('invoice.trade', fn($q) => $q->where('internal_bu_id', $buId))
            ->where('settlement_status', 'Pending')
            ->whereDate('payment_date', '<=', $date)
            ->exists();

        $unmatchedNominations = Nomination::whereHas('trade', fn($q) => $q->where('internal_bu_id', $buId))
            ->whereIn('nomination_status', ['Pending', 'Unmatched'])
            ->whereDate('gas_day', $date)
            ->exists();

        $this->update([
            'all_trades_validated'      => ! $pendingTrades,
            'all_invoices_issued'       => ! $draftInvoices,
            'all_settlements_confirmed' => ! $pendingSettlements,
            'all_nominations_matched'   => ! $unmatchedNominations,
        ]);
    }
}
