<?php

namespace App\Policies;

use App\Models\Dispatch;
use App\Models\User;

class DispatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Dispatch $dispatch): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Dispatch $dispatch): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Staff can edit dispatches within 24 hours of creation
        return $dispatch->created_at->diffInHours(now()) <= 24;
    }

    public function delete(User $user, Dispatch $dispatch): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function restore(User $user, Dispatch $dispatch): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function forceDelete(User $user, Dispatch $dispatch): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}
