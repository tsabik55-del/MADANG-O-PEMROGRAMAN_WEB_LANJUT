<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'owner' || $user->role === 'karyawan') {
            return true;
        }
        return $user->id === $order->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'pelanggan';
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->role === 'owner' || $user->role === 'karyawan') {
            return true;
        }
        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        if ($user->role === 'owner') {
            return true;
        }
        if ($user->role === 'pelanggan' && $user->id === $order->user_id) {
            return $order->status === 'menunggu_pembayaran';
        }
        return false;
    }

    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}