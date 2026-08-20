<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Render (or re-render) the booking's invoice PDF and return where it lives.
     *
     * @return array{path: string, url: string, filename: string}
     */
    public function generateInvoicePdf(Booking $booking): array
    {
        $booking->loadMissing(['customer', 'hall', 'lawn', 'package', 'addons', 'payments']);

        $pdf = Pdf::loadView('bookings.invoice', ['booking' => $booking, 'forPdf' => true])
            ->setPaper('a4');

        $path = $this->pathFor($booking);
        Storage::disk('public')->put($path, $pdf->output());

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => basename($path),
        ];
    }

    /** Storage path for a booking's invoice, derived from its number. */
    public function pathFor(Booking $booking): string
    {
        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '', $booking->formatted_booking_number);

        return 'invoices/invoice_'.$safe.'.pdf';
    }

    /**
     * Public URL for the invoice, generating it on first request.
     * The old code guessed the filename and 404'd whenever the PDF had never
     * been rendered or the booking had since been edited.
     */
    public function urlFor(Booking $booking): string
    {
        $path = $this->pathFor($booking);

        if (! Storage::disk('public')->exists($path)) {
            return $this->generateInvoicePdf($booking)['url'];
        }

        return Storage::disk('public')->url($path);
    }

    /** Drop a stale PDF so the next request re-renders it. */
    public function forget(Booking $booking): void
    {
        Storage::disk('public')->delete($this->pathFor($booking));
    }

    /* ------------------------------------------------------------------ sharing */

    /**
     * WhatsApp deep link with the booking summary pre-filled.
     * Returns null when the customer has no phone number on file.
     */
    public function generateWhatsAppUrl(Booking $booking, string $invoiceUrl): ?string
    {
        $phone = $this->formatPhoneForWhatsApp($booking->customer?->phone);

        if (! $phone) {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->messageBody($booking, $invoiceUrl));
    }

    /** Gmail compose link. Null when the customer has no email on file. */
    public function generateGmailUrl(Booking $booking, string $invoiceUrl): ?string
    {
        $email = $booking->customer?->email;

        if (! $email) {
            return null;
        }

        return 'https://mail.google.com/mail/?view=cm&fs=1'
            .'&to='.rawurlencode($email)
            .'&su='.rawurlencode($this->subject($booking))
            .'&body='.rawurlencode($this->messageBody($booking, $invoiceUrl));
    }

    /** Plain mailto: fallback for users without Gmail. */
    public function generateMailtoUrl(Booking $booking, string $invoiceUrl): ?string
    {
        $email = $booking->customer?->email;

        if (! $email) {
            return null;
        }

        return 'mailto:'.$email
            .'?subject='.rawurlencode($this->subject($booking))
            .'&body='.rawurlencode($this->messageBody($booking, $invoiceUrl));
    }

    private function subject(Booking $booking): string
    {
        return 'Invoice for Booking #'.$booking->formatted_booking_number
            .' - '.($booking->hall->name ?? 'Hall');
    }

    /**
     * Shared message body. Every relation is treated as optional so a deleted
     * lawn or a missing phone number can never turn a share link into a crash.
     */
    private function messageBody(Booking $booking, string $invoiceUrl): string
    {
        $hallName = $booking->hall->name ?? 'our venue';
        $venue = trim(($booking->hall->name ?? '').' - '.($booking->lawn->name ?? ''), ' -');
        $paid = $booking->amount_paid;
        $balance = $booking->balance_due;

        $lines = [
            'Dear '.($booking->customer->name ?? 'Customer').',',
            '',
            'Thank you for booking with '.$hallName.'.',
            '',
            'Booking Details',
            'Booking #: '.$booking->formatted_booking_number,
        ];

        if ($booking->event_type_label) {
            $lines[] = 'Event: '.$booking->event_type_label;
        }

        if ($venue !== '') {
            $lines[] = 'Venue: '.$venue;
        }

        if ($booking->start_datetime) {
            $lines[] = 'Date: '.$booking->start_datetime->format('d M Y');
            $lines[] = 'Time: '.$booking->start_datetime->format('h:i A')
                .' - '.($booking->end_datetime?->format('h:i A') ?? '');
        }

        if ($booking->guest_count) {
            $lines[] = 'Guests: '.number_format($booking->guest_count);
        }

        $lines = array_merge($lines, [
            '',
            'Payment Summary',
            'Total: Rs. '.number_format((float) $booking->total_amount, 2),
            'Received: Rs. '.number_format($paid, 2),
            'Balance Due: Rs. '.number_format($balance, 2),
            '',
            'Invoice: '.$invoiceUrl,
            '',
            'We look forward to hosting your event.',
            '',
            $hallName,
        ]);

        if ($booking->hall?->phone) {
            $lines[] = $booking->hall->phone;
        }

        return implode("\n", $lines);
    }

    /**
     * Normalise a Pakistani number to WhatsApp's country-code form.
     * Returns null for blank or unusable input rather than throwing.
     */
    private function formatPhoneForWhatsApp(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0092')) {
            $digits = substr($digits, 2);
        }

        if (! str_starts_with($digits, '92')) {
            $digits = '92'.ltrim($digits, '0');
        }

        // A Pakistani mobile is 92 + 10 digits; anything shorter is not dialable.
        return strlen($digits) >= 11 ? $digits : null;
    }
}
