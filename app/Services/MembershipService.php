<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\MembershipPerson;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;

class MembershipService
{
    /**
     * Create a draft application. The plan is chosen by an administrator at
     * approval time, so it may be omitted here.
     */
    public function createDraft(User $user, ?int $planId = null, array $sectionIds = []): Application
    {
        return DB::transaction(function () use ($user, $planId, $sectionIds) {
            $application = Application::query()->create([
                'user_id' => $user->id,
                'membership_category_id' => $planId,
                'plan_id' => $planId,
                'status' => Application::DRAFT,
                'application_fee_active' => false,
                'application_fee' => 0,
                'application_method' => 'online',
                'reference' => 'APP-'.strtoupper(Str::random(8)),
            ]);

            if ($sectionIds !== []) {
                $application->sections()->sync($sectionIds);
            }

            return $application;
        });
    }

    public function submit(Application $application, array $attributes = []): Application
    {
        $application->update(array_merge($attributes, [
            'status' => Application::SUBMITTED,
            'submitted_at' => now(),
        ]));

        $this->audit($application, 'application.submitted', [
            'reference' => $application->reference,
        ]);

        $application->loadMissing('user');

        if ($application->user) {
            try {
                app(MailService::class)->send(
                    'application_received',
                    $application->user,
                    'We received your membership application',
                    $this->applicationReceivedData($application)
                );
            } catch (\Throwable $e) {
                Log::warning('Application received email failed', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $application->refresh();
    }

    /**
     * Record that an administrator checked the applicant's identity documents.
     */
    public function verifyDocuments(Application $application, User $admin): Application
    {
        $application->update([
            'documents_verified_at' => now(),
            'documents_verified_by' => $admin->id,
        ]);

        $this->audit($application, 'application.documents_verified', [
            'documents_verified_by' => $admin->id,
        ]);

        return $application->refresh();
    }

    /**
     * Admin approves the application for payment: choose the plan and notify
     * the applicant with a secure payment link.
     */
    public function approveForPayment(Application $application, User $admin, int $planId): Application
    {
        $plan = Plan::query()->active()->findOrFail($planId);

        $application->update([
            'plan_id' => $plan->id,
            'status' => Application::FEES_NOTIFIED,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // All currently active sections belong to the plan and are granted on activation.
        $sectionIds = $plan->sections()
            ->where('active', true)
            ->pluck('sections.id')
            ->all();
        $application->sections()->sync($sectionIds);

        $this->audit($application, 'application.approved_for_payment', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
        ]);

        $application->loadMissing('user');

        if ($application->user) {
            try {
                app(MailService::class)->send(
                    'application_activated_payment_due',
                    $application->user,
                    'Your membership application is approved — complete payment',
                    [
                        'greeting' => 'Hello '.($application->user->name ?: 'there').',',
                        'applicantName' => $application->user->name,
                        'planName' => $plan->name,
                        'applicationReference' => $application->reference,
                        'monthlyFee' => $this->money((int) $plan->monthly_fee),
                        'annualFee' => $this->money((int) $plan->annual_fee),
                        'monthlyAvailable' => $plan->monthlyAvailable(),
                        'payUrl' => route('account.applications.pay', $application),
                    ] + $this->clubData()
                );
            } catch (\Throwable $e) {
                Log::warning('Application approved email failed', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $application->refresh();
    }

    public function reject(Application $application, User $admin, string $reason): Application
    {
        $application->update([
            'status' => Application::REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->audit($application, 'application.rejected', ['reason' => $reason]);

        return $application->refresh();
    }

    /**
     * Activate (or refresh) a membership after a successful payment.
     */
    public function activate(
        Application $application,
        ?string $billingFrequency = null,
        ?string $subscriptionId = null
    ): Membership {
        return DB::transaction(function () use ($application, $billingFrequency, $subscriptionId) {
            $frequency = $billingFrequency
                ?: $application->billing_frequency
                ?: Membership::ANNUAL;
            $frequency = $frequency === Membership::MONTHLY ? Membership::MONTHLY : Membership::ANNUAL;

            $planId = $application->plan_id ?: $application->membership_category_id;
            $plan = $planId ? Plan::query()->find($planId) : null;

            $application->update([
                'status' => Application::ACTIVE,
                'billing_frequency' => $frequency,
            ]);

            $startsAt = now();

            $membership = Membership::query()
                ->where('user_id', $application->user_id)
                ->whereIn('status', [Membership::ACTIVE, Membership::PAST_DUE, Membership::SUSPENDED])
                ->latest('id')
                ->first()
                ?: new Membership(['user_id' => $application->user_id]);

            $membership->fill([
                'membership_category_id' => $planId,
                'plan_id' => $planId,
                'billing_frequency' => $frequency,
                'status' => Membership::ACTIVE,
                'starts_at' => $membership->starts_at ?: $startsAt->toDateString(),
                'renews_at' => $frequency === Membership::MONTHLY
                    ? $startsAt->copy()->addMonth()->toDateString()
                    : $startsAt->copy()->addYear()->toDateString(),
                'ends_at' => null,
                'stripe_subscription_id' => $subscriptionId ?: $membership->stripe_subscription_id,
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
            ]);
            $membership->save();

            MembershipPerson::query()->firstOrCreate([
                'membership_id' => $membership->id,
                'user_id' => $application->user_id,
            ], [
                'role' => 'primary',
            ]);

            $user = $application->user;
            $user->assignRole('member');
            $user->memberProfile()->updateOrCreate([], [
                'member_number' => $user->memberProfile?->member_number
                    ?? 'CEC-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'joined_at' => $user->memberProfile?->joined_at?->toDateString()
                    ?? $startsAt->toDateString(),
                'is_financial' => true,
                'is_suspended' => false,
                'suspended_at' => null,
            ]);
            $user->update(['status' => 'active']);

            $this->audit($application, 'membership.activated', [
                'membership_id' => $membership->id,
                'plan_id' => $planId,
                'plan_name' => $plan?->name,
                'billing_frequency' => $frequency,
                'stripe_subscription_id' => $membership->stripe_subscription_id,
            ]);

            return $membership->refresh();
        });
    }

    /**
     * Admin-only membership cancellation.
     */
    public function cancelMembership(
        Membership $membership,
        User $admin,
        string $reason,
        bool $immediately = false
    ): Membership {
        $subscriptionId = $membership->stripe_subscription_id;
        $stripe = null;

        if ($subscriptionId && config('cashier.secret')) {
            $stripe = Cashier::stripe();
        }

        $effectiveDate = $membership->renews_at?->copy() ?? now();

        if ($stripe) {
            try {
                if ($immediately) {
                    $stripe->subscriptions->cancel($subscriptionId);
                    $effectiveDate = now();
                } else {
                    $subscription = $stripe->subscriptions->update($subscriptionId, [
                        'cancel_at_period_end' => true,
                    ]);
                    $effectiveDate = $this->periodEnd($subscription) ?? $effectiveDate;
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe membership cancellation failed', [
                    'membership_id' => $membership->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif ($immediately) {
            $effectiveDate = now();
        }

        $membership->update([
            'cancelled_at' => now(),
            'cancelled_by' => $admin->id,
            'cancellation_reason' => $reason,
            'ends_at' => $effectiveDate->toDateString(),
            'status' => $immediately ? Membership::CANCELLED : $membership->status,
        ]);

        if ($immediately) {
            $membership->user?->memberProfile?->update([
                'is_financial' => false,
                'is_suspended' => true,
                'suspended_at' => now(),
            ]);
        }

        $this->audit($membership, 'membership.cancelled', [
            'reason' => $reason,
            'immediately' => $immediately,
            'ends_at' => $effectiveDate->toDateString(),
            'cancelled_by' => $admin->id,
        ]);

        $membership->loadMissing(['user', 'plan']);

        if ($membership->user) {
            try {
                app(MailService::class)->send(
                    'membership_cancelled',
                    $membership->user,
                    'Your Charlton Exclusive membership has been cancelled',
                    [
                        'greeting' => 'Hello '.($membership->user->name ?: 'there').',',
                        'planName' => $membership->plan?->name ?? 'Membership',
                        'reason' => $reason,
                        'effectiveDate' => $effectiveDate->format('j F Y'),
                        'immediately' => $immediately,
                    ] + $this->clubData()
                );
            } catch (\Throwable $e) {
                Log::warning('Membership cancelled email failed', [
                    'membership_id' => $membership->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $membership->refresh();
    }

    public function suspend(User $user, string $reason = 'Payment overdue beyond grace period'): void
    {
        $user->memberProfile?->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'is_financial' => false,
        ]);

        $user->membership?->update(['status' => Membership::SUSPENDED]);

        $this->audit($user, 'membership.suspended', ['reason' => $reason]);
    }

    public function reinstate(User $user): void
    {
        $user->memberProfile?->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'is_financial' => true,
        ]);

        $user->membership?->update(['status' => Membership::ACTIVE]);

        $this->audit($user, 'membership.reinstated');
    }

    private function applicationReceivedData(Application $application): array
    {
        return [
            'greeting' => 'Hello '.($application->user?->name ?: 'there').',',
            'applicantName' => $application->user?->name,
            'applicationReference' => $application->reference,
            'membershipUrl' => route('account.applications.index'),
        ] + $this->clubData();
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

    private function money(int $pence): string
    {
        return '£'.number_format($pence / 100, 2);
    }

    private function periodEnd(object $subscription): ?Carbon
    {
        $timestamp = $subscription->current_period_end
            ?? $subscription->items->data[0]->current_period_end
            ?? null;

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    private function audit(object $subject, string $action, array $newValues = []): void
    {
        $type = $subject instanceof Membership ? Membership::class : Application::class;
        $id = $subject instanceof Membership ? $subject->id : $subject->id;

        if ($subject instanceof User) {
            $type = User::class;
            $id = $subject->id;
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $type,
            'auditable_id' => $id,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
