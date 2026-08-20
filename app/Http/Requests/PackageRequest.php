<?php

namespace App\Http\Requests;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
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
        $packageId = $this->route('package')?->id;

        return [
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
            'name' => [
                'required', 'string', 'max:255',
                // Package names only need to be unique inside their own hall.
                Rule::unique('packages', 'name')
                    ->where(fn ($q) => $q->where('hall_id', $this->input('hall_id'))->whereNull('deleted_at'))
                    ->ignore($packageId),
            ],
            'type' => ['required', Rule::in(array_keys(Package::TYPES))],
            'per_head_rate' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'min_guests' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'per_head_rate' => 'per-head rate',
            'min_guests' => 'minimum guests',
            'hall_id' => 'hall',
        ];
    }
}
