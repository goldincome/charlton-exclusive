<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanSectionDiscount;
use App\Models\Section;
use App\Services\PricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function index(): View
    {
        return view('admin.sections.index', [
            'sections' => Section::query()->withCount('discounts')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.sections.form', [
            'section' => new Section(['type' => Section::TYPE_SLOT, 'access_type' => Section::ACCESS_MEMBERS_ONLY, 'active' => true]),
            'plans' => Plan::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $section = Section::query()->create($this->validated($request));
        $this->syncDiscounts($request, $section);

        return redirect()->route('admin.sections.edit', $section)->with('status', 'Section created.');
    }

    public function edit(Section $section): View
    {
        return view('admin.sections.form', [
            'section' => $section->load('discounts'),
            'plans' => Plan::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $section->update($this->validated($request, $section));
        $this->syncDiscounts($request, $section);

        return back()->with('status', 'Section updated.');
    }

    public function togglePause(Section $section): RedirectResponse
    {
        $section->update(['active' => ! $section->active]);

        return back()->with('status', $section->active ? 'Section resumed.' : 'Section paused.');
    }

    private function validated(Request $request, ?Section $section = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('sections', 'slug')->ignore($section?->id)],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in([Section::TYPE_SLOT, Section::TYPE_CART])],
            'access_type' => ['required', Rule::in([Section::ACCESS_OPEN, Section::ACCESS_MEMBERS_ONLY])],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['active'] = $request->boolean('active');
        $data['recurring_annually'] = false; // discounts apply on use

        return $data;
    }

    private function syncDiscounts(Request $request, Section $section): void
    {
        $request->validate([
            'discounts' => ['nullable', 'array'],
            'discounts.*.type' => ['nullable', Rule::in(['percent', 'fixed', 'flat'])],
            'discounts.*.value' => ['nullable', 'integer', 'min:0'],
            'discounts.*.is_active' => ['nullable', 'boolean'],
        ]);

        $pricing = app(PricingService::class);

        foreach ($request->input('discounts', []) as $planId => $discount) {
            if (! Plan::query()->whereKey($planId)->exists()) {
                continue;
            }
            $type = $pricing->normalizeDiscountType($discount['type'] ?? 'percent');
            $value = (int) ($discount['value'] ?? 0);
            if ($type === 'percent') {
                $value = min(100, max(0, $value));
            }

            PlanSectionDiscount::query()->updateOrCreate(
                ['plan_id' => (int) $planId, 'section_id' => $section->id],
                [
                    'type' => $type,
                    'value' => $value,
                    'is_active' => (bool) ($discount['is_active'] ?? false),
                ]
            );
        }
    }
}
