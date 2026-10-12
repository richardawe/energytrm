<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\FunctionalGroup;
use App\Models\IndexDefinition;
use App\Models\Party;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $hidden = ['password', 'remember_token'];

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'personnel_id',
        'version',
        'user_type',
        'license_type',
        'short_ref_name',
        'short_alias_name',
        'employee_id',
        'title',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'password_never_expires',
        'status',
        'trial_expires_at',
    ];

    public const TRIAL_GROUP = 'Trial Access';
    public const TRIAL_MINUTES = 60;

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'password_never_expires' => 'boolean',
            'user_type'              => 'string',
            'license_type'           => 'string',
            'status'                 => 'string',
            'trial_expires_at'       => 'datetime',
        ];
    }

    // Role helpers
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isTrader(): bool     { return $this->role === 'trader'; }
    public function isBackOffice(): bool { return $this->role === 'back_office'; }

    // Trial accounts: members of the "Trial Access" security group with a time-boxed session
    public function isTrial(): bool { return $this->trial_expires_at !== null; }

    public function trialExpired(): bool
    {
        return $this->isTrial() && $this->trial_expires_at->isPast();
    }

    public function scopeNonTrial($query)
    {
        return $query->whereNull('trial_expires_at');
    }

    /**
     * Name shown to other users. Real names are visible only to admins and to the user
     * themselves; everyone else sees a pseudonym so colleagues stay anonymous.
     */
    public function getDisplayNameAttribute(): string
    {
        $viewer = auth()->user();

        if ($viewer && ($viewer->isAdmin() || $viewer->id === $this->id)) {
            return $this->name;
        }

        return $this->pseudonym();
    }

    // Derived from the ID only — short_ref_name / personnel_id often encode the real name
    public function pseudonym(): string
    {
        return 'Trader-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    // Pivot relationships
    public function businessUnits(): BelongsToMany
    {
        return $this->belongsToMany(Party::class, 'user_business_units', 'user_id', 'party_id');
    }

    public function portfolios(): BelongsToMany
    {
        return $this->belongsToMany(Portfolio::class, 'user_portfolios');
    }

    public function securityGroups(): BelongsToMany
    {
        return $this->belongsToMany(SecurityGroup::class, 'user_security_groups');
    }

    public function tradingLocations(): BelongsToMany
    {
        return $this->belongsToMany(TradingLocation::class, 'user_trading_locations')->withPivot('is_default');
    }

    public function legalEntities(): BelongsToMany
    {
        return $this->belongsToMany(Party::class, 'user_legal_entities', 'user_id', 'party_id')->withPivot('is_default');
    }

    public function securedIndices(): BelongsToMany
    {
        return $this->belongsToMany(IndexDefinition::class, 'user_secured_indices', 'user_id', 'index_id');
    }

    public function functionalGroups(): BelongsToMany
    {
        return $this->belongsToMany(FunctionalGroup::class, 'user_functional_groups');
    }
}
