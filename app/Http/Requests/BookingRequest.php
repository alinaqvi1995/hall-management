<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_email'   => 'nullable|email|max:255',
            'customer_cnic'    => 'required|string|max:20',
            'customer_address' => 'nullable|string|max:500',

            'hall_id'          => 'required|exists:halls,id',
            'start_datetime'   => 'required|date|after_or_equal:today',
            'end_datetime'     => 'required|date|after:start_datetime',
            'capacity'         => 'required|integer|min:1',
            'quote_price'      => 'required|numeric|min:0',
            'booking_price'    => 'nullable|numeric|min:0',
            'advance_paid'     => 'nullable|numeric|min:0',
            'payment_status'   => 'required|in:pending,partial,paid',
            'status'           => 'required|in:pending,confirmed,cancelled',
            'notes'            => 'nullable|string|max:2000',
            'facilities'       => 'nullable|array',
            'facilities.*'     => 'string|max:100',
        ];
    }
}
