@php
    /**
     * Shared booking form. Used by both create and edit.
     *
     * $booking  existing model on edit, a blank model on create
     * $halls    halls the user may pick from
     * $hall     resolved hall when there is only one choice
     * $isEdit   true on the edit screen
     */
    $user = auth()->user();
    $lockedHall = ! $user->isSuperAdmin();
    $selectedHallId = old('hall_id', $booking->hall_id ?? ($lockedHall ? $user->hall_id : ($halls->count() === 1 ? $halls->first()->id : null)));

    $customer = $booking->customer ?? null;

    // Add-on rows already attached, keyed by id, so quantities survive a
    // validation failure.
    $existingAddons = $booking->relationLoaded('addons')
        ? $booking->addons->mapWithKeys(fn($a) => [$a->id => $a->pivot->quantity])->all()
        : [];
    $oldAddons = old('addons', []);

    $fmt = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('Y-m-d\TH:i') : null;
@endphp

@csrf
@if ($isEdit)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-xl-8">
        {{-- ───────────────────────────── Customer ───────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">person</i> Customer Details
            </div>
            <div class="card-body">
                <div id="customerAlert"></div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">CNIC <span class="required-mark">*</span></label>
                        <input type="text" name="customer_cnic" id="customerCnic"
                            value="{{ old('customer_cnic', $customer->cnic ?? '') }}"
                            class="form-control @error('customer_cnic') is-invalid @enderror"
                            placeholder="35201-1234567-1" inputmode="numeric" required>
                        <div class="form-text">Existing customers are filled in automatically.</div>
                        @error('customer_cnic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Full Name <span class="required-mark">*</span></label>
                        <input type="text" name="customer_name" id="customerName"
                            value="{{ old('customer_name', $customer->name ?? '') }}"
                            class="form-control @error('customer_name') is-invalid @enderror" required>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Mobile <span class="required-mark">*</span></label>
                        <input type="text" name="customer_phone" id="customerPhone"
                            value="{{ old('customer_phone', $customer->phone ?? '') }}"
                            class="form-control @error('customer_phone') is-invalid @enderror"
                            placeholder="0300-1234567" required>
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Alternate Mobile</label>
                        <input type="text" name="customer_secondary_phone" id="customerSecondaryPhone"
                            value="{{ old('customer_secondary_phone', $customer->secondary_phone ?? '') }}"
                            class="form-control @error('customer_secondary_phone') is-invalid @enderror">
                        @error('customer_secondary_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" id="customerEmail"
                            value="{{ old('customer_email', $customer->email ?? '') }}"
                            class="form-control @error('customer_email') is-invalid @enderror">
                        @error('customer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="customer_address" id="customerAddress" rows="2"
                            class="form-control @error('customer_address') is-invalid @enderror">{{ old('customer_address', $customer->address ?? '') }}</textarea>
                        @error('customer_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ────────────────────────── Event & venue ─────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">celebration</i> Event &amp; Venue
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Hall <span class="required-mark">*</span></label>
                        @if ($lockedHall)
                            <input type="text" class="form-control"
                                value="{{ $user->hall->name ?? 'No venue linked' }}" disabled>
                            <input type="hidden" name="hall_id" id="hallSelect" value="{{ $user->hall_id }}">
                        @else
                            <select name="hall_id" id="hallSelect"
                                class="form-select @error('hall_id') is-invalid @enderror" required>
                                <option value="">Select hall</option>
                                @foreach ($halls as $h)
                                    <option value="{{ $h->id }}" @selected($selectedHallId == $h->id)>{{ $h->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('hall_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select @error('event_type') is-invalid @enderror">
                            <option value="">Select event type</option>
                            @foreach (\App\Models\Booking::EVENT_TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('event_type', $booking->event_type ?? '') === $key)>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('event_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Starts <span class="required-mark">*</span></label>
                        <input type="datetime-local" name="start_datetime" id="startDatetime"
                            value="{{ old('start_datetime', $fmt($booking->start_datetime ?? null)) }}"
                            class="form-control @error('start_datetime') is-invalid @enderror" required>
                        @error('start_datetime')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ends <span class="required-mark">*</span></label>
                        <input type="datetime-local" name="end_datetime" id="endDatetime"
                            value="{{ old('end_datetime', $fmt($booking->end_datetime ?? null)) }}"
                            class="form-control @error('end_datetime') is-invalid @enderror" required>
                        @error('end_datetime')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Lawn / Hall Space <span class="required-mark">*</span></label>
                        <select name="lawn_id" id="lawnSelect"
                            class="form-select @error('lawn_id') is-invalid @enderror" required
                            data-selected="{{ old('lawn_id', $booking->lawn_id ?? '') }}">
                            <option value="">Pick the dates first</option>
                        </select>
                        <div class="form-text" id="lawnHint">Availability is checked against the chosen dates.</div>
                        @error('lawn_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Expected Guests <span class="required-mark">*</span></label>
                        <input type="number" name="guest_count" id="guestCount" min="1"
                            value="{{ old('guest_count', $booking->guest_count ?? '') }}"
                            class="form-control @error('guest_count') is-invalid @enderror" required>
                        @error('guest_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ────────────────────────────── Charges ───────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">calculate</i> Charges
            </div>
            <div class="card-body">

                {{-- Hall rent stands on its own: a customer may rent the venue
                     only and bring their own caterer. --}}
                <div class="form-section">
                    <p class="form-section-title">Venue</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hall / Lawn Rent</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" min="0" name="hall_rent" id="hallRent"
                                    value="{{ old('hall_rent', (float) $booking->hall_rent ?: null) }}"
                                    class="form-control @error('hall_rent') is-invalid @enderror" placeholder="0">
                            </div>
                            @error('hall_rent')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" min="0" name="discount" id="discount"
                                    value="{{ old('discount', (float) $booking->discount ?: null) }}"
                                    class="form-control @error('discount') is-invalid @enderror" placeholder="0">
                            </div>
                            @error('discount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tax / GST</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="tax_percent"
                                    id="taxPercent"
                                    value="{{ old('tax_percent', (float) $booking->tax_percent ?: (float) ($hall->tax_percent ?? 0)) }}"
                                    class="form-control @error('tax_percent') is-invalid @enderror">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('tax_percent')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Optional: plenty of customers book the hall only and arrange
                     catering through their own vendor. --}}
                <div class="form-section">
                    <p class="form-section-title">
                        Catering <span class="text-secondary fw-normal">(optional)</span>
                    </p>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="noCatering" name="no_catering"
                            value="1" @checked(old('no_catering', $isEdit ? !((float) $booking->per_head_rate) : false))>
                        <label class="form-check-label" for="noCatering">
                            Customer is arranging their own catering &mdash; charge hall rent only
                        </label>
                    </div>

                    <div class="row g-3" id="cateringFields">
                        <div class="col-md-6">
                            <label class="form-label">Package / Menu</label>
                            <select name="package_id" id="packageSelect"
                                class="form-select @error('package_id') is-invalid @enderror"
                                data-selected="{{ old('package_id', $booking->package_id ?? '') }}">
                                <option value="">No package (custom rate)</option>
                            </select>
                            <div class="form-text">Choosing a package fills in its per-head rate.</div>
                            @error('package_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Per-Head Rate</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" min="0" name="per_head_rate" id="perHeadRate"
                                    value="{{ old('per_head_rate', (float) $booking->per_head_rate ?: null) }}"
                                    class="form-control @error('per_head_rate') is-invalid @enderror"
                                    placeholder="0">
                                <span class="input-group-text">/ head</span>
                            </div>
                            <div class="form-text">Leave blank if the hall is not providing food.</div>
                            @error('per_head_rate')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <p class="form-section-title">Status</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Booking Status <span class="required-mark">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror"
                                required>
                                @foreach (\App\Models\Booking::STATUSES as $key => $label)
                                    {{-- Cancelling happens from the booking page so a reason is captured. --}}
                                    @continue($key === 'cancelled')
                                    <option value="{{ $key }}" @selected(old('status', $booking->status ?? 'pending') === $key)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────── Add-ons ────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">add_circle</i> Extra Services
            </div>
            <div class="card-body">
                <div id="addonList" class="row g-2"
                    data-existing='@json($oldAddons ?: collect($existingAddons)->mapWithKeys(fn($q, $id) => [$id => ['selected' => 1, 'quantity' => $q]]))'>
                    <p class="text-secondary small mb-0">Select a hall to load its services.</p>
                </div>
            </div>
        </div>

        {{-- ───────────────────────── Advance payment ────────────────────── --}}
        @unless ($isEdit)
            {{-- Optional, and only on create: once a booking exists, payments
                 are added to its ledger from the booking page instead. --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="material-icons-outlined fs-6">payments</i>
                    Advance Payment <span class="text-secondary fw-normal">(optional)</span>
                </div>
                <div class="card-body">
                    <p class="text-secondary small">
                        Recording the advance here creates the first receipt with the booking.
                        Leave it blank if nothing has been collected yet.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Amount Received</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="0.01" min="0" name="advance_amount"
                                    id="advanceAmount" value="{{ old('advance_amount') }}"
                                    class="form-control @error('advance_amount') is-invalid @enderror"
                                    placeholder="0">
                            </div>
                            <button type="button" class="btn btn-link btn-sm p-0 mt-1 d-none"
                                id="useExpectedAdvance">
                                Use expected advance (<span id="expectedAdvanceLabel">Rs. 0</span>)
                            </button>
                            @error('advance_amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Method</label>
                            <select name="advance_method"
                                class="form-select @error('advance_method') is-invalid @enderror">
                                @foreach (\App\Models\Payment::METHODS as $key => $label)
                                    <option value="{{ $key }}" @selected(old('advance_method', 'cash') === $key)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            @error('advance_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Date</label>
                            <input type="date" name="advance_paid_on" max="{{ now()->toDateString() }}"
                                value="{{ old('advance_paid_on', now()->toDateString()) }}"
                                class="form-control @error('advance_paid_on') is-invalid @enderror">
                            @error('advance_paid_on')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Reference</label>
                            <input type="text" name="advance_reference"
                                value="{{ old('advance_reference') }}"
                                class="form-control @error('advance_reference') is-invalid @enderror"
                                placeholder="Cheque / txn no.">
                            @error('advance_reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endunless

        {{-- ────────────────────────────── Notes ─────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">sticky_note_2</i> Notes
            </div>
            <div class="card-body">
                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                    placeholder="Stage position, special requests, entry timing…">{{ old('notes', $booking->notes ?? '') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- ─────────────────────────── Live bill sidebar ────────────────────── --}}
    <div class="col-xl-4">
        <div class="card position-xl-sticky" style="top:1rem">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="material-icons-outlined fs-6">calculate</i> Bill Summary
            </div>
            <div class="card-body">
                {{-- Rows appear only when they carry a charge, so a hall-only
                     booking shows just rent and the total. --}}
                <div class="totals-row d-none" id="rowMenu">
                    <span class="totals-row__label">Catering <small id="menuBasis"
                            class="text-secondary"></small></span>
                    <span id="sumMenu">Rs. 0</span>
                </div>
                <div class="totals-row d-none" id="rowAddons">
                    <span class="totals-row__label">Extra services</span>
                    <span id="sumAddons">Rs. 0</span>
                </div>
                <div class="totals-row d-none" id="rowRent">
                    <span class="totals-row__label">Hall / lawn rent</span>
                    <span id="sumRent">Rs. 0</span>
                </div>
                <div class="totals-row d-none" id="rowDiscount">
                    <span class="totals-row__label">Discount</span>
                    <span id="sumDiscount" class="text-danger">Rs. 0</span>
                </div>
                <div class="totals-row d-none" id="rowTax">
                    <span class="totals-row__label">Tax <small id="taxBasis" class="text-secondary"></small></span>
                    <span id="sumTax">Rs. 0</span>
                </div>
                <div class="totals-row totals-row--grand">
                    <span class="totals-row__label">Total</span>
                    <span id="sumTotal">Rs. 0</span>
                </div>

                <p class="small text-secondary mt-2 mb-0 d-none" id="noCateringNote">
                    <i class="material-icons-outlined fs-6 align-middle">restaurant</i>
                    Venue only &mdash; the customer arranges their own catering.
                </p>

                @unless ($isEdit)
                    <div class="totals-row d-none" id="rowAdvance">
                        <span class="totals-row__label">Advance received</span>
                        <span id="sumAdvance" class="text-success">Rs. 0</span>
                    </div>
                    <div class="totals-row d-none" id="rowBalance">
                        <span class="totals-row__label">Balance due</span>
                        <span id="sumBalance" class="text-danger">Rs. 0</span>
                    </div>
                @endunless

                <div class="alert alert-info app-alert mt-3 mb-0 py-2 small d-flex gap-2" id="advanceHint">
                    <i class="material-icons-outlined fs-6">info</i>
                    <span></span>
                </div>

                @if ($isEdit)
                    <div class="mt-3 pt-3 border-top small">
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">Already received</span>
                            <strong><x-money :amount="$booking->amount_paid" /></strong>
                        </div>
                        <p class="text-secondary mb-0 mt-1">
                            Recorded payments are not changed by editing this booking.
                        </p>
                    </div>
                @endif
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"
                    data-loading-text="{{ $isEdit ? 'Updating…' : 'Saving…' }}">
                    <i class="material-icons-outlined fs-6 align-middle">save</i>
                    {{ $isEdit ? 'Update Booking' : 'Save Booking' }}
                </button>
                <a href="{{ $isEdit ? route('bookings.show', $booking) : route('bookings.index') }}"
                    class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            var hallSelect = document.getElementById('hallSelect');
            var lawnSelect = document.getElementById('lawnSelect');
            var packageSelect = document.getElementById('packageSelect');
            var addonList = document.getElementById('addonList');
            var noCatering = document.getElementById('noCatering');
            var advanceInput = document.getElementById('advanceAmount');
            var cateringFields = document.getElementById('cateringFields');
            var startInput = document.getElementById('startDatetime');
            var endInput = document.getElementById('endDatetime');
            var guestInput = document.getElementById('guestCount');
            var rateInput = document.getElementById('perHeadRate');
            var rentInput = document.getElementById('hallRent');
            var discountInput = document.getElementById('discount');
            var taxInput = document.getElementById('taxPercent');
            var lawnHint = document.getElementById('lawnHint');

            var ignoreBooking = {{ $isEdit ? $booking->id : 'null' }};
            var advancePercent = {{ (int) ($hall->advance_policy_percent ?? 25) }};
            var addonCatalogue = [];
            var preselectedAddons = {};

            // Local formatter so the totals still render if the shared helper
            // in app.js has not loaded for any reason.
            var money = window.hmMoney || function (value) {
                return 'Rs. ' + (Number(value) || 0).toLocaleString('en-PK', { maximumFractionDigits: 0 });
            };

            try {
                preselectedAddons = JSON.parse(addonList.dataset.existing || '{}') || {};
            } catch (e) {
                preselectedAddons = {};
            }

            /* ------------------------------------------------ live bill maths */

            function computeAddons(guests) {
                var total = 0;
                addonCatalogue.forEach(function (addon) {
                    var checkbox = document.getElementById('addon_' + addon.id);
                    if (!checkbox || !checkbox.checked) return;

                    var qtyField = document.getElementById('addon_qty_' + addon.id);
                    var qty = Math.max(parseInt(qtyField && qtyField.value, 10) || 1, 1);
                    var multiplier = addon.pricing_mode === 'per_head' ? guests : 1;
                    total += Number(addon.price) * qty * multiplier;
                });
                return total;
            }

            function recalc() {
                var guests = Math.max(parseInt(guestInput.value, 10) || 0, 0);
                var rate = Number(rateInput.value) || 0;
                var rent = Number(rentInput.value) || 0;
                var discount = Number(discountInput.value) || 0;
                var taxPercent = Number(taxInput.value) || 0;

                var menu = guests * rate;
                var addons = computeAddons(guests);
                var subtotal = Math.max(menu + addons + rent - discount, 0);
                var tax = subtotal * taxPercent / 100;
                var total = subtotal + tax;

                document.getElementById('sumMenu').textContent = money(menu);
                document.getElementById('sumAddons').textContent = money(addons);
                document.getElementById('sumRent').textContent = money(rent);
                document.getElementById('sumDiscount').textContent = '-' + money(discount);
                document.getElementById('sumTax').textContent = money(tax);
                document.getElementById('sumTotal').textContent = money(total);

                document.getElementById('menuBasis').textContent =
                    guests && rate ? '(' + guests + ' × ' + money(rate) + ')' : '';
                document.getElementById('taxBasis').textContent = taxPercent ? '(' + taxPercent + '%)' : '';

                // A hall-rent-only booking has no menu line, so hide the row
                // rather than showing a meaningless "Rs. 0".
                toggleRow('rowMenu', menu > 0);
                toggleRow('rowAddons', addons > 0);
                toggleRow('rowRent', rent > 0);
                toggleRow('rowDiscount', discount > 0);
                toggleRow('rowTax', tax > 0);

                var note = document.getElementById('noCateringNote');
                if (note) {
                    note.classList.toggle('d-none', menu > 0);
                }

                var expected = total * advancePercent / 100;

                var hint = document.querySelector('#advanceHint span');
                hint.textContent = total > 0
                    ? 'Advance expected at ' + advancePercent + '%: ' + money(expected)
                    : 'Enter a hall rent or a per-head rate to see the bill.';

                // Show what will still be owed once the advance is taken.
                if (advanceInput) {
                    var advance = Number(advanceInput.value) || 0;

                    document.getElementById('sumAdvance').textContent = money(advance);
                    document.getElementById('sumBalance').textContent = money(Math.max(total - advance, 0));

                    toggleRow('rowAdvance', advance > 0);
                    toggleRow('rowBalance', advance > 0);

                    var shortcut = document.getElementById('useExpectedAdvance');
                    if (shortcut) {
                        shortcut.classList.toggle('d-none', !(expected > 0));
                        document.getElementById('expectedAdvanceLabel').textContent = money(expected);
                        shortcut.dataset.amount = Math.round(expected);
                    }
                }
            }

            function toggleRow(id, show) {
                var el = document.getElementById(id);
                if (el) {
                    el.classList.toggle('d-none', !show);
                }
            }

            /**
             * "Own catering" clears and disables the package and per-head rate,
             * so a hall-rent-only booking cannot carry a stale food charge.
             */
            function applyCateringMode() {
                var off = noCatering && noCatering.checked;

                cateringFields.classList.toggle('opacity-50', off);

                [packageSelect, rateInput].forEach(function (field) {
                    field.disabled = off;
                    if (off) {
                        field.value = '';
                    }
                });

                if (off) {
                    packageSelect.dataset.selected = '';
                }

                recalc();
            }

            /* -------------------------------------------------- lawn loading */

            function loadLawns() {
                var hallId = hallSelect && hallSelect.value;
                var wanted = lawnSelect.dataset.selected;

                if (!hallId) {
                    lawnSelect.innerHTML = '<option value="">Select a hall first</option>';
                    return;
                }

                if (!startInput.value || !endInput.value) {
                    lawnSelect.innerHTML = '<option value="">Pick the event dates first</option>';
                    lawnHint.textContent = 'Availability is checked once both dates are set.';
                    return;
                }

                var url = '/halls/' + hallId + '/lawns?start=' + encodeURIComponent(startInput.value)
                    + '&end=' + encodeURIComponent(endInput.value)
                    + (ignoreBooking ? '&ignore=' + ignoreBooking : '');

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (lawns) {
                        if (!lawns.length) {
                            lawnSelect.innerHTML = '<option value="">This hall has no lawns configured</option>';
                            lawnHint.textContent = 'Add lawns to the hall before booking it.';
                            return;
                        }

                        var free = 0;
                        var html = '<option value="">Select lawn</option>';

                        lawns.forEach(function (lawn) {
                            var label = lawn.name + (lawn.capacity ? ' — seats ' + lawn.capacity : '');
                            if (!lawn.available) {
                                label += ' · BOOKED (' + lawn.booked_from + ' → ' + lawn.booked_to + ')';
                            } else {
                                free++;
                            }
                            html += '<option value="' + lawn.id + '"'
                                + (String(wanted) === String(lawn.id) ? ' selected' : '')
                                + (lawn.available ? '' : ' disabled') + '>' + label + '</option>';
                        });

                        lawnSelect.innerHTML = html;
                        lawnHint.textContent = free + ' of ' + lawns.length + ' space(s) free for this slot.';
                        lawnHint.className = free ? 'form-text' : 'form-text text-danger';
                    })
                    .catch(function () {
                        lawnSelect.innerHTML = '<option value="">Could not load lawns</option>';
                    });
            }

            /* ----------------------------------------------- package loading */

            function loadPackages() {
                var hallId = hallSelect && hallSelect.value;
                var wanted = packageSelect.dataset.selected;

                if (!hallId) {
                    packageSelect.innerHTML = '<option value="">Select a hall first</option>';
                    return;
                }

                fetch('{{ route('packages.forHall') }}?hall_id=' + hallId, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (packages) {
                        var html = '<option value="">No package (custom rate)</option>';
                        packages.forEach(function (p) {
                            html += '<option value="' + p.id + '" data-rate="' + p.per_head_rate
                                + '" data-min="' + (p.min_guests || 0) + '"'
                                + (String(wanted) === String(p.id) ? ' selected' : '') + '>'
                                + p.name + ' — ' + money(p.per_head_rate) + '/head'
                                + (p.min_guests ? ' (min ' + p.min_guests + ')' : '')
                                + '</option>';
                        });
                        packageSelect.innerHTML = html;
                    })
                    .catch(function () {
                        packageSelect.innerHTML = '<option value="">Could not load packages</option>';
                    });
            }

            /* ------------------------------------------------ addon loading */

            function loadAddons() {
                var hallId = hallSelect && hallSelect.value;

                if (!hallId) {
                    addonList.innerHTML = '<p class="text-secondary small mb-0">Select a hall to load its services.</p>';
                    return;
                }

                fetch('{{ route('addons.forHall') }}?hall_id=' + hallId, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (addons) {
                        addonCatalogue = addons;

                        if (!addons.length) {
                            addonList.innerHTML =
                                '<p class="text-secondary small mb-0">No extra services set up for this hall yet.</p>';
                            return;
                        }

                        var html = '';
                        addons.forEach(function (addon) {
                            var pre = preselectedAddons[addon.id] || preselectedAddons[String(addon.id)];
                            var checked = pre ? ' checked' : '';
                            var qty = pre && pre.quantity ? pre.quantity : 1;
                            var unit = addon.pricing_mode === 'per_head' ? '/head' : ' fixed';

                            html += '<div class="col-md-6">'
                                + '<div class="d-flex align-items-center gap-2 border rounded p-2 h-100">'
                                + '<div class="form-check mb-0">'
                                + '<input class="form-check-input addon-toggle" type="checkbox" id="addon_' + addon.id
                                + '" name="addons[' + addon.id + '][selected]" value="1"' + checked + '>'
                                + '</div>'
                                + '<label class="flex-grow-1 min-w-0 mb-0 cursor-pointer" for="addon_' + addon.id + '">'
                                + '<span class="d-block text-truncate">' + addon.name + '</span>'
                                + '<small class="text-secondary">' + money(addon.price) + unit + '</small>'
                                + '</label>'
                                + '<input type="number" min="1" class="form-control form-control-sm addon-qty" '
                                + 'style="width:70px" id="addon_qty_' + addon.id + '" '
                                + 'name="addons[' + addon.id + '][quantity]" value="' + qty + '">'
                                + '</div></div>';
                        });

                        addonList.innerHTML = html;
                        recalc();
                    })
                    .catch(function () {
                        addonList.innerHTML = '<p class="text-danger small mb-0">Could not load services.</p>';
                    });
            }

            /* ------------------------------------------------------ wiring */

            // Keep the selected lawn/package across reloads of their dropdowns.
            function rememberSelection(el) {
                el.addEventListener('change', function () { el.dataset.selected = el.value; });
            }
            rememberSelection(lawnSelect);
            rememberSelection(packageSelect);

            if (hallSelect) {
                hallSelect.addEventListener('change', function () {
                    loadLawns();
                    loadPackages();
                    loadAddons();
                });
            }

            [startInput, endInput].forEach(function (input) {
                input.addEventListener('change', function () {
                    // Keep the end date at or after the start date.
                    if (startInput.value) {
                        endInput.min = startInput.value;
                        if (endInput.value && endInput.value < startInput.value) {
                            endInput.value = startInput.value;
                        }
                    }
                    loadLawns();
                });
            });

            packageSelect.addEventListener('change', function () {
                var opt = this.options[this.selectedIndex];
                var rate = opt && opt.dataset.rate;
                if (rate) {
                    rateInput.value = rate;
                }
                recalc();
            });

            [guestInput, rateInput, rentInput, discountInput, taxInput].forEach(function (input) {
                input.addEventListener('input', recalc);
            });

            addonList.addEventListener('change', recalc);
            addonList.addEventListener('input', recalc);

            if (noCatering) {
                noCatering.addEventListener('change', applyCateringMode);
            }

            if (advanceInput) {
                advanceInput.addEventListener('input', recalc);

                document.getElementById('useExpectedAdvance').addEventListener('click', function () {
                    advanceInput.value = this.dataset.amount || '';
                    recalc();
                });
            }

            /* ------------------------------------------- customer CNIC lookup */

            var cnicInput = document.getElementById('customerCnic');
            var lookupTimer = null;

            function formatCnic(value) {
                var d = (value || '').replace(/\D/g, '').slice(0, 13);
                if (d.length > 12) return d.slice(0, 5) + '-' + d.slice(5, 12) + '-' + d.slice(12);
                if (d.length > 5) return d.slice(0, 5) + '-' + d.slice(5);
                return d;
            }

            cnicInput.addEventListener('input', function () {
                var caretAtEnd = this.selectionStart === this.value.length;
                this.value = formatCnic(this.value);
                if (caretAtEnd) {
                    this.selectionStart = this.selectionEnd = this.value.length;
                }

                clearTimeout(lookupTimer);
                var cnic = this.value;

                if (cnic.length < 15) {
                    return;
                }

                lookupTimer = setTimeout(function () {
                    fetch('{{ route('customers.lookup') }}?cnic=' + encodeURIComponent(cnic),
                        { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (c) {
                            var alertBox = document.getElementById('customerAlert');

                            if (!c) {
                                alertBox.innerHTML = '';
                                return;
                            }

                            // Only fill blanks, so typed corrections are not overwritten.
                            var map = {
                                customerName: c.name, customerPhone: c.phone,
                                customerSecondaryPhone: c.secondary_phone,
                                customerEmail: c.email, customerAddress: c.address
                            };
                            Object.keys(map).forEach(function (id) {
                                var el = document.getElementById(id);
                                if (el && !el.value && map[id]) el.value = map[id];
                            });

                            if (c.is_blacklisted) {
                                alertBox.innerHTML = '<div class="alert alert-danger app-alert py-2 small d-flex gap-2">'
                                    + '<i class="material-icons-outlined fs-6">block</i><div><strong>This customer is '
                                    + 'blacklisted.</strong> ' + (c.blacklist_reason || '') + '</div></div>';
                            } else {
                                alertBox.innerHTML = '<div class="alert alert-info app-alert py-2 small d-flex gap-2">'
                                    + '<i class="material-icons-outlined fs-6">history</i><div>Returning customer — '
                                    + c.total_bookings + ' previous booking(s). Details filled in.</div></div>';
                            }
                        })
                        .catch(function () {});
                }, 350);
            });

            /* --------------------------------------------------------- boot */

            if (startInput.value) {
                endInput.min = startInput.value;
            }

            loadLawns();
            loadPackages();
            loadAddons();
            applyCateringMode();
        })();
    </script>
@endpush
