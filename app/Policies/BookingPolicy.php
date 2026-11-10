<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->hasRole('staff') || $booking->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin();
    }

    public function checkIn(User $user): bool
    {
        return $user->isStaffLike();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $booking->user_id === $user->id;
    }

    public function refund(User $user): bool
    {
        return $user->isAdmin();
    }
}
