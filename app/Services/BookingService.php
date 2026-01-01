<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function createBooking(array $bookingData): Booking
    {
        return DB::transaction(function () use ($bookingData) {
            $bookingData['created_by'] = Auth::id();
            $bookingData['updated_by'] = Auth::id();
            $bookingData['booking_price'] = $bookingData['booking_price'] ?? $bookingData['quote_price'];

            // Generate Booking Number
            if (empty($bookingData['booking_number'])) {
                $bookingData['booking_number'] = $this->generateBookingNumber($bookingData['hall_id']);
            }

            return Booking::create($bookingData);
        });
    }

    private function generateBookingNumber($hallId)
    {
        $hall = \App\Models\Hall::find($hallId);
        $hallName = $hall ? $hall->name : 'General';

        // 1. Generate Hall Code (First letters of words, or first 2 chars)
        $words = explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $hallName));
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 1));
        }
        if (strlen($code) < 2) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $hallName), 0, 2));
        }
        // Ensure at least 2 chars
        if (strlen($code) < 2) {
            $code = 'HL';
        }

        // 2. Generate Date Code (DDMMYY)
        $dateCode = now()->format('dmy');

        // 3. Find Sequence
        $prefix = $code.$dateCode.'-';
        $latestBooking = Booking::where('booking_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($latestBooking && preg_match('/-(\d+)$/', $latestBooking->booking_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function updateBooking(Booking $booking, array $bookingData): Booking
    {
        return DB::transaction(function () use ($booking, $bookingData) {
            $bookingData['updated_by'] = Auth::id();
            $bookingData['booking_price'] = $bookingData['booking_price'] ?? $bookingData['quote_price'];

            // Ensure booking number exists (for legacy records)
            if (empty($booking->booking_number)) {
                $hallId = $bookingData['hall_id'] ?? $booking->hall_id;
                $bookingData['booking_number'] = $this->generateBookingNumber($hallId);
            }

            $booking->update($bookingData);

            return $booking;
        });
    }

    public function checkAvailability(int $hallId, string $start, string $end, ?int $excludeId = null): bool
    {
        return ! Booking::where('hall_id', $hallId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($r) use ($start, $end) {
                        $r->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                    });
            })
            ->exists();
    }
}
