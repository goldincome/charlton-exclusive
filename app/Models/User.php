<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address_line_1', 'address_line_2',
        'city', 'postcode', 'date_of_birth', 'committee_member',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date:Y-m-d',
            'committee_member' => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class)->latestOfMany();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class, 'sponsor_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function foodOrders(): HasMany
    {
        return $this->hasMany(FoodOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isMember(): bool
    {
        return $this->hasRole('member');
    }

    public function memberNumber(): string
    {
        $profile = $this->memberProfile;
        if ($profile && $profile->member_number) {
            return $profile->member_number;
        }

        $number = 'CEC-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);

        $this->memberProfile()->updateOrCreate(
            ['user_id' => $this->id],
            [
                'member_number' => $number,
                'joined_at' => $this->memberProfile?->joined_at ?? $this->created_at->toDateString(),
            ]
        );

        return $number;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStaffLike(): bool
    {
        return $this->hasAnyRole(['admin', 'staff']);
    }

    public function isCommittee(): bool
    {
        return (bool) $this->committee_member || $this->hasRole('committee');
    }

    /**
     * A member is financial when their subscription is up to date and they are not suspended.
     */
    public function isFinancial(): bool
    {
        $profile = $this->memberProfile;

        if (! $profile || $profile->is_suspended) {
            return false;
        }

        $membership = $this->membership;

        if (! $membership || $membership->status !== Membership::ACTIVE) {
            return false;
        }

        return $profile->is_financial;
    }

    /**
     * Active, financial members may use members-only sections and member bookings.
     */
    public function hasMemberAccess(): bool
    {
        return $this->isMember() && $this->isFinancial();
    }

    /**
     * Only members paying yearly receive member discounts/rates.
     */
    public function isAnnualMember(): bool
    {
        return $this->hasMemberAccess() && $this->membership?->billing_frequency === Membership::ANNUAL;
    }

    public function canGetMemberRates(): bool
    {
        return $this->isAnnualMember();
    }

    public function sponsorCap(): int
    {
        $override = $this->memberProfile?->sponsor_cap;

        if (! is_null($override) && $override > 0) {
            return $override;
        }

        return AppConfig::int('sponsor_cap_default', 0);
    }

    public function activeSponsorshipCount(): int
    {
        return $this->sponsorships()
            ->whereIn('status', [Sponsorship::PENDING, Sponsorship::ACCEPTED])
            ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                Application::REJECTED, Application::WITHDRAWN, Application::EXPIRED,
            ]))
            ->count();
    }

    public function isEligibleSponsor(): bool
    {
        if (! $this->isMember() || ! $this->isFinancial()) {
            return false;
        }

        if ($this->memberProfile?->is_suspended) {
            return false;
        }

        $joinedAt = $this->memberProfile?->joined_at;

        if (! $joinedAt || $joinedAt->diffInYears(now()) < 3) {
            return false;
        }

        $cap = $this->sponsorCap();

        if ($cap === 0) {
            return true;
        }

        return $this->activeSponsorshipCount() < $cap;
    }

    public function displayName(): string
    {
        return $this->name;
    }
}
