<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user->load(['memberProfile', 'membership.category']),
            'bookings' => $user->bookings()->with('bookingable')->latest()->take(5)->get(),
            'invoices' => $user->invoices()->latest()->take(5)->get(),
            'applications' => $user->applications()->with('category')->latest()->get(),
            'pendingSponsorships' => $user->sponsorships()->where('status', 'pending')->with('application.user')->get(),
        ]);
    }
}