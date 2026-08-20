<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:1000'],
            // Left blank, the hall's cancellation policy percentage is applied.
            'cancellation_charge' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cancellation_reason' => 'reason',
            'cancellation_charge' => 'cancellation charge',
        ];
    }
}
