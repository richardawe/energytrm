<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    protected $fillable = [
        'name', 'internal_party_id', 'counterparty_id', 'payment_terms_id',
        'effective_date', 'expiry_date', 'notes', 'status', 'version',
    ];
    protected $casts = ['effective_date' => 'date', 'expiry_date' => 'date'];

    public function internalParty(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'internal_party_id');
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'counterparty_id');
    }

    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }
}
