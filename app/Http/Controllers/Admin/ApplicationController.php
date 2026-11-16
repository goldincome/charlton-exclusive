<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\MailService;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function __construct(
        private MembershipService $memberships,
        private MailService $mail,
    ) {}

    private function eligibleUsersQuery()
    {
        return User::query()
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->whereDoesntHave('membership', fn ($q) => $q->where('status', Membership::ACTIVE))
            ->whereDoesntHave('applications', fn ($q) => $q->whereIn('status', [Application::SUBMITTED, Application::FEES_NOTIFIED, Application::ACTIVE]));
    }

    public function create(Request $request): View
    {
        $this->authorize('decide', Application::class);

        return view('admin.applications.create', [
            'plans' => Plan::query()->active()->orderBy('annual_fee')->get(),
            'users' => $this->eligibleUsersQuery()->latest()->take(50)->get(),
        ]);
    }

    public function searchUsers(Request $request)
    {
        $this->authorize('decide', Application::class);
        $q = trim($request->query('q', ''));

        $users = $this->eligibleUsersQuery()
            ->when($q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhereHas('memberProfile', function ($profileSub) use ($q) {
                            $profileSub->where('member_number', 'like', "%{$q}%");
                        })
                        ->orWhereHas('applications', function ($appSub) use ($q) {
                            $appSub->where('reference', 'like', "%{$q}%");
                        });
                });
            })
            ->latest()
            ->take(50)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json($users);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('decide', Application::class);

        $data = $request->validate([
            'account_type' => ['required', 'in:new,existing'],
            'user_id' => ['required_if:account_type,existing', 'nullable', 'exists:users,id'],
            'name' => ['required_if:account_type,new', 'nullable', 'string', 'max:255'],
            'email' => ['required_if:account_type,new', 'nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'cv_document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $isNewAccount = false;
        if ($data['account_type'] === 'existing') {
            $user = User::query()->findOrFail($data['user_id']);
            if (! empty($data['phone'])) {
                $user->update(['phone' => $data['phone']]);
            }
        } else {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'password' => bcrypt(Str::random(32)),
                    'email_verified_at' => now(),
                ]
            );

            if ($user->wasRecentlyCreated) {
                $isNewAccount = true;
            } else {
                $user->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? $user->phone,
                ]);
            }
        }

        $idPath = $request->file('id_document')->store('applications/id-cards', 'local');
        $cvPath = $request->hasFile('cv_document') ? $request->file('cv_document')->store('applications/cvs', 'local') : null;

        $application = Application::query()->create([
            'user_id' => $user->id,
            'membership_category_id' => $data['plan_id'],
            'plan_id' => $data['plan_id'],
            'status' => Application::SUBMITTED,
            'application_method' => 'walk_in',
            'reference' => 'APP-'.strtoupper(Str::random(8)),
            'id_path' => $idPath,
            'cv_path' => $cvPath,
            'submitted_at' => now(),
            'admin_notes' => $data['notes'] ?? null,
        ]);

        if ($isNewAccount) {
            try {
                $token = Password::broker()->createToken($user);
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

                $this->mail->send(
                    'account_created',
                    $user,
                    'Your account has been created — set your password',
                    [
                        'greeting' => 'Hello '.($user->name ?: 'there').',',
                        'userEmail' => $user->email,
                        'applicationReference' => $application->reference,
                        'resetUrl' => $resetUrl,
                    ] + $this->clubData()
                );
            } catch (\Throwable $e) {
                Log::warning('Account created email failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $plan = Plan::query()->findOrFail($data['plan_id']);
        $application->sections()->sync($plan->sections()->where('active', true)->pluck('sections.id')->all());

        // Verify documents automatically because admin checked physical ID card
        $this->memberships->verifyDocuments($application, $request->user());

        // Approve for payment to notify user
        $this->memberships->approveForPayment($application, $request->user(), (int) $data['plan_id']);

        return redirect()->route('admin.applications.index')->with('status', 'Walk-in application registered, verified, and payment notification sent to applicant.');
    }

    private function clubData(): array
    {
        return [
            'clubName' => config('club.name'),
            'clubAddress' => config('club.address'),
            'clubPhone' => config('club.phone'),
            'clubEmail' => config('club.email'),
        ];
    }

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
