<?php

namespace App\Http\Requests;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user && ! $user->isSuperAdmin() && $user->hall_id) {
            $this->merge(['hall_id' => $user->hall_id]);
        }

        if ($this->filled('cnic')) {
            $digits = preg_replace('/\D/', '', (string) $this->input('cnic'));

            if (strlen($digits) === 13) {
                $this->merge([
                    'cnic' => substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12, 1),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'cnic' => ['nullable', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'employment_type' => ['required', Rule::in(array_keys(Staff::EMPLOYMENT_TYPES))],
            'joined_on' => ['nullable', 'date', 'before_or_equal:today'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnic.regex' => 'Enter a valid CNIC, for example 35201-1234567-1.',
        ];
    }
}
