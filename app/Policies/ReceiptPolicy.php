<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Receipt $receipt): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Receipt $receipt): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Staff can update receipts within 24 hours
        return $receipt->created_at->diffInHours(now()) <= 24;
    }

    public function delete(User $user, Receipt $receipt): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}
