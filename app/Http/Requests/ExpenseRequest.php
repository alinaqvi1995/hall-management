<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
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
    }

    public function rules(): array
    {
        return [
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
            // Optional: an unlinked expense is a hall overhead rather than an
            // event cost.
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000000'],
            'method' => ['required', Rule::in(array_keys(Expense::METHODS))],
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'spent_on' => 'expense date',
            'expense_category_id' => 'category',
            'vendor_id' => 'vendor',
            'booking_id' => 'booking',
        ];
    }
}
