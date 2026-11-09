<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\MembershipUpgradeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MembershipUpgradeController extends Controller
{
    public function __construct(private MembershipUpgradeService $upgrades)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $membership = $user->membership()->with('plan')->first();

        if (! $membership || $membership->status !== Membership::ACTIVE) {
            return redirect()->route('account.applications.index')
                ->with('status', 'An active membership is required to change plans.');
        }

        $plans = $this->upgrades->upgradePlans($user);

        if ($plans->isEmpty()) {
            return redirect()->route('account.applications.index')
                ->with('status', 'There are no other plans available to you at the moment.');
        }

        $selectedId = (int) $request->query('plan');
        $target = $plans->firstWhere('id', $selectedId) ?? $plans->first();

        $isCurrentPlan = $target->id === $membership->plan_id;
        $frequency = $request->query('frequency') === Membership::MONTHLY
            && ! $isCurrentPlan
            && $target->monthlyAvailable()
            ? Membership::MONTHLY
            : Membership::ANNUAL;
        $quote = null;
        $quoteError = null;

        try {
            $quote = $this->upgrades->quote($membership, $target, $frequency);
        } catch (\Throwable $e) {
            $quoteError = $e->getMessage();
        }

        return view('account.membership.upgrade', [
            'membership' => $membership,
            'plans' => $plans,
            'target' => $target,
            'quote' => $quote,
            'quoteError' => $quoteError,
            'frequency' => $frequency,
            'isCurrentPlan' => $isCurrentPlan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $membership = $user->membership()->with('plan')->first();

        if (! $membership || $membership->status !== Membership::ACTIVE) {
            return redirect()->route('account.applications.index')
                ->with('status', 'An active membership is required to change plans.');
        }

        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'billing_frequency' => ['required', Rule::in([Membership::MONTHLY, Membership::ANNUAL])],
        ]);

        $target = Plan::query()->active()->findOrFail($data['plan_id']);
        $allowed = $this->upgrades->upgradePlans($user)->contains('id', $target->id);

        if (! $allowed) {
            throw ValidationException::withMessages([
                'plan_id' => 'This plan is not available as an upgrade from your current membership.',
            ]);
        }

        if ($target->id === $membership->plan_id && $membership->isMonthly()) {
            // Switching the same plan to yearly billing fulfils the discount requirement.
            $data['billing_frequency'] = Membership::ANNUAL;
        }

        if ($data['billing_frequency'] === Membership::MONTHLY && ! $target->monthlyAvailable()) {
            throw ValidationException::withMessages([
                'billing_frequency' => 'Monthly billing is not available for this plan. Please choose yearly billing.',
            ]);
        }

        try {
            $result = $this->upgrades->upgrade($user, $membership, $target, $data['billing_frequency']);
        } catch (\Throwable $e) {
            return back()->withErrors(['upgrade' => $e->getMessage()]);
        }

        $amount = '£'.number_format($result['amount_due'] / 100, 2);

        return redirect()->route('account.applications.index')
            ->with('status', 'Your membership has been upgraded to '.$target->name.'. '.$amount.' was charged today.');
    }
}
