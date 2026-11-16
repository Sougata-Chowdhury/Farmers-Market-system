<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $order->user_id === $user->id) {
            return true;
        }

        return $user->isFarmer()
            && $order->items()->where('farmer_id', $user->id)->exists();
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return $order->status->allowedTransitions() !== [];
        }

        return $order->isCancellableBy($user);
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
