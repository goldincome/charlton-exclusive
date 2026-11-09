<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Booking;
use App\Models\FoodOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $revenue = Payment::query()->where('status', 'succeeded')->sum('amount');
        $refunds = Refund::query()->where('status', 'succeeded')->sum('amount');

        return view('admin.dashboard', [
            'stats' => [
                'members' => User::role('member')->count(),
                'applications' => Application::query()->whereIn('status', ['submitted', 'fees_notified'])->count(),
                'pending_bookings' => Booking::query()->where('status', Booking::REQUESTED)->count(),
                'pending_orders' => FoodOrder::query()->where('status', FoodOrder::REQUESTED)->count(),
                'revenue' => $revenue - $refunds,
                'unpaid_invoices' => Invoice::query()->where('status', Invoice::UNPAID)->count(),
            ],
            'recentBookings' => Booking::query()->with(['user', 'bookingable'])->latest()->take(8)->get(),
            'recentApplications' => Application::query()->with(['user', 'plan'])->latest()->take(8)->get(),
            'revenueByModule' => DB::table('invoice_line_items')
                ->join('invoices', 'invoices.id', '=', 'invoice_line_items.invoice_id')
                ->selectRaw('SUM(invoice_line_items.gross) as gross, COUNT(DISTINCT invoices.id) as invoices')
                ->where('invoices.status', Invoice::PAID)
                ->first(),
        ]);
    }
}