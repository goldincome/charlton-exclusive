<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MembershipCategory;
use App\Models\Section;
use Illuminate\Contracts\View\View;

class MembershipController extends Controller
{
    public function index(): View
    {
        return view('public.membership.index', [
            'categories' => MembershipCategory::query()->active()->with('sections')->get(),
            'sections' => Section::query()->active()->get(),
        ]);
    }

    public function apply(): View
    {
        return view('public.membership.apply', [
            'categories' => MembershipCategory::query()->active()->with('sections')->get(),
        ]);
    }

    public function section(string $slug): View
    {
        $section = Section::query()->active()->where('slug', $slug)->firstOrFail();

        return view('public.membership.section', [
            'section' => $section,
            'categories' => MembershipCategory::query()->active()->whereHas('sections', fn ($q) => $q->whereKey($section->id))->get(),
        ]);
    }
}
