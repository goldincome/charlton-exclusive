<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(): View
    {
        return view('staff.bookings.index', [
            'bookings' => Booking::query()->with(['user', 'bookingable'])
                ->whereIn('status', [Booking::CONFIRMED, Booking::CHECKED_IN])
                ->orderBy('start_time')->paginate(20),
            'today' => Booking::query()->whereDate('start_time', today())->count(),
        ]);
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        $this->bookings->checkIn($booking, auth()->user());

        return back()->with('status', 'Guest checked in.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->bookings->markCompleted($booking, auth()->user());

        return back()->with('status', 'Booking marked completed.');
    }

    public function noShow(Booking $booking): RedirectResponse
    {
        $this->bookings->markNoShow($booking, auth()->user());

        return back()->with('status', 'Booking marked as a no-show.');
    }
}