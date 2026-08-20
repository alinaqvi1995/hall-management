@php
    /**
     * Printable payment receipt — a standalone page, not wrapped in the admin
     * shell, so it prints cleanly on A5/A4 without the sidebar.
     */
    $booking = $payment->booking;
    $hall = $booking?->hall;
    $customer = $booking?->customer;
    $isRefund = $payment->direction === 'refund';
@endphp
    <!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Noto Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 28px;
            background: #f3f4f6;
        }

        .sheet {
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 26px 30px;
        }

        table { width: 100%; border-collapse: collapse; }

        .head { border-bottom: 2px solid {{ $isRefund ? '#dc2626' : '#16a34a' }}; padding-bottom: 12px; }
        .hall { font-size: 18px; font-weight: 700; margin: 0 0 2px; }
        .meta { color: #6b7280; font-size: 10px; line-height: 1.6; }
        .title {
            font-size: 17px; font-weight: 700; text-align: right; margin: 0 0 3px;
            color: {{ $isRefund ? '#dc2626' : '#16a34a' }};
            text-transform: uppercase; letter-spacing: .5px;
        }
        .rmeta { text-align: right; font-size: 10px; color: #6b7280; line-height: 1.7; }

        .amount-box {
            margin: 20px 0;
            background: {{ $isRefund ? '#fef2f2' : '#f0fdf4' }};
            border: 1px solid {{ $isRefund ? '#fecaca' : '#bbf7d0' }};
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .amount-label {
            font-size: 9px; text-transform: uppercase; letter-spacing: .8px;
            color: #6b7280; margin: 0 0 4px;
        }
        .amount {
            font-size: 27px; font-weight: 700; margin: 0;
            color: {{ $isRefund ? '#991b1b' : '#166534' }};
        }
        .words { font-size: 10px; color: #6b7280; margin: 5px 0 0; font-style: italic; }

        .rows td { padding: 6px 0; border-bottom: 1px dashed #e5e7eb; }
        .rows td:first-child { color: #6b7280; width: 40%; }
        .rows td:last-child { text-align: right; font-weight: 600; }

        .balance {
            margin-top: 16px; padding: 11px 14px;
            background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;
        }
        .balance td { padding: 3px 0; }
        .balance td:last-child { text-align: right; font-weight: 600; }
        .balance .due td { color: #991b1b; font-weight: 700; }

        .sig { margin-top: 34px; }
        .sig-line { border-top: 1px solid #9ca3af; padding-top: 4px; font-size: 9px; color: #6b7280; }

        .foot {
            margin-top: 18px; padding-top: 10px; border-top: 1px solid #e5e7eb;
            text-align: center; font-size: 9px; color: #9ca3af;
        }

        .toolbar { max-width: 640px; margin: 0 auto 14px; text-align: right; }
        .btn {
            padding: 7px 14px; border: 1px solid #d1d5db; border-radius: 6px;
            background: #fff; color: #374151; cursor: pointer;
            text-decoration: none; font-size: 12px; display: inline-block;
        }
        .btn-primary { background: #fc5523; border-color: #fc5523; color: #fff; font-weight: 600; }

        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: none; border-radius: 0; max-width: none; padding: 0; }
            .toolbar { display: none !important; }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
        @if ($booking)
            <a class="btn" href="{{ route('bookings.show', $booking) }}">Back to booking</a>
        @endif
    </div>

    <div class="sheet">
        <div class="head">
            <table>
                <tr>
                    <td style="width:58%;vertical-align:top">
                        <p class="hall">{{ $hall->name ?? 'Hall' }}</p>
                        <div class="meta">
                            {{ $hall->full_address ?? '' }}<br>
                            @if ($hall?->phone)Phone: {{ $hall->phone }}@endif
                            @if ($hall?->ntn_number)<br>NTN: {{ $hall->ntn_number }}@endif
                        </div>
                    </td>
                    <td style="width:42%;vertical-align:top">
                        <p class="title">{{ $isRefund ? 'Refund Voucher' : 'Payment Receipt' }}</p>
                        <div class="rmeta">
                            <b>{{ $payment->receipt_number }}</b><br>
                            Date: {{ $payment->paid_on?->format('d M Y') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="amount-box">
            <p class="amount-label">{{ $isRefund ? 'Amount Refunded' : 'Amount Received' }}</p>
            <p class="amount">Rs. {{ number_format($payment->amount, 2) }}</p>
            <p class="words">{{ ucfirst(\Illuminate\Support\Number::spell((int) $payment->amount)) }} rupees only</p>
        </div>

        <table class="rows">
            <tr>
                <td>{{ $isRefund ? 'Refunded to' : 'Received from' }}</td>
                <td>{{ $customer->name ?? '—' }}</td>
            </tr>
            @if ($customer?->phone)
                <tr>
                    <td>Contact</td>
                    <td>{{ $customer->phone }}</td>
                </tr>
            @endif
            @if ($customer?->formatted_cnic)
                <tr>
                    <td>CNIC</td>
                    <td>{{ $customer->formatted_cnic }}</td>
                </tr>
            @endif
            <tr>
                <td>Booking number</td>
                <td>{{ $booking->formatted_booking_number ?? '—' }}</td>
            </tr>
            <tr>
                <td>Event</td>
                <td>
                    {{ $booking->event_type_label ?? 'Event' }}
                    @if ($booking?->start_datetime) · {{ $booking->start_datetime->format('d M Y') }} @endif
                </td>
            </tr>
            <tr>
                <td>Venue</td>
                <td>{{ $booking->lawn->name ?? ($hall->name ?? '—') }}</td>
            </tr>
            <tr>
                <td>Payment method</td>
                <td>{{ $payment->method_label }}</td>
            </tr>
            @if ($payment->reference)
                <tr>
                    <td>Reference</td>
                    <td>{{ $payment->reference }}</td>
                </tr>
            @endif
            <tr>
                <td>Received by</td>
                <td>{{ $payment->receiver->name ?? '—' }}</td>
            </tr>
        </table>

        @if ($booking)
            <div class="balance">
                <table>
                    <tr>
                        <td>Total bill</td>
                        <td>Rs. {{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total received to date</td>
                        <td>Rs. {{ number_format($booking->amount_paid, 2) }}</td>
                    </tr>
                    <tr class="due">
                        <td>Balance remaining</td>
                        <td>Rs. {{ number_format(max($booking->balance_due, 0), 2) }}</td>
                    </tr>
                </table>
            </div>
        @endif

        @if ($payment->notes)
            <p style="margin-top:14px;font-size:10px;color:#6b7280"><b>Notes:</b> {{ $payment->notes }}</p>
        @endif

        <div class="sig">
            <table>
                <tr>
                    <td style="width:45%"><div class="sig-line">Customer Signature</div></td>
                    <td style="width:10%"></td>
                    <td style="width:45%"><div class="sig-line">Authorised Signature</div></td>
                </tr>
            </table>
        </div>

        <div class="foot">
            Computer-generated receipt · {{ now()->format('d M Y, h:i A') }}<br>
            Please retain this receipt for your records.
        </div>
    </div>
</body>

</html>
