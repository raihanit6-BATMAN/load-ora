<?php

namespace App\Policies;

use App\Models\User;

class ImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function upload(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function commit(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function rollback(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}
