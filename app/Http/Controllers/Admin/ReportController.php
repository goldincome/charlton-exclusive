<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'revenue' => Payment::query()->where('status', 'succeeded')->whereBetween('paid_at', [$from, $to])->sum('amount'),
            'refunds' => Refund::query()->where('status', 'succeeded')->whereBetween('created_at', [$from, $to])->sum('amount'),
            'vat' => InvoiceLineItem::query()->whereHas('invoice', fn ($q) => $q->where('status', Invoice::PAID)->whereBetween('issued_at', [$from, $to]))->sum('vat'),
            'byModule' => InvoiceLineItem::query()
                ->join('invoices', 'invoices.id', '=', 'invoice_line_items.invoice_id')
                ->whereBetween('invoices.issued_at', [$from, $to])
                ->selectRaw('invoice_line_items.description as description, SUM(invoice_line_items.gross) as gross, SUM(invoice_line_items.vat) as vat')
                ->groupBy('invoice_line_items.description')
                ->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        $rows = Payment::query()->with('invoice')->whereBetween('paid_at', [$from, $to])->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice', 'Paid at', 'Amount (pence)', 'Currency', 'Status']);
            foreach ($rows as $row) {
                fputcsv($out, [$row->invoice?->number, $row->paid_at, $row->amount, $row->currency, $row->status]);
            }
            fclose($out);
        }, 'revenue-'.now()->format('Y-m-d').'.csv');
    }
}