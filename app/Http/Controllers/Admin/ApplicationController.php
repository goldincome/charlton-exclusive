<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private MembershipService $memberships)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.applications.index', [
            'applications' => Application::query()
                ->with(['user', 'category', 'sponsorships.sponsor'])
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'statuses' => [
                Application::SUBMITTED, Application::AWAITING_SPONSORS, Application::UNDER_REVIEW,
                Application::INTERVIEW_SCHEDULED, Application::INTERVIEW_COMPLETED,
                Application::FEES_NOTIFIED, Application::ACTIVE, Application::REJECTED,
            ],
        ]);
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        return view('admin.applications.show', [
            'application' => $application->load(['user', 'category', 'sections', 'sponsorships.sponsor', 'interviews.attendees']),
        ]);
    }

    public function scheduleInterview(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('review', $application);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        $application->interviews()->create($data);
        $application->update(['status' => Application::INTERVIEW_SCHEDULED]);

        return back()->with('status', 'Interview scheduled.');
    }

    public function recordRecommendation(Request $request, Interview $interview): RedirectResponse
    {
        $this->authorize('review', $interview->application);

        $data = $request->validate([
            'recommendation' => ['required', 'in:eligible,ineligible'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $interview->update($data);
        $interview->application->update(['status' => Application::INTERVIEW_COMPLETED]);

        return back()->with('status', 'Committee recommendation recorded.');
    }

    public function decide(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('decide', Application::class);

        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'reason' => ['required_if:decision,reject', 'nullable', 'string', 'max:2000'],
        ]);

        $this->memberships->recordDecision($application, $request->user(), $data['decision'] === 'approve', $data['reason'] ?? null);

        return back()->with('status', 'Decision recorded and audited.');
    }
}