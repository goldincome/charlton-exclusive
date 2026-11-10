<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BookableItem;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SectionUsage;
use App\Services\PricingService;
use App\Services\SectionPricingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;

class CartController extends Controller
{
    public function __construct(private SectionPricingService $sectionPricing, private PricingService $pricing) {}

    public function index(Request $request): View
    {
        $items = CartItem::query()
            ->with(['item.section'])
            ->where('user_id', $request->user()->id)
            ->get();

        $quotes = [];
        foreach ($items->groupBy(fn ($line) => $line->item->section_id) as $sectionId => $lines) {
            $section = $lines->first()->item->section;
            $subtotal = $lines->sum(fn ($line) => (int) $line->item->price * (int) $line->quantity);
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

    public function add(Request $request, BookableItem $item): RedirectResponse
    {
        $item->loadMissing('section');
        abort_unless($item->is_active && $item->section?->active, 404);

        if ($item->section->isMembersOnly()) {
            abort_unless($request->user()->canGetMemberRates(), 403, 'This section is available to active members only.');
        }

        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:50']]);
        $qty = max(1, (int) ($data['quantity'] ?? 1));

        $row = CartItem::query()->firstOrNew([
            'user_id' => $request->user()->id,
            'bookable_item_id' => $item->id,
        ]);
        $row->quantity = min(50, (int) ($row->quantity ?? 0) + $qty);
        $row->save();

        return back()->with('status', 'Added to cart.');
    }

    public function remove(Request $request, BookableItem $item): RedirectResponse
    {
        CartItem::query()->where('user_id', $request->user()->id)->where('bookable_item_id', $item->id)->delete();

        return back()->with('status', 'Removed from cart.');
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = CartItem::query()->with(['item.section'])->where('user_id', $user->id)->get();
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

            foreach ($cart->groupBy(fn ($line) => $line->item->section_id) as $lines) {
                $section = $lines->first()->item->section;
                $subtotal = $lines->sum(fn ($line) => (int) $line->item->price * (int) $line->quantity);
                $quote = $this->sectionPricing->quote($user, $section, $subtotal);
                if (! ($quote['allowed'] ?? true)) {
                    throw ValidationException::withMessages(['cart' => $quote['reason'] ?? 'Unable to price cart.']);
                }
                $ratio = $subtotal > 0 ? (($quote['final_amount'] ?? $subtotal) / $subtotal) : 1;
                foreach ($lines as $line) {
                    $lineOriginal = (int) $line->item->price * (int) $line->quantity;
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

        if ((int) $order->final_amount <= 0) {
            $order->update(['status' => 'paid']);
            CartItem::query()->where('user_id', $user->id)->delete();
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

            return redirect()->route('account.cart.index')->with('status', 'Order confirmed (no payment required).');
        }

        if (! config('cashier.secret')) {
            return redirect()->route('account.cart.index')->with('status', 'Stripe is not configured. Order '.$order->reference.' created as pending.');
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
            'success_url' => route('account.cart.success').'?session_id={CHECKOUT_SESSION_ID}&order='.$order->id,
            'cancel_url' => route('account.cart.index'),
            'metadata' => ['type' => 'order', 'order_id' => (string) $order->id],
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        return redirect()->route('account.cart.index')->with('status', 'Thank you. Your payment is being confirmed.');
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
