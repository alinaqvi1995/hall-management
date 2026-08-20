<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Record a receipt or refund against a booking and re-derive its
     * payment status. Runs in a transaction so the ledger and the booking
     * status can never disagree.
     */
    public function record(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $direction = $data['direction'] ?? 'in';
            $amount = round((float) $data['amount'], 2);

            $this->guardAmount($booking, $amount, $direction);

            $payment = $booking->payments()->create([
                'hall_id' => $booking->hall_id,
                'receipt_number' => $this->generateReceiptNumber($booking),
                'amount' => $amount,
                'method' => $data['method'] ?? 'cash',
                'direction' => $direction,
                'reference' => $data['reference'] ?? null,
                'paid_on' => $data['paid_on'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'received_by' => Auth::id(),
            ]);

            $this->recalculateBookingStatus($booking->fresh());

            return $payment;
        });
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $booking = $payment->booking;
            $payment->delete();

            if ($booking) {
                $this->recalculateBookingStatus($booking->fresh());
            }
        });
    }

    /**
     * Derive payment_status from the ledger.
     *
     * Also mirrors the collected total into the legacy `advance_paid` column so
     * older reads keep working while the ledger stays authoritative.
     */
    public function recalculateBookingStatus(Booking $booking): void
    {
        $paid = $booking->amount_paid;
        $total = round((float) $booking->total_amount, 2);

        $status = match (true) {
            $booking->isCancelled() && $paid <= 0.009 => 'refunded',
            $total > 0 && $paid >= $total - 0.009 => 'paid',
            $paid > 0.009 => 'partial',
            default => 'pending',
        };

        $booking->newQuery()->whereKey($booking->getKey())->update([
            'payment_status' => $status,
            'advance_paid' => max($paid, 0),
        ]);

        $booking->setAttribute('payment_status', $status);
        $booking->setAttribute('advance_paid', max($paid, 0));
    }

    /**
     * A receipt must not push the collected amount past the bill, and a refund
     * must not exceed what was actually collected.
     */
    private function guardAmount(Booking $booking, float $amount, string $direction): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);
        }

        $paid = $booking->amount_paid;

        if ($direction === 'refund') {
            if ($amount > $paid + 0.009) {
                throw ValidationException::withMessages([
                    'amount' => 'Refund cannot exceed the amount already received (Rs. '
                        .number_format($paid, 2).').',
                ]);
            }

            return;
        }

        $balance = round((float) $booking->total_amount - $paid, 2);

        if ($balance <= 0.009) {
            throw ValidationException::withMessages([
                'amount' => 'This booking is already fully paid.',
            ]);
        }

        if ($amount > $balance + 0.009) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds the outstanding balance of Rs. '.number_format($balance, 2).'.',
            ]);
        }
    }

    /** Receipt number in the form RCPT-<booking number>-01. */
    private function generateReceiptNumber(Booking $booking): string
    {
        $prefix = 'RCPT-'.$booking->formatted_booking_number.'-';

        $latest = Payment::withTrashed()
            ->where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $sequence = 1;

        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $sequence = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }
}
