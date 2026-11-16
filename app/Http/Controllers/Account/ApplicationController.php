<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\Section;
use App\Services\InvoicePaymentService;
use App\Services\InvoiceService;
use App\Services\MembershipService;
use App\Services\MembershipUpgradeService;
use App\Services\StripeCustomerService;
use App\Services\StripePriceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;

class ApplicationController extends Controller
{
    public const ID_TYPES = [
        'national_id' => 'National ID card',
        'drivers_licence' => "Driver's licence",
        'passport' => 'International passport',
    ];

    public function __construct(
        private MembershipService $memberships,
        private InvoiceService $invoices,
        private MembershipUpgradeService $upgrades,
    ) {}

    /**
     * Onboarding page shown to new registrations.
     */
    public function start(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $membership = $user->membership()->with('plan')->first();

        if ($membership && $membership->status === Membership::ACTIVE) {
            return redirect()->route('account.applications.index');
        }

        $application = $user->applications()->with('plan')->latest()->first();

        return view('account.applications.start', [
            'application' => $application,
            'membership' => $membership,
            'idTypes' => self::ID_TYPES,
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $applications = $user->applications()->with([
            'plan.sections',
            'invoices' => fn ($q) => $q->latest('id'),
        ])->latest()->get();

        $membership = $user->membership()->with('plan.sections')->first();
        $currentApplication = $applications->first(fn (Application $application) => $application->isOpen());
        $currentPlan = ($membership?->plan ?? $currentApplication?->resolvedPlan())?->loadMissing('sections');

        $upgradePlans = $membership && $membership->status === Membership::ACTIVE
            ? $this->upgrades->upgradePlans($user)
            : collect();

        if ($membership && $membership->status === Membership::ACTIVE && $currentPlan) {
            $upgradePlans = $upgradePlans
                ->reject(fn (Plan $plan) => $plan->id === $currentPlan->id && ! $membership->isMonthly())
                ->values();
        }

        return view('account.applications.index', [
            'applications' => $applications,
            'membership' => $membership,
            'currentApplication' => $currentApplication,
            'currentPlan' => $currentPlan,
            'upgradePlans' => $upgradePlans,
            'hasMultiplePlans' => Plan::query()->active()->count() > 1,
            'sections' => Section::query()->active()->has('plans')->with(['plans' => fn ($q) => $q->where('plans.is_active', true)])->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->membership()->where('status', Membership::ACTIVE)->exists()) {
            return redirect()->route('account.applications.index')
                ->with('status', 'You already have an active membership.');
        }

        $open = $user->applications()
            ->whereIn('status', [Application::SUBMITTED, Application::FEES_NOTIFIED])
            ->latest()
            ->first();

        if ($open) {
            return redirect()->route('account.applications.index')
                ->with('status', 'You already have an application in progress.');
        }

        return view('account.applications.create', [
            'user' => $user,
            'idTypes' => self::ID_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postcode' => ['required', 'string', 'max:20'],
            'profession' => ['required', 'string', 'max:160'],
            'id_type' => ['required', Rule::in(array_keys(self::ID_TYPES))],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'id_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'terms' => ['accepted'],
        ]);

        $existing = $user->applications()
            ->whereIn('status', [Application::SUBMITTED, Application::FEES_NOTIFIED])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'application' => 'You already have an application in progress. Please wait for the club to review it.',
            ]);
        }

        $application = $this->memberships->createDraft($user);

        $cvPath = $request->file('cv')->storeAs(
            'applications/'.$application->id,
            'cv.'.$request->file('cv')->getClientOriginalExtension(),
            'local'
        );

        $idPath = $request->file('id_document')->storeAs(
            'applications/'.$application->id,
            'id.'.$request->file('id_document')->getClientOriginalExtension(),
            'local'
        );

        $application->update([
            'profession' => $data['profession'],
            'id_type' => $data['id_type'],
            'cv_path' => $cvPath,
            'id_path' => $idPath,
            'application_method' => 'online',
        ]);

        $user->update([
            'phone' => $data['phone'],
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'postcode' => $data['postcode'],
        ]);

        $this->memberships->submit($application);

        return redirect()->route('account.applications.index')
            ->with('status', 'Application submitted. Bring an accepted ID to the office so we can verify your documents and approve your application.');
    }

    /**
     * Fillable PDF application form; no record is created.
     */
    public function applicationForm(Request $request)
    {
        $user = $request->user();

        return Pdf::loadView('pdf.membership_application', [
            'user' => $user,
            'idTypes' => self::ID_TYPES,
            'club' => [
                'name' => config('club.name'),
                'address' => config('club.address'),
                'phone' => config('club.phone'),
                'email' => config('club.email'),
            ],
        ])->download('charlton-exclusive-membership-application.pdf');
    }

    public function pay(Request $request, Application $application): View|RedirectResponse
    {
        $this->authorizeOwner($request, $application);

        if ($application->status === Application::ACTIVE) {
            return redirect()->route('account.applications.index');
        }

        if ($application->status !== Application::FEES_NOTIFIED || ! $application->resolvedPlan()) {
            return redirect()->route('account.applications.index')
                ->with('status', 'Your application is awaiting review. We will email you as soon as it is approved.');
        }

        // Once the Stripe webhook has reported a successful payment the invoice is
        // paid and the application moves on; never offer the payment button again.
        // A failed payment keeps the application awaiting payment so the member
        // may retry through the secure checkout.
        if ($application->invoices()->where('status', Invoice::PAID)->exists()) {
            return redirect()->route('account.applications.payment-success', $application);
        }

        return view('account.applications.pay', [
            'application' => $application->load('plan'),
            'plan' => $application->resolvedPlan(),
            'annualOnlyDiscount' => true,
        ]);
    }

    public function startPayment(Request $request, Application $application): RedirectResponse
    {
        $this->authorizeOwner($request, $application);

        if ($application->status !== Application::FEES_NOTIFIED) {
            return redirect()->route('account.applications.index')
                ->with('status', 'This application is not awaiting payment.');
        }

        $plan = $application->resolvedPlan();

        if (! $plan) {
            return back()->with('status', 'An administrator has not selected a plan for this application yet.');
        }

        $data = $request->validate([
            'billing_frequency' => ['required', Rule::in([Membership::MONTHLY, Membership::ANNUAL])],
        ]);

        $frequency = $data['billing_frequency'];

        if ($frequency === Membership::MONTHLY && ! $plan->monthlyAvailable()) {
            throw ValidationException::withMessages([
                'billing_frequency' => 'Monthly billing is not available for this plan. Please choose yearly billing.',
            ]);
        }

        $application->update(['billing_frequency' => $frequency]);

        if (! config('cashier.secret')) {
            return redirect()->route('account.applications.payment-success', $application)
                ->with('status', 'Stripe is not configured, so online payment is unavailable. Please contact the club to complete payment.');
        }

        try {
            $priceId = app(StripePriceService::class)->ensure($plan, $frequency);
            $customerId = app(StripeCustomerService::class)->ensureFor($request->user());
            $invoice = $this->applicationInvoice($application, $plan, $frequency);

            $session = Cashier::stripe()->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $customerId,
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'success_url' => route('account.applications.payment-success', $application).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('account.applications.pay', $application),
                'metadata' => [
                    'type' => 'application',
                    'invoice_id' => (string) $invoice->id,
                    'application_id' => (string) $application->id,
                    'plan_id' => (string) $plan->id,
                    'billing_frequency' => $frequency,
                    'user_id' => (string) $request->user()->id,
                ],
                'subscription_data' => [
                    'metadata' => [
                        'type' => 'membership',
                        'application_id' => (string) $application->id,
                        'user_id' => (string) $request->user()->id,
                        'plan_id' => (string) $plan->id,
                        'billing_frequency' => $frequency,
                    ],
                ],
            ]);

            return redirect($session->url);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('account.applications.pay', $application)
                ->with('status', 'We could not connect to Stripe right now. Please try again, or contact the club if the problem continues.');
        }
    }

    public function paymentSuccess(Request $request, Application $application): View|RedirectResponse
    {
        $this->authorizeOwner($request, $application);

        $application->loadMissing(['plan']);
        $invoice = Invoice::query()
            ->where('invoiceable_type', Application::class)
            ->where('invoiceable_id', $application->id)
            ->latest('id')
            ->first();

        $sessionId = $request->query('session_id');

        if ($sessionId && $invoice && $invoice->status === Invoice::UNPAID && config('cashier.secret')) {
            try {
                $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
                $sessionInvoiceId = (string) ($session->metadata->invoice_id ?? '');
                $subscriptionId = $session->subscription ?? null;
                $frequency = $session->metadata->billing_frequency ?? $application->billing_frequency;

                if (in_array($session->payment_status ?? null, ['paid', 'no_payment_required'], true)
                    && ($sessionInvoiceId === '' || $sessionInvoiceId === (string) $invoice->id)) {
                    app(InvoicePaymentService::class)->fulfill(
                        $invoice,
                        $session->payment_intent ?? null,
                        $session->id ?? null,
                        $subscriptionId,
                        $frequency,
                    );

                    $invoice->refresh();
                    $application->refresh();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('account.applications.payment-success', [
            'application' => $application,
            'invoice' => $invoice,
        ]);
    }

    private function applicationInvoice(Application $application, Plan $plan, string $frequency): Invoice
    {
        // Supersede any outstanding invoice so the amount matches the chosen frequency.
        Invoice::query()
            ->where('invoiceable_type', Application::class)
            ->where('invoiceable_id', $application->id)
            ->where('status', Invoice::UNPAID)
            ->whereNull('stripe_invoice_id')
            ->update(['status' => Invoice::VOID]);

        $amount = $plan->feeFor($frequency);
        $label = $frequency === Membership::MONTHLY ? 'Monthly membership' : 'Yearly membership';

        return $this->invoices->issue($application->user, [[
            'description' => $plan->name.' — '.$label,
            'quantity' => 1,
            'unit_price' => $amount,
            'product_type' => 'membership',
        ]], $application);
    }

    private function authorizeOwner(Request $request, Application $application): void
    {
        abort_unless($application->user_id === $request->user()->id, 403);
        abort_unless($request->user()->can('view', $application), 403);
    }
}
