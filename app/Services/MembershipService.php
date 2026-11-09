<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\MembershipPerson;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    public function createDraft(User $user, int $categoryId, array $sectionIds = []): Application
    {
        return DB::transaction(function () use ($user, $categoryId, $sectionIds) {
            $application = Application::query()->create([
                'user_id' => $user->id,
                'membership_category_id' => $categoryId,
                'status' => Application::DRAFT,
                'application_fee_active' => false,
                'application_fee' => 0,
                'reference' => 'APP-'.strtoupper(Str::random(8)),
            ]);

            $application->sections()->sync($sectionIds);

            return $application;
        });
    }

    public function submit(Application $application): Application
    {
        $application->update([
            'status' => Application::AWAITING_SPONSORS,
            'submitted_at' => now(),
        ]);

        return $application->refresh();
    }

    public function acceptSponsorship(Sponsorship $sponsorship, User $sponsor): Sponsorship
    {
        abort_unless($sponsorship->sponsor_id === $sponsor->id, 403);
        abort_unless($sponsorship->status === Sponsorship::PENDING, 422);

        $eligibility = $sponsor->isEligibleSponsor();

        abort_unless($eligibility, 422, 'You are not eligible to sponsor at this time.');

        $sponsorship->update([
            'status' => Sponsorship::ACCEPTED,
            'responded_at' => now(),
        ]);

        $this->advanceIfSponsored($sponsorship->application);

        return $sponsorship->refresh();
    }

    public function declineSponsorship(Sponsorship $sponsorship, User $sponsor, string $reason): Sponsorship
    {
        abort_unless($sponsorship->sponsor_id === $sponsor->id, 403);

        $sponsorship->update([
            'status' => Sponsorship::DECLINED,
            'reason' => $reason,
            'responded_at' => now(),
        ]);

        return $sponsorship->refresh();
    }

    public function withdrawSponsorship(Sponsorship $sponsorship, User $sponsor, string $reason): Sponsorship
    {
        abort_unless($sponsorship->sponsor_id === $sponsor->id, 403);

        $sponsorship->update([
            'status' => Sponsorship::WITHDRAWN,
            'reason' => $reason,
            'responded_at' => now(),
        ]);

        return $sponsorship->refresh();
    }

    public function advanceIfSponsored(Application $application): void
    {
        if ($application->acceptedSponsors()->count() >= 2
            && $application->status === Application::AWAITING_SPONSORS) {
            $application->update(['status' => Application::UNDER_REVIEW]);
        }
    }

    public function recordDecision(Application $application, User $admin, bool $approved, ?string $reason = null): Application
    {
        if ($approved) {
            $application->update([
                'status' => Application::FEES_NOTIFIED,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);
        } else {
            $application->update([
                'status' => Application::REJECTED,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);
        }

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => $approved ? 'application.approved' : 'application.rejected',
            'auditable_type' => Application::class,
            'auditable_id' => $application->id,
            'new_values' => ['reason' => $reason],
            'ip_address' => request()->ip(),
        ]);

        return $application->refresh();
    }

    /**
     * Activate membership once the entrance/category/section invoice is paid.
     */
    public function activate(Application $application, string $billingFrequency = Membership::ANNUAL): Membership
    {
        return DB::transaction(function () use ($application, $billingFrequency) {
            $application->update(['status' => Application::ACTIVE]);

            $membership = Membership::query()->create([
                'user_id' => $application->user_id,
                'membership_category_id' => $application->membership_category_id,
                'billing_frequency' => $billingFrequency,
                'status' => Membership::ACTIVE,
                'starts_at' => now()->toDateString(),
                'renews_at' => now()->addYear()->toDateString(),
            ]);

            MembershipPerson::query()->create([
                'membership_id' => $membership->id,
                'user_id' => $application->user_id,
                'role' => 'primary',
            ]);

            $user = $application->user;
            $user->assignRole('member');
            $user->memberProfile()->updateOrCreate([], [
                'member_number' => $user->memberProfile?->member_number ?? 'CEC-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'joined_at' => now()->toDateString(),
                'is_financial' => true,
                'is_suspended' => false,
            ]);
            $user->update(['status' => 'active']);

            return $membership;
        });
    }

    public function suspend(User $user, string $reason = 'Payment overdue beyond grace period'): void
    {
        $user->memberProfile?->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'is_financial' => false,
        ]);

        $user->membership?->update(['status' => Membership::SUSPENDED]);

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => 'membership.suspended',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => ['reason' => $reason],
        ]);
    }

    public function reinstate(User $user): void
    {
        $user->memberProfile?->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'is_financial' => true,
        ]);

        $user->membership?->update(['status' => Membership::ACTIVE]);
    }
}