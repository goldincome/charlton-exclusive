<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPlanToStripe;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => Plan::query()->withCount('discounts')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.form', ['plan' => new Plan(['is_active' => true, 'billing_interval' => 'year', 'nominee_limit' => 2])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plan = Plan::query()->create($this->validated($request));
        $this->dispatchSync($plan, $request);

        return redirect()->route('admin.plans.edit', $plan)->with('status', 'Plan saved. Stripe sync has been queued.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.form', ['plan' => $plan]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));
        $this->dispatchSync($plan, $request);

        return back()->with('status', 'Plan saved. Stripe sync has been queued.');
    }

    public function sync(Plan $plan): RedirectResponse
    {
        SyncPlanToStripe::dispatch($plan->id);

        return back()->with('status', 'Stripe sync queued.');
    }

    private function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string'],
            'entrance_fee' => ['required', 'numeric', 'min:0'],
            'annual_fee' => ['required', 'numeric', 'min:0'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        foreach (['entrance_fee', 'annual_fee', 'monthly_fee', 'price'] as $money) {
            if ($data[$money] === null || $data[$money] === '') {
                $data[$money] = null;

                continue;
            }
            $data[$money] = (int) round(((float) $data[$money]) * 100);
        }
        $data['price'] = $data['price'] ?: $data['annual_fee'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function dispatchSync(Plan $plan, Request $request): void
    {
        $plan->update(['stripe_sync_status' => 'pending', 'stripe_sync_error' => null]);
        SyncPlanToStripe::dispatch($plan->id);
    }
}
