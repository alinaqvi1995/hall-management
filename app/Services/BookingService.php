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
            $bookingData['created_by']    = Auth::id();
            $bookingData['updated_by']    = Auth::id();
            $bookingData['booking_price'] = $bookingData['booking_price'] ?? $bookingData['quote_price'];
            return Booking::create($bookingData);
        });
    }

    public function updateBooking(Booking $booking, array $bookingData): Booking
    {
        return DB::transaction(function () use ($booking, $bookingData) {
            $bookingData['updated_by']    = Auth::id();
            $bookingData['booking_price'] = $bookingData['booking_price'] ?? $bookingData['quote_price'];
            $booking->update($bookingData);
            return $booking;
        });
    }

    public function checkAvailability(int $hallId, string $start, string $end, ?int $excludeId = null): bool
    {
        return ! Booking::where('hall_id', $hallId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
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
