<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private MembershipService $memberships) {}

    public function index(Request $request): View
    {
        return view('admin.members.index', [
            'members' => User::query()
                ->role('member')
                ->with(['memberProfile', 'membership.plan'])
                ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->paginate(20),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.members.show', [
            'user' => $user->load(['memberProfile', 'membership.plan', 'applications.plan', 'invoices']),
        ]);
    }

    public function cancelMembership(Request $request, User $user): RedirectResponse
    {
        $membership = $user->membership()->first();

        abort_unless($membership, 404, 'This member has no membership to cancel.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'immediately' => ['nullable', 'boolean'],
        ]);

        $this->memberships->cancelMembership(
            $membership,
            $request->user(),
            $data['reason'],
            $request->boolean('immediately')
        );

        return back()->with('status', $request->boolean('immediately')
            ? 'Membership cancelled immediately.'
            : 'Membership scheduled to cancel at the end of the billing period.');
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

    public function updateMemberNumber(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'member_number' => ['required', 'string', 'max:50', 'unique:member_profiles,member_number,'.optional($user->memberProfile)->id],
        ]);

        $user->memberProfile()->updateOrCreate(
            ['user_id' => $user->id],
            ['member_number' => $data['member_number']]
        );

        return back()->with('status', 'Member number updated successfully.');
    }
}
