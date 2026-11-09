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

        return $application->user_id === $user->id;
    }

    public function review(User $user, Application $application): bool
    {
        return $user->isCommittee() || $user->isAdmin();
    }

    public function decide(User $user): bool
    {
        return $user->isAdmin();
    }
}
