<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BookableItem;
use App\Models\CartItem;
use App\Models\Event;
use App\Models\Hall;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Section;
use App\Models\SectionUsage;
use App\Services\BookingService;
use App\Services\PricingService;
use App\Services\SectionPricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;

class CartController extends Controller
{
    public function __construct(private SectionPricingService $sectionPricing, private PricingService $pricing) {}

    public function index(Request $request): View
    {
        if ($request->user() && session()->has('cart_items')) {
            $sessionItems = session('cart_items', []);
            foreach ($sessionItems as $sItem) {
                CartItem::query()->create([
                    'user_id' => $request->user()->id,
                    'bookable_item_id' => $sItem['bookable_item_id'],
                    'quantity' => $sItem['quantity'],
                    'metadata' => $sItem['metadata'] ?? [],
                ]);
            }
            session()->forget('cart_items');
        }

        $items = $this->getCartItems($request);

        $quotes = [];
        $grouped = $items instanceof Collection ? $items->groupBy(fn ($line) => $line->item->section_id) : $items->groupBy(fn ($line) => $line->item->section_id);

        foreach ($grouped as $sectionId => $lines) {
            $section = $lines->first()->item->section;
            $subtotal = $lines->sum(function ($line) {
                $meta = $line->metadata ?? [];
                if (isset($meta['price'])) {
                    return (int) $meta['price'];
                }

                return (int) $line->item->price * (int) $line->quantity;
            });
            $quotes[$sectionId] = $section
                ? $this->sectionPricing->quote($request->user(), $section, $subtotal)
                : ['allowed' => true, 'original_amount' => $subtotal, 'discount_amount' => 0, 'final_amount' => $subtotal, 'discount_type' => null, 'discount_value' => 0, 'plan_id' => null];
        }

        return view('account.cart.index', [
            'items' => $items,
            'quotes' => $quotes,
            'total' => collect($quotes)->sum('final_amount'),
        ]);
    }

    private function getCartItems(Request $request)
    {
        if ($request->user()) {
            return CartItem::query()->with(['item.section'])->where('user_id', $request->user()->id)->get();
        }

        $sessionItems = session('cart_items', []);

        return collect($sessionItems)->map(function ($arr, $key) {
            $item = BookableItem::with('section')->find($arr['bookable_item_id']);
            if (! $item) {
                return null;
            }

            return (object) [
                'id' => $key,
                'bookable_item_id' => $arr['bookable_item_id'],
                'quantity' => $arr['quantity'],
                'metadata' => $arr['metadata'] ?? [],
                'item' => $item,
            ];
        })->filter();
    }

    public function add(Request $request, BookableItem $item): RedirectResponse
    {
        $item->loadMissing('section');
        abort_unless($item->is_active && $item->section?->active, 404);

        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:50']]);
        $qty = max(1, (int) ($data['quantity'] ?? 1));

        if ($request->user()) {
            $row = CartItem::query()->firstOrNew([
                'user_id' => $request->user()->id,
                'bookable_item_id' => $item->id,
            ]);
            $row->quantity = min(50, (int) ($row->quantity ?? 0) + $qty);
            $row->save();
        } else {
            $cart = session('cart_items', []);
            $found = false;
            foreach ($cart as &$cItem) {
                if ($cItem['bookable_item_id'] == $item->id) {
                    $cItem['quantity'] = min(50, $cItem['quantity'] + $qty);
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $cart[] = [
                    'bookable_item_id' => $item->id,
                    'quantity' => $qty,
                    'metadata' => [],
                ];
            }
            session(['cart_items' => $cart]);
        }

        return back()->with('status', 'Added to cart.');
    }

    public function addHall(Request $request, Hall $hall): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'slot_selection' => ['nullable', 'array'],
            'slot_selection.*' => ['required', 'string'],
            'guests' => ['required', 'integer', 'min:1', 'max:'.$hall->capacity],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $slotSelections = $data['slot_selection'] ?? [];
        $calculatedHours = ! empty($slotSelections) ? count($slotSelections) : max(1, (int) round(Carbon::parse($data['start_time'])->diffInMinutes(Carbon::parse($data['end_time'])) / 60));

        app(BookingService::class)->validateHallRules($hall, Carbon::parse($data['start_time']), Carbon::parse($data['end_time']));

        $meetingSection = Section::query()->firstOrCreate(
            ['slug' => 'meeting-rooms'],
            ['name' => 'Meeting Rooms', 'type' => Section::TYPE_SLOT, 'access_type' => Section::ACCESS_MEMBERS_ONLY, 'active' => true]
        );

        $rate = $hall->rateFor(Carbon::parse($data['start_time']));
        $unitPrice = $rate ? (int) $rate->standard_price_per_hour : 5000;

        $hasDiscount = false;
        if ($user && $user->canGetMemberRates() && $rate && (int) $rate->member_discount_value > 0) {
            $hasDiscount = true;
        }

        if ($hasDiscount) {
            $quote = $this->pricing->hallQuote($hall, Carbon::parse($data['start_time']), Carbon::parse($data['end_time']), true);
            $baseMin = max(1, Carbon::parse($data['start_time'])->diffInMinutes(Carbon::parse($data['end_time'])) / 60);
            $totalPrice = (int) round(($quote['final_amount'] / $baseMin) * $calculatedHours);
        } else {
            $totalPrice = (int) round($unitPrice * $calculatedHours);
        }

        $bookableItem = BookableItem::query()->firstOrCreate(
            ['source_type' => Hall::class, 'source_id' => $hall->id],
            [
                'section_id' => $meetingSection->id,
                'name' => $hall->name,
                'description' => $hall->description,
                'price' => $unitPrice,
                'pricing_unit' => 'hour',
                'capacity' => $hall->capacity,
                'is_active' => (bool) $hall->active,
            ]
        );

        $metadata = [
            'type' => 'hall',
            'hall_id' => $hall->id,
            'hall_name' => $hall->name,
            'booking_date' => $data['booking_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_selections' => $slotSelections,
            'guests' => $data['guests'],
            'notes' => $data['notes'] ?? null,
            'price' => $totalPrice,
        ];

        if ($user) {
            CartItem::query()->create([
                'user_id' => $user->id,
                'bookable_item_id' => $bookableItem->id,
                'quantity' => $calculatedHours,
                'metadata' => $metadata,
            ]);
        } else {
            $cart = session('cart_items', []);
            $cart[] = [
                'bookable_item_id' => $bookableItem->id,
                'quantity' => $calculatedHours,
                'metadata' => $metadata,
            ];
            session(['cart_items' => $cart]);
        }

        return redirect()->route('cart.index')->with('status', 'Meeting room added to cart.');
    }

    public function addEvent(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'tickets' => ['required', 'integer', 'min:1', 'max:'.$event->tickets_per_booking],
        ]);

        $eventsSection = Section::query()->firstOrCreate(
            ['slug' => 'events'],
            ['name' => 'Events', 'type' => Section::TYPE_SLOT, 'access_type' => Section::ACCESS_MEMBERS_ONLY, 'active' => true]
        );

        $tickets = (int) $data['tickets'];
        $hasDiscount = false;
        if ($user && $user->canGetMemberRates() && (int) $event->member_discount_value > 0) {
            $hasDiscount = true;
        }
        $quote = $this->pricing->eventQuote($event, $hasDiscount, $tickets);
        $totalPrice = $quote['final_amount'];

        $bookableItem = BookableItem::query()->firstOrCreate(
            ['source_type' => Event::class, 'source_id' => $event->id],
            [
                'section_id' => $eventsSection->id,
                'name' => $event->name,
                'description' => $event->description,
                'price' => (int) $event->price_per_ticket,
                'pricing_unit' => 'flat',
                'capacity' => $event->capacity,
                'is_active' => (bool) $event->active,
            ]
        );

        $metadata = [
            'type' => 'event',
            'event_id' => $event->id,
            'event_name' => $event->name,
            'starts_at' => $event->starts_at->toDateTimeString(),
            'ends_at' => $event->ends_at->toDateTimeString(),
            'tickets' => $tickets,
            'price' => $totalPrice,
        ];

        if ($user) {
            CartItem::query()->create([
                'user_id' => $user->id,
                'bookable_item_id' => $bookableItem->id,
                'quantity' => $tickets,
                'metadata' => $metadata,
            ]);
        } else {
            $cart = session('cart_items', []);
            $cart[] = [
                'bookable_item_id' => $bookableItem->id,
                'quantity' => $tickets,
                'metadata' => $metadata,
            ];
            session(['cart_items' => $cart]);
        }

        return redirect()->route('cart.index')->with('status', 'Event tickets added to cart.');
    }

    public function remove(Request $request, BookableItem $item): RedirectResponse
    {
        if ($request->user()) {
            CartItem::query()->where('user_id', $request->user()->id)->where('bookable_item_id', $item->id)->delete();
        } else {
            $cart = session('cart_items', []);
            $cart = array_filter($cart, fn ($line) => ($line['bookable_item_id'] ?? null) != $item->id);
            session(['cart_items' => array_values($cart)]);
        }

        return back()->with('status', 'Item removed from cart.');
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login')->with('status', 'Please sign in or create an account to proceed to checkout.');
        }

        $cart = $this->getCartItems($request);
        abort_if($cart->isEmpty(), 422, 'Your cart is empty.');

        foreach ($cart as $line) {
            if (! $line->item || ! $line->item->is_active || ! $line->item->section?->active) {
                return back()->with('status', 'One or more items are no longer available.');
            }
            if ($line->item->section->isMembersOnly() && ! $user->canGetMemberRates()) {
                return back()->with('status', 'One or more items require active membership.');
            }
        }

        $order = DB::transaction(function () use ($user, $cart) {
            $planId = $user->membership?->plan_id ?: $user->membership?->membership_category_id;
            $totals = ['original_amount' => 0, 'discount_amount' => 0, 'final_amount' => 0];
            $primarySectionId = $cart->first()->item->section_id;
            $primaryDiscountType = null;
            $primaryDiscountValue = 0;

            $order = Order::query()->create([
                'user_id' => $user->id,
                'plan_id' => $planId,
                'section_id' => $primarySectionId,
                'status' => 'pending',
                'original_amount' => 0,
                'discount_amount' => 0,
                'final_amount' => 0,
            ]);

            foreach ($cart->groupBy(fn ($line) => $line->item->section_id) as $sectionId => $lines) {
                $section = $lines->first()->item->section;
                $subtotal = $lines->sum(function ($line) {
                    $meta = $line->metadata ?? [];
                    if (isset($meta['price'])) {
                        return (int) $meta['price'];
                    }

                    return (int) $line->item->price * (int) $line->quantity;
                });
                $quote = $this->sectionPricing->quote($user, $section, $subtotal);
                if (! ($quote['allowed'] ?? true)) {
                    throw ValidationException::withMessages(['cart' => $quote['reason'] ?? 'Unable to price cart.']);
                }
                $ratio = $subtotal > 0 ? (($quote['final_amount'] ?? $subtotal) / $subtotal) : 1;
                foreach ($lines as $line) {
                    $meta = $line->metadata ?? [];
                    $lineOriginal = isset($meta['price']) ? (int) $meta['price'] : (int) $line->item->price * (int) $line->quantity;
                    $lineFinal = (int) round($lineOriginal * $ratio);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'bookable_item_id' => $line->bookable_item_id,
                        'section_id' => $line->item->section_id,
                        'name' => $line->item->name,
                        'unit_price' => (int) $line->item->price,
                        'quantity' => (int) $line->quantity,
                        'original_amount' => $lineOriginal,
                        'discount_amount' => max(0, $lineOriginal - $lineFinal),
                        'final_amount' => $lineFinal,
                    ]);

                    if (($meta['type'] ?? '') === 'hall') {
                        $hall = Hall::query()->findOrFail($meta['hall_id']);
                        app(BookingService::class)->request($user, 'hall', $hall->id, [
                            'start' => Carbon::parse($meta['start_time']),
                            'end' => Carbon::parse($meta['end_time']),
                            'guests' => $meta['guests'] ?? 1,
                            'notes' => $meta['notes'] ?? null,
                        ]);
                    } elseif (($meta['type'] ?? '') === 'event') {
                        $event = Event::query()->findOrFail($meta['event_id']);
                        app(BookingService::class)->request($user, 'event', $event->id, [
                            'start' => $event->starts_at,
                            'end' => $event->ends_at,
                            'guests' => $meta['tickets'] ?? 1,
                        ]);
                    }
                }
                $totals['original_amount'] += (int) $quote['original_amount'];
                $totals['discount_amount'] += (int) $quote['discount_amount'];
                $totals['final_amount'] += (int) $quote['final_amount'];
                $primaryDiscountType = $quote['discount_type'] ?? $primaryDiscountType;
                $primaryDiscountValue = (int) ($quote['discount_value'] ?? $primaryDiscountValue);
            }

            $order->update($totals + ['discount_type' => $primaryDiscountType, 'discount_value' => $primaryDiscountValue]);

            return $order->refresh();
        });

        // Clear cart
        if ($user) {
            CartItem::query()->where('user_id', $user->id)->delete();
        }
        session()->forget('cart_items');

        if ((int) $order->final_amount <= 0) {
            $order->update(['status' => 'paid']);
            foreach ($order->items as $item) {
                SectionUsage::query()->updateOrCreate(
                    ['usable_type' => Order::class, 'usable_id' => $order->id, 'section_id' => (int) $item->section_id],
                    [
                        'user_id' => $user->id,
                        'plan_id' => $order->plan_id,
                        'original_amount' => (int) $item->original_amount,
                        'discount_amount' => (int) $item->discount_amount,
                        'final_amount' => (int) $item->final_amount,
                        'discount_type' => $order->discount_type,
                        'discount_value' => (int) $order->discount_value,
                        'used_at' => now(),
                        'metadata' => ['reference' => $order->reference],
                    ]
                );
            }

            return redirect()->route('cart.index')->with('status', 'Order confirmed (no payment required).');
        }

        if (! config('cashier.secret')) {
            return redirect()->route('cart.index')->with('status', 'Stripe is not configured. Order '.$order->reference.' created as pending.');
        }

        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $user->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => 'Section order '.$order->reference],
                    'unit_amount' => (int) $order->final_amount,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('cart.success').'?session_id={CHECKOUT_SESSION_ID}&order='.$order->id,
            'cancel_url' => route('cart.index'),
            'metadata' => ['type' => 'order', 'order_id' => (string) $order->id],
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        return redirect()->route('cart.index')->with('status', 'Thank you. Your payment is being confirmed.');
    }

    public function services(Request $request): View
    {
        $query = BookableItem::query()->with('section')
            ->where('is_active', true)
            ->whereHas('section', fn ($q) => $q->where('active', true));

        if (! $request->user()?->canGetMemberRates()) {
            $query->whereHas('section', fn ($q) => $q->where('access_type', 'open'));
        }

        return view('account.cart.services', [
            'items' => $query->orderBy('section_id')->orderBy('name')->get()->groupBy('section.name'),
        ]);
    }
}
