<?php

namespace App\Http\Requests;

use App\Models\Addon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddonRequest extends FormRequest
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
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('addons', 'name')
                    ->where(fn ($q) => $q->where('hall_id', $this->input('hall_id'))->whereNull('deleted_at'))
                    ->ignore($this->route('addon')?->id),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'pricing_mode' => ['required', Rule::in(array_keys(Addon::PRICING_MODES))],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
