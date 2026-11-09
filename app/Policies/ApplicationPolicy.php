<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCommittee();
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isAdmin() || $user->isCommittee()) {
            return true;
        }

        if ($application->user_id === $user->id) {
            return true;
        }

        return $application->sponsorships()->where('sponsor_id', $user->id)->exists();
    }

    public function review(User $user, Application $application): bool
    {
        if (! $user->isCommittee() && ! $user->isAdmin()) {
            return false;
        }

        return ! $application->sponsorships()
            ->where('sponsor_id', $user->id)
            ->exists();
    }

    public function decide(User $user): bool
    {
        return $user->isAdmin();
    }
}