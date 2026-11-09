<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SlotManager
{
    public const SLOT_MINUTES = 30;

    /**
     * Build the set of 30-minute slot start timestamps for a booking window,
     * including the trailing buffer slots.
     *
     * @return array<int, string>
     */
    public function slotsFor(CarbonInterface $start, CarbonInterface $end, int $bufferMinutes = 0): array
    {
        $start = Carbon::parse($start)->utc()->startOfMinute();
        $end = Carbon::parse($end)->utc()->startOfMinute()->addMinutes($bufferMinutes);

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $slots[] = $cursor->toDateTimeString();
            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    /**
     * Attempt to reserve all slots. Relies on the database unique index for the
     * final guarantee: if any slot is taken, the whole transaction fails.
     *
     * @param  array<int, string>  $slots
     *
     * @throws \Illuminate\Database\UniqueConstraintViolationException
     */
    public function reserve(string $resourceType, int $resourceId, array $slots, Booking $booking): void
    {
        $now = now();

        $rows = collect($slots)->map(fn (string $slot) => [
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'slot_start' => $slot,
            'booking_id' => $booking->id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        BookingSlot::query()->insert($rows);
    }

    public function release(Booking $booking): void
    {
        BookingSlot::query()->where('booking_id', $booking->id)->delete();
    }

    /**
     * @return Collection<int, BookingSlot>
     */
    public function conflicts(string $resourceType, int $resourceId, array $slots): Collection
    {
        return BookingSlot::query()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->whereIn('slot_start', $slots)
            ->get();
    }
}