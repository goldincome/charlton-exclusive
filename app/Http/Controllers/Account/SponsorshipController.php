<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    public function __construct(private MembershipService $memberships)
    {
    }

    public function index(Request $request): View
    {
        return view('account.sponsorships.index', [
            'sponsorships' => $request->user()->sponsorships()->with('application.user', 'application.category')->latest()->get(),
        ]);
    }

    public function accept(Sponsorship $sponsorship, MembershipService $memberships): RedirectResponse
    {
        $memberships->acceptSponsorship($sponsorship, auth()->user());

        return back()->with('status', 'Sponsorship accepted.');
    }

    public function decline(Request $request, Sponsorship $sponsorship, MembershipService $memberships): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $memberships->declineSponsorship($sponsorship, auth()->user(), $data['reason']);

        return back()->with('status', 'Sponsorship declined.');
    }

    public function withdraw(Request $request, Sponsorship $sponsorship, MembershipService $memberships): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $memberships->withdrawSponsorship($sponsorship, auth()->user(), $data['reason']);

        return back()->with('status', 'Support withdrawn.');
    }
}