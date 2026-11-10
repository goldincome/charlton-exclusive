<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Plan;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function __construct(private MembershipService $memberships) {}

    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));

        return view('admin.applications.index', [
            'applications' => Application::query()
                ->with(['user.membership', 'plan', 'category'])
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->when($search, function ($q, $search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('reference', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'statuses' => [
                Application::SUBMITTED,
                Application::FEES_NOTIFIED,
                Application::ACTIVE,
                Application::REJECTED,
                Application::WITHDRAWN,
                Application::EXPIRED,
            ],
        ]);
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'user', 'plan.sections', 'category', 'sections',
            'interviews.attendees', 'documentsVerifiedBy', 'invoices',
        ]);

        return view('admin.applications.show', [
            'application' => $application,
            'plans' => Plan::query()->active()->orderBy('annual_fee')->get(),
        ]);
    }

    public function verifyDocuments(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('decide', Application::class);

        $this->memberships->verifyDocuments($application, $request->user());

        return back()->with('status', 'Documents marked as verified.');
    }

    public function approve(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('decide', Application::class);

        if (! $application->hasVerifiedDocuments()) {
            return back()->with('status', 'Verify the applicant\'s identity documents before approving the application.');
        }

        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $this->memberships->approveForPayment($application, $request->user(), (int) $data['plan_id']);

        return back()->with('status', 'Application approved. The applicant has been emailed a payment link.');
    }

    public function reject(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('decide', Application::class);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->memberships->reject($application, $request->user(), $data['reason']);

        return back()->with('status', 'Application rejected.');
    }

    public function downloadDocument(Application $application, string $type)
    {
        $this->authorize('view', $application);

        abort_unless(in_array($type, ['cv', 'id'], true), 404);

        $path = $type === 'cv' ? $application->cv_path : $application->id_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $label = $type === 'cv' ? 'cv' : 'id-document';
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download(
            $path,
            $application->reference.'-'.$label.($extension ? '.'.$extension : '')
        );
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
}
