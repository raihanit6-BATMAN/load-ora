<?php

namespace App\Policies;

use App\Models\Goods;
use App\Models\User;

class GoodsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Goods $goods): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function update(User $user, Goods $goods): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function delete(User $user, Goods $goods): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}
