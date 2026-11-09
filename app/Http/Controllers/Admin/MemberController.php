<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private MembershipService $memberships)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.members.index', [
            'members' => User::query()
                ->role('member')
                ->with(['memberProfile', 'membership.category'])
                ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->paginate(20),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.members.show', [
            'user' => $user->load(['memberProfile', 'membership.category', 'applications', 'invoices']),
            'sponsorships' => $user->sponsorships()->with('application.user')->latest()->get(),
        ]);
    }

    public function updateSponsorCap(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['sponsor_cap' => ['required', 'integer', 'min:0', 'max:50']]);
        $user->memberProfile()->updateOrCreate([], ['sponsor_cap' => $data['sponsor_cap']]);

        return back()->with('status', 'Sponsor cap updated.');
    }

    public function suspend(User $user): RedirectResponse
    {
        $this->memberships->suspend($user);

        return back()->with('status', 'Membership suspended.');
    }

    public function reinstate(User $user): RedirectResponse
    {
        $this->memberships->reinstate($user);

        return back()->with('status', 'Membership reinstated.');
    }

    public function toggleFinancial(User $user): RedirectResponse
    {
        $profile = $user->memberProfile()->firstOrCreate([]);
        $profile->update(['is_financial' => ! $profile->is_financial]);

        return back()->with('status', 'Financial status updated.');
    }
}