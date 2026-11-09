<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class InvoicePaymentService
{
    /**
     * Mark an invoice as paid and run the downstream effects (bookings, membership
     * activation, confirmation email). Idempotent: returns true only when the
     * invoice was not already paid.
     */
    public function fulfill(
        Invoice $invoice,
        ?string $paymentIntentId = null,
        ?string $sessionId = null,
        ?string $subscriptionId = null,
        ?string $billingFrequency = null
    ): bool {
        $invoice->loadMissing(['user', 'invoiceable']);
        $wasPaid = $invoice->status === Invoice::PAID;

        if (! $wasPaid) {
            Payment::query()->create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'stripe_payment_intent_id' => $paymentIntentId,
                'amount' => $invoice->total,
                'currency' => 'gbp',
                'method' => 'card',
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);

            $invoice->update(['status' => Invoice::PAID]);
        }

        if ($invoice->invoiceable_type === Booking::class && $invoice->invoiceable) {
            app(BookingService::class)->confirm($invoice->invoiceable);
        }

        if ($invoice->invoiceable_type === Application::class && $invoice->invoiceable) {
            $application = $invoice->invoiceable->loadMissing(['user', 'plan', 'category']);

            $membership = app(MembershipService::class)->activate(
                $application,
                $billingFrequency ?: $application->billing_frequency,
                $subscriptionId,
            );

            if (! $wasPaid && $application->user) {
                try {
                    app(MailService::class)->send(
                        'application_payment_received',
                        $application->user,
                        'Welcome to Charlton Exclusive Club — membership active',
                        [
                            'greeting' => 'Hello '.($application->user->name ?: 'there').',',
                            'planName' => $application->resolvedPlan()?->name ?? 'Membership',
                            'applicationReference' => $application->reference,
                            'invoiceNumber' => $invoice->number,
                            'amountPaid' => $invoice->grossFormatted(),
                            'billingFrequency' => $membership->billing_frequency === Membership::MONTHLY ? 'Monthly' : 'Yearly',
                            'renewsAt' => $membership->renews_at?->format('j F Y'),
                            'membershipUrl' => route('account.applications.index'),
                        ] + $this->clubData()
                    );
                } catch (\Throwable $e) {
                    Log::warning('Application payment email failed', [
                        'application_id' => $application->id,
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return ! $wasPaid;
    }

    private function clubData(): array
    {
        return [
            'clubName' => config('club.name'),
            'clubAddress' => config('club.address'),
            'clubPhone' => config('club.phone'),
            'clubEmail' => config('club.email'),
        ];
    }
}
