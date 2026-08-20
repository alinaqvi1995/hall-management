@extends('dashboard.includes.partial.base')

@php
    $isEdit = $expense->exists;
    // Prefill the booking when arriving from a booking page.
    $selectedBooking = old('booking_id', $expense->booking_id ?? request('booking_id'));
@endphp

@section('title', $isEdit ? 'Edit Expense' : 'Record Expense')

@section('content')
    <x-page-header :title="$isEdit ? 'Edit Expense' : 'Record Expense'"
        subtitle="Link a cost to an event, or leave it unlinked as a hall overhead" icon="receipt_long"
        :breadcrumbs="['Expenses' => route('expenses.index'), $isEdit ? 'Edit' : 'Record Expense' => null]" />

    <form method="POST"
        action="{{ $isEdit ? route('expenses.update', $expense) : route('expenses.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">Expense Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if (auth()->user()->isSuperAdmin())
                                <div class="col-md-6">
                                    <label class="form-label">Hall <span class="required-mark">*</span></label>
                                    <select name="hall_id"
                                        class="form-select @error('hall_id') is-invalid @enderror" required>
                                        <option value="">Select hall</option>
                                        @foreach ($halls as $hall)
                                            <option value="{{ $hall->id }}" @selected(old('hall_id', $expense->hall_id) == $hall->id)>
                                                {{ $hall->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('hall_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="hall_id" value="{{ auth()->user()->hall_id }}">
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">Description <span class="required-mark">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $expense->title) }}"
                                    class="form-control @error('title') is-invalid @enderror"
                                    placeholder="e.g. Catering for 300 guests" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Amount <span class="required-mark">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                        value="{{ old('amount', $expense->amount) }}"
                                        class="form-control @error('amount') is-invalid @enderror" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date <span class="required-mark">*</span></label>
                                <input type="date" name="spent_on" max="{{ now()->toDateString() }}"
                                    value="{{ old('spent_on', $expense->spent_on?->format('Y-m-d') ?? now()->toDateString()) }}"
                                    class="form-control @error('spent_on') is-invalid @enderror" required>
                                @error('spent_on')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paid By <span class="required-mark">*</span></label>
                                <select name="method" class="form-select @error('method') is-invalid @enderror"
                                    required>
                                    @foreach (\App\Models\Expense::METHODS as $key => $label)
                                        <option value="{{ $key }}" @selected(old('method', $expense->method ?? 'cash') === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="expense_category_id"
                                    class="form-select @error('expense_category_id') is-invalid @enderror">
                                    <option value="">Uncategorised</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('expense_category_id', $expense->expense_category_id) == $category->id)>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('expense_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Vendor</label>
                                <select name="vendor_id" class="form-select select2 @error('vendor_id') is-invalid @enderror"
                                    data-placeholder="No vendor">
                                    <option value="">No vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $expense->vendor_id) == $vendor->id)>
                                            {{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Link to Booking</label>
                                <select name="booking_id"
                                    class="form-select select2 @error('booking_id') is-invalid @enderror"
                                    data-placeholder="Not linked — treat as hall overhead">
                                    <option value="">Not linked — treat as hall overhead</option>
                                    @foreach ($bookings as $booking)
                                        <option value="{{ $booking->id }}" @selected($selectedBooking == $booking->id)>
                                            {{ $booking->booking_number ?? ('#'.$booking->id) }} —
                                            {{ $booking->customer->name ?? 'Customer' }}
                                            ({{ $booking->start_datetime?->format('d M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    Linked costs appear on the booking and feed the profit-per-event report.
                                </div>
                                @error('booking_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Reference</label>
                                <input type="text" name="reference"
                                    value="{{ old('reference', $expense->reference) }}"
                                    class="form-control @error('reference') is-invalid @enderror"
                                    placeholder="Bill / cheque number">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="2"
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $expense->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button type="submit" class="btn btn-primary"
                            data-loading-text="{{ $isEdit ? 'Updating…' : 'Saving…' }}">
                            <i class="material-icons-outlined fs-6 align-middle">save</i>
                            {{ $isEdit ? 'Update Expense' : 'Save Expense' }}
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <p class="form-section-title">About expenses</p>
                        <ul class="small text-secondary ps-3 mb-0">
                            <li class="mb-2">
                                <strong>Linked to a booking</strong> — catering, decor or event staff. These are
                                subtracted from what the event collected to give its margin.
                            </li>
                            <li class="mb-0">
                                <strong>Unlinked</strong> — electricity, gas, salaries, taxes and maintenance.
                                These count against the venue as a whole.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
