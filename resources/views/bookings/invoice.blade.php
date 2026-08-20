@php
    /**
     * Invoice, rendered both to the browser and to PDF via dompdf.
     *
     * $forPdf is true when dompdf is rendering: it cannot resolve the admin
     * theme, so this template is deliberately self-contained inline CSS with no
     * external stylesheets, flexbox or CSS variables.
     */
    $hall = $booking->hall;
    $customer = $booking->customer;
    $paid = $booking->amount_paid;
    $balance = $booking->balance_due;
    $logo = $hall?->logo ? public_path($hall->logo) : null;
    $hasLogo = $logo && file_exists($logo);
@endphp
    <!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $booking->formatted_booking_number }}</title>
    <style>
        /* dompdf understands tables and floats far better than flex/grid. */
        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            background: #fff;
        }

        table { width: 100%; border-collapse: collapse; }

        .header { border-bottom: 2px solid #fc5523; padding-bottom: 12px; margin-bottom: 18px; }
        .hall-name { font-size: 20px; font-weight: bold; color: #111827; margin: 0 0 3px; }
        .hall-meta { color: #6b7280; font-size: 10px; line-height: 1.55; }
        .doc-title { font-size: 22px; font-weight: bold; color: #fc5523; margin: 0 0 4px; text-align: right; }
        .doc-meta { text-align: right; font-size: 10px; color: #6b7280; line-height: 1.6; }
        .logo { max-height: 58px; max-width: 150px; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-paid      { background: #dcfce7; color: #166534; }
        .badge-partial   { background: #fef3c7; color: #92400e; }
        .badge-pending   { background: #fee2e2; color: #991b1b; }
        .badge-refunded  { background: #e5e7eb; color: #374151; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .panel { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; }
        .panel-title {
            font-size: 9px; font-weight: bold; text-transform: uppercase;
            letter-spacing: .6px; color: #6b7280; margin: 0 0 6px;
        }
        .kv { margin: 0 0 3px; }
        .kv b { color: #111827; }

        .items th {
            background: #f9fafb; text-align: left; padding: 8px;
            font-size: 9px; text-transform: uppercase; letter-spacing: .5px;
            color: #4b5563; border-bottom: 1px solid #e5e7eb;
        }
        .items td { padding: 8px; border-bottom: 1px solid #f3f4f6; }
        .num { text-align: right; }

        .totals td { padding: 5px 8px; }
        .totals .label { color: #6b7280; text-align: right; }
        .totals .grand td {
            border-top: 2px solid #111827; font-size: 14px;
            font-weight: bold; color: #111827; padding-top: 8px;
        }
        .due td { color: #991b1b; font-weight: bold; }

        .terms { margin-top: 20px; font-size: 9px; color: #6b7280; line-height: 1.7; }
        .terms h4 { font-size: 10px; color: #374151; margin: 0 0 4px; text-transform: uppercase; letter-spacing: .5px; }

        .signatures { margin-top: 34px; }
        .sig-line { border-top: 1px solid #9ca3af; padding-top: 4px; font-size: 9px; color: #6b7280; }

        .footer {
            margin-top: 22px; padding-top: 10px; border-top: 1px solid #e5e7eb;
            text-align: center; font-size: 9px; color: #9ca3af;
        }

        .watermark {
            position: absolute; top: 40%; left: 18%;
            font-size: 68px; color: #fee2e2; font-weight: bold;
            transform: rotate(-24deg); z-index: -1;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>

<body>
    @if ($booking->isCancelled())
        <div class="watermark">CANCELLED</div>
    @endif

    {{-- Browser-only toolbar; excluded from the PDF and from printing. --}}
    @unless ($forPdf ?? false)
        <div class="no-print" style="margin-bottom:16px;text-align:right">
            <button onclick="window.print()"
                style="padding:7px 14px;border:1px solid #d1d5db;border-radius:6px;background:#fc5523;color:#fff;cursor:pointer;font-weight:600">
                Print / Save as PDF
            </button>
            <a href="{{ route('bookings.show', $booking) }}"
                style="padding:7px 14px;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#374151;margin-left:6px">
                Back to booking
            </a>
        </div>
    @endunless

    {{-- ───────────────────────────────── header ─────────────────────────── --}}
    <div class="header">
        <table>
            <tr>
                <td style="width:60%;vertical-align:top">
                    @if ($hasLogo)
                        <img src="{{ $logo }}" class="logo" alt=""><br>
                    @endif
                    <p class="hall-name">{{ $hall->name ?? 'Hall' }}</p>
                    <div class="hall-meta">
                        {{ $hall->full_address ?? '' }}<br>
                        @if ($hall?->phone)Phone: {{ $hall->phone }}@endif
                        @if ($hall?->email) &nbsp;|&nbsp; {{ $hall->email }}@endif
                        @if ($hall?->ntn_number)<br>NTN: {{ $hall->ntn_number }}@endif
                        @if ($hall?->gst_number) &nbsp;|&nbsp; GST: {{ $hall->gst_number }}@endif
                    </div>
                </td>
                <td style="width:40%;vertical-align:top">
                    <p class="doc-title">INVOICE</p>
                    <div class="doc-meta">
                        <b>{{ $booking->formatted_booking_number }}</b><br>
                        Issued: {{ now()->format('d M Y') }}<br>
                        <span class="badge badge-{{ $booking->isCancelled() ? 'cancelled' : $booking->payment_status }}">
                            {{ $booking->isCancelled() ? 'Cancelled' : $booking->payment_status_label }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─────────────────────────── customer / event ───────────────────── --}}
    <table style="margin-bottom:16px">
        <tr>
            <td style="width:49%;vertical-align:top">
                <div class="panel">
                    <p class="panel-title">Billed To</p>
                    <p class="kv"><b>{{ $customer->name ?? '—' }}</b></p>
                    @if ($customer?->formatted_cnic)
                        <p class="kv">CNIC: {{ $customer->formatted_cnic }}</p>
                    @endif
                    @if ($customer?->phone)
                        <p class="kv">Phone: {{ $customer->phone }}</p>
                    @endif
                    @if ($customer?->email)
                        <p class="kv">Email: {{ $customer->email }}</p>
                    @endif
                    @if ($customer?->address)
                        <p class="kv">{{ $customer->address }}</p>
                    @endif
                </div>
            </td>
            <td style="width:2%"></td>
            <td style="width:49%;vertical-align:top">
                <div class="panel">
                    <p class="panel-title">Event Details</p>
                    <p class="kv">Type: <b>{{ $booking->event_type_label ?? 'Event' }}</b></p>
                    <p class="kv">Venue: <b>{{ $booking->lawn->name ?? ($hall->name ?? '—') }}</b></p>
                    <p class="kv">Date: <b>{{ $booking->start_datetime?->format('d M Y') }}</b></p>
                    <p class="kv">Time:
                        {{ $booking->start_datetime?->format('h:i A') }} –
                        {{ $booking->end_datetime?->format('h:i A') }}
                    </p>
                    <p class="kv">Guests: <b>{{ number_format($booking->guest_count) }}</b></p>
                    @if ($booking->menu_amount <= 0)
                        <p class="kv" style="color:#6b7280">Catering: arranged by customer</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ────────────────────────────── line items ──────────────────────── --}}
    <table class="items" style="margin-bottom:12px">
        <thead>
            <tr>
                <th style="width:46%">Description</th>
                <th style="width:14%" class="num">Qty</th>
                <th style="width:20%" class="num">Rate</th>
                <th style="width:20%" class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            {{-- Hall rent is the first line for a venue-only booking. --}}
            @if ($booking->hall_rent > 0)
                <tr>
                    <td>
                        <b>Hall / lawn rent</b>
                        <br><span style="color:#6b7280">{{ $booking->lawn->name ?? ($hall->name ?? '') }}</span>
                    </td>
                    <td class="num">1</td>
                    <td class="num">{{ number_format($booking->hall_rent, 2) }}</td>
                    <td class="num">{{ number_format($booking->hall_rent, 2) }}</td>
                </tr>
            @endif

            {{-- Catering is optional: many customers arrange their own caterer. --}}
            @if ($booking->menu_amount > 0)
                <tr>
                    <td>
                        <b>{{ $booking->package->name ?? 'Catering / Menu' }}</b>
                        @if ($booking->package?->type)
                            <br><span style="color:#6b7280">{{ $booking->package->type_label }}</span>
                        @endif
                    </td>
                    <td class="num">{{ number_format($booking->guest_count) }}</td>
                    <td class="num">{{ number_format($booking->per_head_rate, 2) }}</td>
                    <td class="num">{{ number_format($booking->menu_amount, 2) }}</td>
                </tr>
            @endif

            @foreach ($booking->addons as $addon)
                <tr>
                    <td>
                        {{ $addon->name }}
                        @if ($addon->pricing_mode === 'per_head')
                            <span style="color:#6b7280">(per head)</span>
                        @endif
                    </td>
                    <td class="num">
                        {{ $addon->pricing_mode === 'per_head'
                            ? number_format($addon->pivot->quantity * $booking->guest_count)
                            : number_format($addon->pivot->quantity) }}
                    </td>
                    <td class="num">{{ number_format($addon->pivot->unit_price, 2) }}</td>
                    <td class="num">{{ number_format($addon->pivot->line_total, 2) }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    {{-- ──────────────────────────────── totals ────────────────────────── --}}
    <table>
        <tr>
            <td style="width:52%;vertical-align:top">
                @if ($booking->payments->isNotEmpty())
                    <div class="panel">
                        <p class="panel-title">Payments Received</p>
                        <table>
                            @foreach ($booking->payments->sortBy('paid_on') as $payment)
                                <tr>
                                    <td style="padding:2px 0">
                                        {{ $payment->paid_on?->format('d M Y') }}
                                        <span style="color:#6b7280">· {{ $payment->method_label }}</span>
                                        @if ($payment->direction === 'refund')
                                            <span style="color:#991b1b">(refund)</span>
                                        @endif
                                    </td>
                                    <td class="num" style="padding:2px 0">
                                        {{ $payment->direction === 'refund' ? '-' : '' }}{{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            </td>
            <td style="width:4%"></td>
            <td style="width:44%;vertical-align:top">
                <table class="totals">
                    @if ($booking->menu_amount > 0)
                        <tr>
                            <td class="label">Catering subtotal</td>
                            <td class="num">{{ number_format($booking->menu_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($booking->addons_amount > 0)
                        <tr>
                            <td class="label">Extra services</td>
                            <td class="num">{{ number_format($booking->addons_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($booking->hall_rent > 0)
                        <tr>
                            <td class="label">Hall rent</td>
                            <td class="num">{{ number_format($booking->hall_rent, 2) }}</td>
                        </tr>
                    @endif
                    @if ($booking->discount > 0)
                        <tr>
                            <td class="label">Discount</td>
                            <td class="num">-{{ number_format($booking->discount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($booking->tax_amount > 0)
                        <tr>
                            <td class="label">
                                Tax / GST ({{ rtrim(rtrim(number_format($booking->tax_percent, 2), '0'), '.') }}%)
                            </td>
                            <td class="num">{{ number_format($booking->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td class="label" style="color:#111827">Total (PKR)</td>
                        <td class="num">{{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Received</td>
                        <td class="num">{{ number_format($paid, 2) }}</td>
                    </tr>
                    <tr class="due">
                        <td class="label" style="color:#991b1b">Balance Due</td>
                        <td class="num">{{ number_format(max($balance, 0), 2) }}</td>
                    </tr>
                    @if ($booking->isCancelled() && $booking->cancellation_charge > 0)
                        <tr>
                            <td class="label">Cancellation charge retained</td>
                            <td class="num">{{ number_format($booking->cancellation_charge, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ──────────────────────────────── terms ─────────────────────────── --}}
    <div class="terms">
        <h4>Terms &amp; Conditions</h4>
        1. An advance of {{ $hall->advance_policy_percent ?? 25 }}% of the total is required to confirm the booking.<br>
        2. The remaining balance is payable before the event begins.<br>
        3. Cancellation attracts a charge of {{ $hall->cancellation_charge_percent ?? 0 }}% of the total bill.<br>
        4. Final guest numbers must be confirmed at least 48 hours before the event; charges are based on the
        confirmed count or the actual attendance, whichever is higher.<br>
        5. Any damage to the property will be charged separately.<br>
        6. All rates are in Pakistani Rupees (PKR).
    </div>

    @if ($booking->notes)
        <div class="terms">
            <h4>Notes</h4>
            {{ $booking->notes }}
        </div>
    @endif

    <div class="signatures">
        <table>
            <tr>
                <td style="width:45%"><div class="sig-line">Customer Signature</div></td>
                <td style="width:10%"></td>
                <td style="width:45%"><div class="sig-line">For {{ $hall->name ?? 'the Hall' }}</div></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated invoice for {{ $hall->name ?? '' }} ·
        Generated {{ now()->format('d M Y, h:i A') }}
    </div>
</body>

</html>
