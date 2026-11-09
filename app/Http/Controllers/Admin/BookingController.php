<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');

        return view('admin.bookings.index', [
            'bookings' => Booking::query()
                ->with(['user', 'bookingable'])
                ->when($status, fn ($q) => $q->where('status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'statuses' => [
                Booking::REQUESTED, Booking::APPROVED, Booking::CONFIRMED,
                Booking::CHECKED_IN, Booking::COMPLETED, Booking::CANCELLED,
                Booking::REJECTED, Booking::EXPIRED, Booking::NO_SHOW,
            ],
        ]);
    }

    public function show(Booking $booking): View
    {
        return view('admin.bookings.show', [
            'booking' => $booking->load(['user', 'bookingable', 'slots', 'invoices']),
        ]);
    }

    public function approve(Booking $booking): RedirectResponse
    {
        $this->authorize('approve', Booking::class);
        $this->bookings->approve($booking, auth()->user());

        return back()->with('status', 'Booking approved. The member has 72 hours to pay.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('approve', Booking::class);
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $this->bookings->reject($booking, auth()->user(), $data['reason']);

        return back()->with('status', 'Booking rejected and slot released.');
    }
}