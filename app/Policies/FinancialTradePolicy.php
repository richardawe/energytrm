<?php

namespace App\Policies;

use App\Models\FinancialTrade;
use App\Models\User;

class FinancialTradePolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'trader']);
    }

    public function update(User $user, FinancialTrade $trade): bool
    {
        if (in_array($trade->trade_status, FinancialTrade::TERMINAL_STATUSES)) {
            return false;
        }
        return in_array($user->role, ['admin', 'trader']);
    }

    public function validate(User $user, FinancialTrade $trade): bool
    {
        return in_array($user->role, ['admin', 'trader'])
            && $trade->trade_status === 'Pending';
    }

    public function revert(User $user, FinancialTrade $trade): bool
    {
        return in_array($user->role, ['admin', 'trader'])
            && $trade->trade_status === 'Validated';
    }

    public function delete(User $user, FinancialTrade $trade): bool
    {
        return $user->role === 'admin' && $trade->trade_status === 'Pending';
    }

    public function settle(User $user, FinancialTrade $trade): bool
    {
        return in_array($user->role, ['admin', 'back_office'])
            && in_array($trade->trade_status, ['Active', 'Open']);
    }
}
