<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private PricingService $pricing)
    {
    }

    /**
     * Issue an invoice with sequential numbering inside a locked transaction.
     *
     * @param  array<int, array{description:string, quantity?:int, unit_price:int, product_type:string}>  $lines
     */
    public function issue(User $user, array $lines, ?Model $invoiceable = null): Invoice
    {
        return DB::transaction(function () use ($user, $lines, $invoiceable) {
            $year = now()->year;

            $counter = DB::table('invoice_counters')->lockForUpdate()->first();

            if (! $counter) {
                DB::table('invoice_counters')->insert([
                    'year' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $counter = DB::table('invoice_counters')->lockForUpdate()->first();
            }

            $next = (int) $counter->last_number + 1;

            DB::table('invoice_counters')->where('id', $counter->id)->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

            $number = sprintf('CEC-%s-%06d', $year, $next);

            $subtotal = 0;
            $vatTotal = 0;

            $invoice = Invoice::query()->create([
                'number' => $number,
                'user_id' => $user->id,
                'invoiceable_type' => $invoiceable?->getMorphClass(),
                'invoiceable_id' => $invoiceable?->getKey(),
                'issued_at' => now()->toDateString(),
                'subtotal' => 0,
                'vat_amount' => 0,
                'total' => 0,
                'status' => Invoice::UNPAID,
            ]);

            foreach ($lines as $line) {
                $quantity = (int) ($line['quantity'] ?? 1);
                $unit = (int) $line['unit_price'];
                $gross = $unit * $quantity;

                $split = $line['product_type'] === 'membership'
                    ? $this->pricing->membershipSplit($gross)
                    : $this->pricing->vatInclusiveSplit($gross, $line['product_type']);

                InvoiceLineItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'description' => $line['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'net' => $split['net'],
                    'vat_rate' => $split['rate'],
                    'vat' => $split['vat'],
                    'gross' => $split['gross'],
                ]);

                $subtotal += $split['net'];
                $vatTotal += $split['vat'];
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'vat_amount' => $vatTotal,
                'total' => $subtotal + $vatTotal,
            ]);

            return $invoice->refresh();
        });
    }
}