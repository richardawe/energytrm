<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $fillable = [
        'account_number', 'account_name', 'account_type',
        'holding_party_id', 'currency_id', 'status',
        'class', 'description', 'on_balance_sheet', 'allow_multiple_units',
        'account_legal_name', 'country', 'date_opened', 'date_closed',
        'general_ledger_account', 'sweep_enabled', 'version', 'created_by',
    ];

    protected $casts = [
        'on_balance_sheet'    => 'boolean',
        'allow_multiple_units'=> 'boolean',
        'sweep_enabled'       => 'boolean',
        'date_opened'         => 'date',
        'date_closed'         => 'date',
    ];

    public function holdingParty(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'holding_party_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
