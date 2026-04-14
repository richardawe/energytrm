<?php

namespace App\Policies;

use App\Models\Trade;
use App\Models\User;

class TradePolicy
{
    // Admin and Trader can capture new trades
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'trader']);
    }

    // Pending trades: Admin + Trader can amend; Back Office cannot
    public function update(User $user, Trade $trade): bool
    {
        if ($trade->trade_status === 'Settled') {
            return false;
        }
        return in_array($user->role, ['admin', 'trader']);
    }

    // Only Admin can validate/revert
    public function validate(User $user, Trade $trade): bool
    {
        return $user->isAdmin() || $user->isTrader();
    }

    public function delete(User $user, Trade $trade): bool
    {
        return $user->isAdmin() && $trade->trade_status === 'Pending';
    }
}
