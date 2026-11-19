<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHallBookingRequest;
use App\Models\Booking;
use App\Models\DiningTable;
use App\Models\Event;
use App\Models\Hall;
use App\Models\SiteHour;
use App\Services\BookingService;
use App\Services\PricingService;
use App\Services\SlotManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookings,
        private PricingService $pricing,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        return view('account.bookings.index', [
            'bookings' => $request->user()->bookings()->with('bookingable')->latest()->paginate(10),
        ]);
    }

    public function createRoom(Hall $hall): View
    {
        return view('public.booking.room', [
            'hall' => $hall->load(['rates', 'images', 'hours']),
            'memberRate' => $this->pricing->hallHourlyRate($hall, now()->addDay(), auth()->user()?->canGetMemberRates() ?? false),
        ]);
    }

    public function getTimeSlots(Request $request, SlotManager $slotManager)
    {
        $hallId = $request->input('hall_id');
        $hall = Hall::query()->findOrFail($hallId);
        $date = Carbon::parse($request->input('booking_date'));

        $siteHour = SiteHour::openingHoursFor($date);
        if (! $siteHour || ! $siteHour->is_open) {
            return response()->json(['slots' => []]);
        }

        $openTime = Carbon::parse($date->toDateString().' '.$siteHour->opens_at);
        $closeTime = Carbon::parse($date->toDateString().' '.$siteHour->closes_at);

        $slots = [];
        $cursor = $openTime->copy();
        while ($cursor->lt($closeTime)) {
            $end = $cursor->copy()->addMinutes(60);
            if ($end->gt($closeTime)) {
                break;
            }

            $slotStartStr = $cursor->toDateTimeString();
            $conflicts = $slotManager->conflicts('hall', $hall->id, [$slotStartStr]);

            if ($conflicts->isEmpty()) {
                $slots[] = $cursor->format('g:i A').' - '.$end->format('g:i A');
            }
            $cursor->addMinutes(60);
        }

        return response()->json(['slots' => $slots]);
    }

    public function storeHall(StoreHallBookingRequest $request, Hall $hall): RedirectResponse
    {
        $this->bookings->validateHallRules($hall, $request->date('start_time'), $request->date('end_time'));

        $booking = $this->bookings->request($request->user(), 'hall', $hall->id, [
            'start' => $request->date('start_time'),
            'end' => $request->date('end_time'),
            'guests' => $request->integer('guests', 1),
            'notes' => $request->string('notes')->toString() ?: null,
        ]);

        return redirect()->route('account.bookings.show', $booking)
            ->with('status', 'Your request has been sent to the club. We will confirm within 72 hours.');
    }

    public function storeEvent(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'tickets' => ['required', 'integer', 'min:1', 'max:'.$event->tickets_per_booking],
        ]);

        $booking = $this->bookings->request($request->user(), 'event', $event->id, [
            'start' => $event->starts_at,
            'end' => $event->ends_at,
            'guests' => $data['tickets'],
        ]);

        return redirect()->route('account.bookings.show', $booking)->with('status', 'Your place request has been received.');
    }

    public function createTable(DiningTable $table): View
    {
        return view('public.booking.table', ['table' => $table->load('category')]);
    }

    public function storeTable(Request $request, DiningTable $table): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'guests' => ['required', 'integer', 'min:1', 'max:'.$table->capacity],
        ]);

        $start = Carbon::parse($data['date'].' '.$data['time'], 'Europe/London')->utc();

        $booking = $this->bookings->request($request->user(), 'table', $table->id, [
            'start' => $start,
            'end' => $start->copy()->addHours(2),
            'guests' => $data['guests'],
        ]);

        return redirect()->route('account.bookings.show', $booking)->with('status', 'Your table request has been received.');
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        return view('account.bookings.show', [
            'booking' => $booking->load(['bookingable', 'invoices', 'user']),
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->bookings->cancel($booking, $request->user(), $request->string('reason')->toString() ?: null);

        return back()->with('status', 'Booking cancelled. Any refund due will be processed to the original payment method.');
    }
}
