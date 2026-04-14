<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Only admins can manage users
    public function viewAny(User $user): bool  { return $user->isAdmin(); }
    public function view(User $user): bool     { return $user->isAdmin(); }
    public function create(User $user): bool   { return $user->isAdmin(); }
    public function update(User $user): bool   { return $user->isAdmin(); }
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id; // can't delete yourself
    }
}
