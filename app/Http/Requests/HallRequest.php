<?php

namespace App\Http\Requests;

use App\Models\Hall;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class HallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hallId = $this->route('hall')?->id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('halls', 'name')->whereNull('deleted_at')->ignore($hallId),
            ],
            // The logo was previously moved into place without any validation,
            // which allowed an arbitrary file into a web-served directory.
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'country' => ['nullable', 'string', 'max:255'],
            'zipcode' => ['nullable', 'string', 'max:20'],
            'area' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'hall_capacity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'hall_types' => ['nullable', Rule::in(array_keys(Hall::HALL_TYPES))],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'ntn_number' => ['nullable', 'string', 'max:30'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'established_at' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:0,1'],
            'notes' => ['nullable', 'string', 'max:5000'],

            // Commercial defaults
            'default_per_head_rate' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'advance_policy_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'cancellation_charge_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // Lawns / halls within the venue
            'lawns' => ['nullable', 'array'],
            'lawns.*.id' => ['nullable', 'integer', 'exists:lawns,id'],
            'lawns.*.name' => ['required', 'string', 'max:255'],
            'lawns.*.capacity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Two lawns with the same name inside one venue are indistinguishable
            // in every dropdown, so reject the duplicate up front.
            $names = collect($this->input('lawns', []))
                ->pluck('name')
                ->filter()
                ->map(fn ($n) => mb_strtolower(trim($n)));

            if ($names->count() !== $names->unique()->count()) {
                $v->errors()->add('lawns', 'Each lawn must have a distinct name.');
            }

            // The chosen city must sit in the chosen province.
            if ($this->filled('city_id') && $this->filled('state_id')) {
                $belongs = \App\Models\City::where('id', $this->input('city_id'))
                    ->where('state_id', $this->input('state_id'))
                    ->exists();

                if (! $belongs) {
                    $v->errors()->add('city_id', 'The selected city is not in the selected province.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'hall_capacity' => 'capacity',
            'hall_types' => 'venue type',
            'state_id' => 'province',
            'city_id' => 'city',
            'ntn_number' => 'NTN',
            'gst_number' => 'GST number',
            'default_per_head_rate' => 'default per-head rate',
            'advance_policy_percent' => 'advance policy',
            'cancellation_charge_percent' => 'cancellation charge',
            'tax_percent' => 'tax rate',
        ];
    }

    /** Hall columns only — lawns are synced separately. */
    public function hallData(): array
    {
        return collect($this->validated())
            ->except(['lawns', 'logo'])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function lawnRows(): array
    {
        return $this->validated()['lawns'] ?? [];
    }
}
