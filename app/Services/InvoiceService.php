<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate PDF invoice for a booking
     *
     * @param Booking $booking
     * @return array ['path' => string, 'url' => string]
     */
    public function generateInvoicePdf(Booking $booking): array
    {
        // Load relationships needed for invoice
        $booking->load(['customer', 'hall', 'lawn']);

        // Generate PDF from invoice view
        $pdf = Pdf::loadView('bookings.invoice', compact('booking'));

        // Create filename
        $filename = 'invoice_' . $booking->formatted_booking_number . '.pdf';
        
        // Save to storage/app/public/invoices
        $path = 'invoices/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());

        // Return both storage path and public URL
        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename
        ];
    }

    /**
     * Generate WhatsApp message URL with pre-filled text
     *
     * @param Booking $booking
     * @param string $invoiceUrl
     * @return string
     */
    public function generateWhatsAppUrl(Booking $booking, string $invoiceUrl): string
    {
        $customer = $booking->customer;
        $phone = $this->formatPhoneForWhatsApp($customer->phone);

        $message = "Hello {$customer->name},\n\n";
        $message .= "Thank you for booking with {$booking->hall->name}!\n\n";
        $message .= "📋 *Booking Details:*\n";
        $message .= "Booking #: {$booking->formatted_booking_number}\n";
        $message .= "Hall/Lawn: {$booking->hall->name} - {$booking->lawn->name}\n";
        $message .= "Event Date: {$booking->start_datetime->format('d M, Y')}\n";
        $message .= "Event Time: {$booking->start_datetime->format('h:i A')} - {$booking->end_datetime->format('h:i A')}\n\n";
        $message .= "💰 *Payment Summary:*\n";
        $message .= "Total Amount: Rs. " . number_format($booking->booking_price, 2) . "\n";
        $message .= "Advance Paid: Rs. " . number_format($booking->advance_paid, 2) . "\n";
        $message .= "Balance Due: Rs. " . number_format($booking->booking_price - $booking->advance_paid, 2) . "\n\n";
        $message .= "📄 Invoice: {$invoiceUrl}\n\n";
        $message .= "We look forward to hosting your event!";

        // URL encode the message
        $encodedMessage = urlencode($message);

        // Return WhatsApp Web URL
        return "https://web.whatsapp.com/send?phone={$phone}&text={$encodedMessage}";
    }

    /**
     * Generate mailto URL with pre-filled email
     *
     * @param Booking $booking
     * @param string $invoiceUrl
     * @return string
     */
    public function generateMailtoUrl(Booking $booking, string $invoiceUrl): string
    {
        $customer = $booking->customer;
        
        $subject = "Invoice for Booking #{$booking->formatted_booking_number} - {$booking->hall->name}";
        
        $body = "Dear {$customer->name},\n\n";
        $body .= "Thank you for booking with {$booking->hall->name}!\n\n";
        $body .= "Please find your booking invoice details below:\n\n";
        $body .= "Booking Number: {$booking->formatted_booking_number}\n";
        $body .= "Hall/Lawn: {$booking->hall->name} - {$booking->lawn->name}\n";
        $body .= "Event Date: {$booking->start_datetime->format('d M, Y')}\n";
        $body .= "Event Time: {$booking->start_datetime->format('h:i A')} - {$booking->end_datetime->format('h:i A')}\n\n";
        $body .= "Total Amount: Rs. " . number_format($booking->booking_price, 2) . "\n";
        $body .= "Advance Paid: Rs. " . number_format($booking->advance_paid, 2) . "\n";
        $body .= "Balance Due: Rs. " . number_format($booking->booking_price - $booking->advance_paid, 2) . "\n\n";
        $body .= "Download Invoice: {$invoiceUrl}\n\n";
        $body .= "Please download the invoice PDF from the link above and keep it for your records.\n\n";
        $body .= "We look forward to hosting your event!\n\n";
        $body .= "Best regards,\n";
        $body .= "{$booking->hall->name}\n";
        $body .= "{$booking->hall->phone}";

        // URL encode subject and body
        $encodedSubject = rawurlencode($subject);
        $encodedBody = rawurlencode($body);

        // Return mailto URL
        return "mailto:{$customer->email}?subject={$encodedSubject}&body={$encodedBody}";
    }

    /**
     * Generate Gmail compose URL that opens in browser
     *
     * @param Booking $booking
     * @param string $invoiceUrl
     * @return string
     */
    public function generateGmailUrl(Booking $booking, string $invoiceUrl): string
    {
        $customer = $booking->customer;
        
        $subject = "Invoice for Booking #{$booking->formatted_booking_number} - {$booking->hall->name}";
        
        $body = "Dear {$customer->name},\n\n";
        $body .= "Thank you for booking with {$booking->hall->name}!\n\n";
        $body .= "Please find your booking invoice details below:\n\n";
        $body .= "Booking Number: {$booking->formatted_booking_number}\n";
        $body .= "Hall/Lawn: {$booking->hall->name} - {$booking->lawn->name}\n";
        $body .= "Event Date: {$booking->start_datetime->format('d M, Y')}\n";
        $body .= "Event Time: {$booking->start_datetime->format('h:i A')} - {$booking->end_datetime->format('h:i A')}\n\n";
        $body .= "Total Amount: Rs. " . number_format($booking->booking_price, 2) . "\n";
        $body .= "Advance Paid: Rs. " . number_format($booking->advance_paid, 2) . "\n";
        $body .= "Balance Due: Rs. " . number_format($booking->booking_price - $booking->advance_paid, 2) . "\n\n";
        $body .= "Download Invoice: {$invoiceUrl}\n\n";
        $body .= "Please download the invoice PDF from the link above and keep it for your records.\n\n";
        $body .= "We look forward to hosting your event!\n\n";
        $body .= "Best regards,\n";
        $body .= "{$booking->hall->name}\n";
        $body .= "{$booking->hall->phone}";

        // URL encode for Gmail
        $encodedSubject = rawurlencode($subject);
        $encodedBody = rawurlencode($body);
        $encodedTo = rawurlencode($customer->email);

        // Return Gmail compose URL
        return "https://mail.google.com/mail/?view=cm&fs=1&to={$encodedTo}&su={$encodedSubject}&body={$encodedBody}";
    }

    /**
     * Format phone number for WhatsApp (remove spaces, dashes, add country code if needed)
     *
     * @param string $phone
     * @return string
     */
    private function formatPhoneForWhatsApp(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If phone doesn't start with country code, assume Pakistan (+92)
        if (!str_starts_with($phone, '92')) {
            // Remove leading zero if present
            $phone = ltrim($phone, '0');
            $phone = '92' . $phone;
        }

        return $phone;
    }
}
