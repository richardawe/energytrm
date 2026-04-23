<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    protected $fillable = [
        'party_type', 'internal_external', 'parent_id', 'short_name', 'long_name',
        'status', 'version', 'lei', 'bic_swift', 'credit_limit', 'credit_limit_currency_id',
        'kyc_status', 'kyc_review_date', 'regulatory_class',
    ];

    protected $casts = ['kyc_review_date' => 'date', 'credit_limit' => 'decimal:2'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Party::class, 'parent_id');
    }

    public function creditLimitCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'credit_limit_currency_id');
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'business_unit_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PartyAddress::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PartyNote::class);
    }

    public function creditRatings(): HasMany
    {
        return $this->hasMany(CreditRating::class);
    }

    public function settlementInstructions(): HasMany
    {
        return $this->hasMany(SettlementInstruction::class);
    }

    // Scope helpers
    public function scopeGroups($q)       { return $q->where('party_type', 'Group'); }
    public function scopeLegalEntities($q){ return $q->where('party_type', 'LE'); }
    public function scopeBusinessUnits($q){ return $q->where('party_type', 'BU'); }
    public function scopeInternal($q)     { return $q->where('internal_external', 'Internal'); }
    public function scopeExternal($q)     { return $q->where('internal_external', 'External'); }
    public function scopeAuthorized($q)   { return $q->where('status', 'Authorized'); }
}
