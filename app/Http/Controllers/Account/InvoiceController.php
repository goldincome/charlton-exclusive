<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.invoices.index', [
            'invoices' => $request->user()->invoices()->latest()->paginate(15),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        return view('account.invoices.show', [
            'invoice' => $invoice->load(['lines', 'payments']),
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice->load(['lines', 'payments', 'user']),
        ])->download($invoice->number.'.pdf');
    }
}