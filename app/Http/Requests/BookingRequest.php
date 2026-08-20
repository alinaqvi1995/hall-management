<?php

namespace App\Http\Requests;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Hall;
use App\Models\Lawn;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware and the BookingPolicy own authorisation.
        return true;
    }

    protected function prepareForValidation(): void
    {
        // A hall admin cannot choose a hall, so the field may be absent.
        $user = $this->user();

        if ($user && ! $user->isSuperAdmin() && $user->hall_id) {
            $this->merge(['hall_id' => $user->hall_id]);
        }

        if ($this->filled('customer_cnic')) {
            $digits = preg_replace('/\D/', '', (string) $this->input('customer_cnic'));

            if (strlen($digits) === 13) {
                $this->merge([
                    'customer_cnic' => substr($digits, 0, 5).'-'.substr($digits, 5, 7).'-'.substr($digits, 12, 1),
                ]);
            }
        }
    }

    public function rules(): array
    {
        $booking = $this->route('booking');
        $isUpdate = $booking instanceof Booking;

        return [
            // ---- customer
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'customer_secondary_phone' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            // 13-digit Pakistani CNIC, normalised to 00000-0000000-0 above.
            'customer_cnic' => ['required', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'customer_address' => ['nullable', 'string', 'max:500'],

            // ---- venue
            'hall_id' => ['required', Rule::exists('halls', 'id')->whereNull('deleted_at')],
            'lawn_id' => ['required', 'integer', 'exists:lawns,id'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'event_type' => ['nullable', Rule::in(array_keys(Booking::EVENT_TYPES))],

            // ---- schedule. Past dates are allowed on edit so an in-progress
            // booking stays editable, but never on create.
            'start_datetime' => array_filter([
                'required', 'date',
                $isUpdate ? null : 'after_or_equal:'.now()->startOfDay()->toDateTimeString(),
            ]),
            'end_datetime' => ['required', 'date', 'after:start_datetime'],

            // ---- sizing
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:100000'],

            // ---- money
            // Catering is optional: a customer may rent the hall only and
            // arrange their own caterer, in which case there is no per-head
            // rate at all. `withValidator` enforces that *something* is charged.
            'per_head_rate' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'hall_rent' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quote_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],

            // ---- state
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],

            // ---- add-ons
            'addons' => ['nullable', 'array'],
            'addons.*.selected' => ['nullable'],
            'addons.*.quantity' => ['nullable', 'integer', 'min:1', 'max:10000'],

            // ---- advance collected at the counter while taking the booking.
            // Optional; when an amount is given the rest becomes required.
            'advance_amount' => ['nullable', 'numeric', 'gt:0', 'max:100000000'],
            'advance_method' => ['nullable', 'required_with:advance_amount', Rule::in(array_keys(Payment::METHODS))],
            'advance_paid_on' => ['nullable', 'required_with:advance_amount', 'date', 'before_or_equal:today'],
            'advance_reference' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Cross-field checks that need more than one value to be resolved.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $hallId = (int) $this->input('hall_id');

            // The lawn must belong to the hall being booked, otherwise a crafted
            // form could attach another hall's lawn to this booking.
            if ($this->filled('lawn_id') && $hallId) {
                $lawn = Lawn::find($this->input('lawn_id'));

                if (! $lawn || (int) $lawn->hall_id !== $hallId) {
                    $v->errors()->add('lawn_id', 'The selected lawn does not belong to this hall.');
                } elseif ($lawn->capacity && (int) $this->input('guest_count') > (int) $lawn->capacity) {
                    $v->errors()->add(
                        'guest_count',
                        'This lawn seats '.number_format($lawn->capacity).' guests. Reduce the guest count or pick a larger lawn.'
                    );
                }
            }

            // Same for the package.
            if ($this->filled('package_id') && $hallId) {
                $package = Package::find($this->input('package_id'));

                if (! $package || (int) $package->hall_id !== $hallId) {
                    $v->errors()->add('package_id', 'The selected package does not belong to this hall.');
                } elseif ($package->min_guests && (int) $this->input('guest_count') < $package->min_guests) {
                    $v->errors()->add(
                        'guest_count',
                        'This package requires at least '.number_format($package->min_guests).' guests.'
                    );
                }
            }

            // Catering may be omitted entirely, but the booking still has to
            // charge for something or the invoice would come to zero.
            $gross = $this->grossAmount();

            if ($gross <= 0) {
                $v->errors()->add(
                    'hall_rent',
                    'Enter a hall rent, a per-head rate, or at least one paid extra service — '
                    .'the booking currently totals zero.'
                );
            }

            // A discount larger than the bill would produce a negative total.
            if ((float) $this->input('discount', 0) > $gross) {
                $v->errors()->add('discount', 'Discount cannot be greater than the bill amount.');
            }

            // An advance cannot exceed the bill it is being paid against.
            if ($this->filled('advance_amount')) {
                $estimated = $this->estimatedTotal();

                if ($estimated > 0 && (float) $this->input('advance_amount') > $estimated + 0.009) {
                    $v->errors()->add(
                        'advance_amount',
                        'The advance cannot be more than the total bill of Rs. '.number_format($estimated, 2).'.'
                    );
                }
            }

            // Events longer than a week are almost always a typo in the year.
            if ($this->filled('start_datetime') && $this->filled('end_datetime')) {
                $start = strtotime((string) $this->input('start_datetime'));
                $end = strtotime((string) $this->input('end_datetime'));

                if ($start && $end && ($end - $start) > 7 * 86400) {
                    $v->errors()->add('end_datetime', 'A booking cannot span more than 7 days.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'customer name',
            'customer_phone' => 'phone number',
            'customer_cnic' => 'CNIC',
            'lawn_id' => 'lawn',
            'hall_id' => 'hall',
            'package_id' => 'package',
            'guest_count' => 'guest count',
            'per_head_rate' => 'per-head rate',
            'advance_amount' => 'advance amount',
            'advance_method' => 'payment method',
            'advance_paid_on' => 'payment date',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_cnic.regex' => 'Enter a valid CNIC, for example 35201-1234567-1.',
            'customer_phone.regex' => 'Enter a valid phone number, for example 0300-1234567.',
            'end_datetime.after' => 'The event must end after it starts.',
        ];
    }

    /* ------------------------------------------------------------- projections */

    /** @return array<string, mixed> */
    public function customerData(): array
    {
        return [
            'name' => $this->input('customer_name'),
            'phone' => $this->input('customer_phone'),
            'secondary_phone' => $this->input('customer_secondary_phone'),
            'email' => $this->input('customer_email'),
            'cnic' => $this->input('customer_cnic'),
            'address' => $this->input('customer_address'),
        ];
    }

    /**
     * Booking columns only. Money totals are recomputed by the service, so
     * whatever the form claimed about them is deliberately ignored here.
     *
     * @return array<string, mixed>
     */
    public function bookingData(): array
    {
        return [
            'lawn_id' => $this->input('lawn_id'),
            'package_id' => $this->input('package_id') ?: null,
            'event_type' => $this->input('event_type'),
            'start_datetime' => $this->input('start_datetime'),
            'end_datetime' => $this->input('end_datetime'),
            'capacity' => $this->input('capacity') ?: $this->input('guest_count'),
            'guest_count' => (int) $this->input('guest_count'),
            'per_head_rate' => (float) $this->input('per_head_rate', 0),
            'hall_rent' => (float) $this->input('hall_rent', 0),
            'discount' => (float) $this->input('discount', 0),
            'tax_percent' => (float) $this->input('tax_percent', 0),
            'quote_price' => $this->filled('quote_price') ? (float) $this->input('quote_price') : null,
            'status' => $this->input('status'),
            'notes' => $this->input('notes'),
        ];
    }

    /**
     * Selected add-ons keyed by id.
     *
     * @return array<int, array{quantity: int}>
     */
    public function addonRows(): array
    {
        $rows = [];

        foreach ((array) $this->input('addons', []) as $addonId => $row) {
            if (empty($row['selected'])) {
                continue;
            }

            $rows[(int) $addonId] = ['quantity' => max((int) ($row['quantity'] ?? 1), 1)];
        }

        return $rows;
    }

    /** Default tax rate for the hall, used when the form omits one. */
    public function hallTaxPercent(): float
    {
        return (float) (Hall::find($this->input('hall_id'))?->tax_percent ?? 0);
    }

    /**
     * The advance to record against the new booking, or null when none was
     * collected at the counter.
     *
     * @return array<string, mixed>|null
     */
    public function advanceData(): ?array
    {
        if (! $this->filled('advance_amount')) {
            return null;
        }

        return [
            'amount' => (float) $this->input('advance_amount'),
            'method' => $this->input('advance_method', 'cash'),
            'direction' => 'in',
            'reference' => $this->input('advance_reference'),
            'paid_on' => $this->input('advance_paid_on') ?: now()->toDateString(),
            'notes' => 'Advance received when the booking was created.',
        ];
    }

    /**
     * Bill total as the form implies it, mirroring BookingService::withPricing.
     * Used only to give a helpful error before the service recomputes it.
     */
    private function estimatedTotal(): float
    {
        $subtotal = max($this->grossAmount() - (float) $this->input('discount', 0), 0);
        $tax = $subtotal * (float) $this->input('tax_percent', 0) / 100;

        return round($subtotal + $tax, 2);
    }

    /**
     * Chargeable amount before discount and tax: catering + hall rent + extras.
     * Used to reject a zero-value booking and to bound the discount.
     */
    private function grossAmount(): float
    {
        $guests = (int) $this->input('guest_count', 0);

        $menu = $guests * (float) $this->input('per_head_rate', 0);
        $rent = (float) $this->input('hall_rent', 0);

        $extras = 0.0;
        $rows = $this->addonRows();

        if ($rows) {
            $catalogue = Addon::whereIn('id', array_keys($rows))->get();

            foreach ($catalogue as $addon) {
                $quantity = $rows[$addon->id]['quantity'] ?? 1;
                $multiplier = $addon->pricing_mode === 'per_head' ? max($guests, 0) : 1;
                $extras += (float) $addon->price * $quantity * $multiplier;
            }
        }

        return round($menu + $rent + $extras, 2);
    }
}
