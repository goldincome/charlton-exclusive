<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FoodOrder;
use App\Models\MenuItem;
use App\Services\PricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoodOrderController extends Controller
{
    public function __construct(private PricingService $pricing)
    {
    }

    public function index(Request $request): View
    {
        return view('account.food-orders.index', [
            'orders' => $request->user()->foodOrders()->with('items.menuItem')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('account.food-orders.create', [
            'items' => MenuItem::query()->active()->with('category')->orderBy('name')->get(),
            'hallBookings' => auth()->user()->bookings()
                ->where('status', Booking::CONFIRMED)
                ->where('bookingable_type', \App\Models\Hall::class)
                ->where('start_time', '>=', now())
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:personal,party'],
            'requested_for' => ['required', 'date', 'after:now'],
            'dietary_notes' => ['nullable', 'string', 'max:1000'],
            'dietary_consent' => ['accepted'],
            'location' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $order = DB::transaction(function () use ($request, $data) {
            $order = FoodOrder::query()->create([
                'user_id' => $request->user()->id,
                'booking_id' => $data['booking_id'] ?? null,
                'type' => $data['type'],
                'status' => FoodOrder::REQUESTED,
                'requested_for' => $data['requested_for'],
                'dietary_notes' => $data['dietary_notes'] ?? null,
                'location' => $data['location'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'expires_at' => now()->addHours(72),
            ]);

            foreach ($data['items'] as $row) {
                $item = MenuItem::query()->findOrFail($row['id']);
                $order->items()->create([
                    'menu_item_id' => $item->id,
                    'quantity' => (int) $row['qty'],
                    'unit_price' => $item->price,
                ]);
            }

            $linked = $order->booking_id ? Booking::query()->find($order->booking_id) : null;
            $order->update(['price_locked' => $this->pricing->foodOrderTotal($order, $linked)]);

            return $order;
        });

        return redirect()->route('food-orders.show', $order)
            ->with('status', 'Your food order request has been sent for approval.');
    }

    public function show(FoodOrder $foodOrder): View
    {
        abort_unless($foodOrder->user_id === auth()->id() || auth()->user()->isStaffLike(), 403);

        return view('account.food-orders.show', [
            'order' => $foodOrder->load(['items.menuItem', 'booking']),
        ]);
    }
}