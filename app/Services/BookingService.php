<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private SlotManager $slots,
        private PricingService $pricing,
    ) {
    }

    /**
     * Create a booking request under a pessimistic lock plus the booking_slot
     * unique-index guard. Any conflict throws a ValidationException with a
     * user-facing message.
     *
     * @param  array{start: CarbonInterface|string, end: CarbonInterface|string, guests?: int, notes?: string|null}  $data
     */
    public function request(User $user, string $resourceType, int $resourceId, array $data): Booking
    {
        $start = Carbon::parse($data['start'])->utc();
        $end = Carbon::parse($data['end'])->utc();

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['end' => 'The end time must be after the start time.']);
        }

        $buffer = 0;
        $price = 0;
        $discountType = null;
        $discountValue = 0;

        if ($resourceType === 'hall') {
            $hall = Hall::query()->findOrFail($resourceId);
            $buffer = $hall->buffer_minutes;
            $price = $this->pricing->hallTotal($hall, $start, $end, $user->canGetMemberRates());
        } elseif ($resourceType === 'event') {
            $event = \App\Models\Event::query()->findOrFail($resourceId);
            $tickets = max(1, (int) ($data['guests'] ?? 1));
            $price = $this->pricing->eventTicketPrice($event, $user->canGetMemberRates(), $tickets);
        }

        $slots = $this->slots->slotsFor($start, $end, $buffer);

        try {
            return DB::transaction(function () use ($user, $resourceType, $resourceId, $start, $end, $data, $price, $buffer, $slots, $discountType, $discountValue) {
                // Layer 1: pessimistic row lock on the parent resource.
                $this->lockResource($resourceType, $resourceId);

                $conflicts = $this->slots->conflicts($resourceType, $resourceId, $slots);

                if ($conflicts->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'start' => 'That time is no longer available. Please choose another slot.',
                    ]);
                }

                $booking = Booking::query()->create([
                    'user_id' => $user->id,
                    'bookingable_type' => $this->resourceClass($resourceType),
                    'bookingable_id' => $resourceId,
                    'status' => Booking::REQUESTED,
                    'start_time' => $start,
                    'end_time' => $end,
                    'guests' => (int) ($data['guests'] ?? 1),
                    'price_locked' => $price,
                    'discount_applied' => $discountValue,
                    'discount_type' => $discountType,
                    'notes' => $data['notes'] ?? null,
                    'expires_at' => now()->addHours(AppConfig::int('booking_request_expiry_hours', 72)),
                ]);

                // Layer 2: unique index on (resource_type, resource_id, slot_start).
                $this->slots->reserve($resourceType, $resourceId, $slots, $booking);

                return $booking;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'start' => 'That time is no longer available. Please choose another slot.',
            ]);
        }
    }

    public function approve(Booking $booking, User $admin): Booking
    {
        if ($booking->status !== Booking::REQUESTED) {
            throw ValidationException::withMessages(['status' => 'Only requested bookings can be approved.']);
        }

        $booking->update([
            'status' => Booking::APPROVED,
            'approved_at' => now(),
            'expires_at' => now()->addHours(AppConfig::int('payment_window_hours', 72)),
        ]);

        $this->audit($admin, 'booking.approved', $booking);

        return $booking->refresh();
    }

    public function reject(Booking $booking, User $admin, string $reason): Booking
    {
        $booking->update([
            'status' => Booking::REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->slots->release($booking);
        $this->audit($admin, 'booking.rejected', $booking);

        return $booking->refresh();
    }

    public function confirm(Booking $booking): Booking
    {
        $booking->update([
            'status' => Booking::CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return $booking->refresh();
    }

    public function cancel(Booking $booking, User $actor, ?string $reason = null): Booking
    {
        $booking->update([
            'status' => Booking::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        $this->slots->release($booking);
        $this->audit($actor, 'booking.cancelled', $booking);

        return $booking->refresh();
    }

    public function checkIn(Booking $booking, User $staff): Booking
    {
        $booking->update([
            'status' => Booking::CHECKED_IN,
            'checked_in_at' => now(),
        ]);

        $this->audit($staff, 'booking.checked_in', $booking);

        return $booking->refresh();
    }

    public function markCompleted(Booking $booking, User $staff): Booking
    {
        $booking->update(['status' => Booking::COMPLETED]);
        $this->audit($staff, 'booking.completed', $booking);

        return $booking->refresh();
    }

    public function markNoShow(Booking $booking, User $staff): Booking
    {
        $booking->update(['status' => Booking::NO_SHOW]);
        $this->audit($staff, 'booking.no_show', $booking);

        return $booking->refresh();
    }

    public function expire(Booking $booking): Booking
    {
        $booking->update(['status' => Booking::EXPIRED]);
        $this->slots->release($booking);

        return $booking->refresh();
    }

    /**
     * Hall booking business rules, applied authoritatively on top of the form request.
     */
    public function validateHallRules(Hall $hall, CarbonInterface $start, CarbonInterface $end): void
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $minutes = $start->diffInMinutes($end);

        if ($minutes < $hall->min_hours * 60) {
            throw ValidationException::withMessages([
                'end' => "The minimum booking for {$hall->name} is {$hall->min_hours} hours.",
            ]);
        }

        if ($minutes % SlotManager::SLOT_MINUTES !== 0) {
            throw ValidationException::withMessages([
                'end' => 'Bookings must be in 30-minute increments.',
            ]);
        }

        foreach ([$start->copy(), $end->copy()->subMinute()] as $moment) {
            $hours = $hall->openingHoursFor($moment);

            if (! $hours) {
                throw ValidationException::withMessages([
                    'start' => "{$hall->name} is closed on this day.",
                ]);
            }

            $opens = Carbon::parse($moment->toDateString().' '.$hours->opens_at);
            $closes = Carbon::parse($moment->toDateString().' '.$hours->closes_at);

            if ($moment->lt($opens) || $moment->gt($closes)) {
                throw ValidationException::withMessages([
                    'start' => "Bookings must fit within opening hours ({$hours->opens_at}–{$hours->closes_at}).",
                ]);
            }
        }

        if (! $hall->rateFor($start)) {
            throw ValidationException::withMessages([
                'start' => 'This day type has no published rate yet, so it cannot be booked.',
            ]);
        }
    }

    private function lockResource(string $resourceType, int $resourceId): void
    {
        match ($resourceType) {
            'hall' => Hall::query()->whereKey($resourceId)->lockForUpdate()->first(),
            'event' => \App\Models\Event::query()->whereKey($resourceId)->lockForUpdate()->first(),
            'table' => \App\Models\DiningTable::query()->whereKey($resourceId)->lockForUpdate()->first(),
            default => null,
        };
    }

    public function resourceClass(string $resourceType): string
    {
        return match ($resourceType) {
            'hall' => Hall::class,
            'event' => \App\Models\Event::class,
            'table' => \App\Models\DiningTable::class,
            default => throw ValidationException::withMessages(['resource' => 'Unknown resource type.']),
        };
    }

    private function audit(User $actor, string $action, Booking $booking): void
    {
        AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'new_values' => ['status' => $booking->status],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}