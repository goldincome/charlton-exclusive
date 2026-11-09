<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request->user();

        $target = match (true) {
            $user?->isAdmin() => route('admin.dashboard'),
            $user?->isStaffLike() => route('staff.dashboard'),
            $user?->isCommittee() => route('committee.applications.index'),
            $user?->membership !== null => route('account.applications.index'),
            default => route('account.applications.start'),
        };

        return redirect()->intended($target);
    }
}
