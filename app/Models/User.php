<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $hidden = ['password', 'remember_token'];

    protected $fillable = [
        'name',
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
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'password_never_expires' => 'boolean',
            'user_type'              => 'string',
            'license_type'           => 'string',
            'status'                 => 'string',
        ];
    }

    // Role helpers
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isTrader(): bool     { return $this->role === 'trader'; }
    public function isBackOffice(): bool { return $this->role === 'back_office'; }

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
}
