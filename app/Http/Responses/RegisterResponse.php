<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        if (session()->has('cart_items') && ! empty(session('cart_items'))) {
            return redirect()->intended(route('cart.index'))
                ->with('status', 'Your account is ready. Review your cart to complete checkout.');
        }

        return redirect()->intended(route('account.applications.start'))
            ->with('status', 'Your account is ready. Complete your membership application to join the club.');
    }
}
