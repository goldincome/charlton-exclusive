<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\RefundService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.invoices.index', [
            'invoices' => Invoice::query()->with('user')
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load(['lines', 'payments.refunds', 'user']),
        ]);
    }

    public function store(Request $request, InvoiceService $invoices): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'description' => ['required', 'string', 'max:190'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'integer', 'min:0'],
            'product_type' => ['required', 'in:hall,table,event,food,membership'],
        ]);

        $invoice = $invoices->issue(User::query()->findOrFail($data['user_id']), [[
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'product_type' => $data['product_type'],
        ]]);

        return redirect()->route('admin.invoices.show', $invoice)->with('status', 'Invoice issued with sequential number.');
    }

    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:190'],
        ]);

        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'amount' => $data['amount'],
            'currency' => 'gbp',
            'method' => 'manual',
            'status' => 'succeeded',
            'paid_at' => now(),
            'stripe_payment_intent_id' => $data['reference'] ?? null,
        ]);

        $invoice->update(['status' => Invoice::PAID]);

        // Run the downstream effects (membership activation) without recording a
        // second payment or sending the online-payment receipt.
        app(\App\Services\InvoicePaymentService::class)->fulfill($invoice);

        return back()->with('status', 'Payment recorded.');
    }

    public function refund(Request $request, Payment $payment, RefundService $refunds): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $refunds->record($payment, $data['amount'], $data['reason'], $request->user());

        return back()->with('status', 'Refund recorded and audited.');
    }
}