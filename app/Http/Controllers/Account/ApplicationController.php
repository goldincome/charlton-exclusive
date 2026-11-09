<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\MembershipCategory;
use App\Models\Section;
use App\Models\Sponsorship;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    public function __construct(private MembershipService $memberships)
    {
    }

    public function index(Request $request): View
    {
        return view('account.applications.index', [
            'applications' => $request->user()->applications()->with(['category', 'sponsorships.sponsor'])->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'sections' => ['required', 'array', 'min:2'],
            'sections.*' => ['exists:sections,id'],
            'sponsors' => ['required', 'array', 'size:2'],
            'sponsors.*' => ['required', 'email', 'distinct', 'exists:users,email'],
            'terms' => ['accepted'],
        ]);

        $category = MembershipCategory::query()->findOrFail($data['membership_category_id']);

        if (count($data['sections']) < $category->min_sections) {
            throw ValidationException::withMessages([
                'sections' => "This category requires at least {$category->min_sections} sections.",
            ]);
        }

        $application = $this->memberships->createDraft($request->user(), $category->id, $data['sections']);
        $this->memberships->submit($application);

        foreach ($data['sponsors'] as $email) {
            $sponsor = User::query()->where('email', $email)->firstOrFail();
            Sponsorship::query()->create([
                'application_id' => $application->id,
                'sponsor_id' => $sponsor->id,
                'status' => Sponsorship::PENDING,
            ]);
        }

        return redirect()->route('account.applications.index')
            ->with('status', 'Your application has been submitted. Both sponsors have been emailed.');
    }

    public function create(): View
    {
        return view('account.applications.create', [
            'categories' => MembershipCategory::query()->active()->with('sections')->get(),
        ]);
    }
}