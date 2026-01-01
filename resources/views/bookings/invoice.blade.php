<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $booking->formatted_booking_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .invoice-container {
            max-width: 850px;
            margin: 30px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .hall-info img {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }

        .hall-info h2 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .hall-info p {
            margin: 5px 0;
            color: #7f8c8d;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h1 {
            margin: 0;
            font-size: 32px;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .invoice-meta .meta-item {
            margin-top: 5px;
        }

        .invoice-meta .label {
            font-weight: bold;
            color: #7f8c8d;
        }

        /* Details Grid */
        .details-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }

        .details-col {
            width: 48%;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-row .label {
            width: 100px;
            font-weight: bold;
            color: #555;
            flex-shrink: 0;
        }

        .info-row .value {
            color: #333;
        }

        /* Tables */
        .table-container {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            background: #fdfdfd;
            border-bottom: 2px solid #ddd;
        }

        .grand-total {
            font-size: 18px;
            color: #e74c3c;
        }

        /* Facilities badge */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            background: #eef2f3;
            color: #2c3e50;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
        }

        /* Signature */
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .sign-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-weight: bold;
        }

        /* Print Control */
        @media print {
            body {
                background: #fff;
            }

            .invoice-container {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .badge {
                border: 1px solid #999;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-container">
        <!-- HEADER -->
        <div class="header">
            <div class="hall-info">
                @if ($booking->hall && $booking->hall->logo)
                    <img src="{{ asset($booking->hall->logo) }}" alt="Logo">
                @else
                    <h2>{{ $booking->hall->name ?? 'Hall Management' }}</h2>
                @endif
                <p>{{ $booking->hall->address ?? 'Address Not Available' }}</p>
                <p>{{ $booking->hall->city ?? '' }} {{ $booking->hall->state ? ', ' . $booking->hall->state : '' }}</p>
                <p><strong>Phone:</strong> {{ $booking->hall->phone ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $booking->hall->email ?? '-' }}</p>
            </div>
            <div class="invoice-meta">
                <h1>Invoice</h1>
                <div class="meta-item">
                    <span class="label">Invoice #:</span> {{ $booking->formatted_booking_number }}
                </div>
                <div class="meta-item">
                    <span class="label">Date:</span> {{ now()->format('d M, Y') }}
                </div>
                <div class="meta-item">
                    <span class="label">Status:</span>
                    <span
                        style="color: {{ $booking->status === 'confirmed' ? 'green' : 'orange' }}; font-weight: bold; text-transform: uppercase;">
                        {{ $booking->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- CLIENT & EVENT INFO -->
        <div class="details-grid">
            <!-- Client -->
            <div class="details-col">
                <div class="section-title">Bill To</div>
                <div class="info-row"><span class="label">Name:</span> <span
                        class="value">{{ $booking->customer->name ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Phone:</span> <span
                        class="value">{{ $booking->customer->phone ?? '-' }}</span></div>
                <div class="info-row"><span class="label">CNIC:</span> <span
                        class="value">{{ $booking->customer->cnic ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Email:</span> <span
                        class="value">{{ $booking->customer->email ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Address:</span> <span
                        class="value">{{ $booking->customer->address ?? '-' }}</span></div>
            </div>

            <!-- Event -->
            <div class="details-col">
                <div class="section-title">Event Details</div>
                <div class="info-row"><span class="label">Event Start:</span> <span
                        class="value">{{ $booking->start_datetime->format('d M, Y (h:i A)') }}</span></div>
                <div class="info-row"><span class="label">Event End:</span> <span
                        class="value">{{ $booking->end_datetime->format('d M, Y (h:i A)') }}</span></div>
                <div class="info-row"><span class="label">Hall / Lawn:</span> <span
                        class="value">{{ $booking->hall->name }} / {{ $booking->lawn->name }}</span></div>
                <div class="info-row"><span class="label">Guests:</span> <span class="value">{{ $booking->capacity }}
                        (Limit: {{ $booking->lawn->capacity ?? 'N/A' }})</span></div>
                @if (!empty($booking->facilities))
                    <div class="info-row">
                        <span class="label">Facilities:</span>
                        <span class="value">
                            @foreach ($booking->facilities as $facility)
                                <span class="badge">{{ ucwords(str_replace('_', ' ', $facility)) }}</span>
                            @endforeach
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- FINANCIALS -->
        <div class="table-container">
            <div class="section-title">Payment Summary</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Hall Booking Charges</strong><br>
                            <small style="color: #666;">Rental charges for {{ $booking->hall->name }} -
                                {{ $booking->lawn->name }}</small>
                        </td>
                        <td class="text-right">{{ number_format($booking->booking_price, 2) }}</td>
                    </tr>
                    <!-- Can add extra rows here if we had detailed line items -->

                    <tr class="total-row">
                        <td>Total Amount</td>
                        <td class="text-right">{{ number_format($booking->booking_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Advance Paid</td>
                        <td class="text-right text-success">- {{ number_format($booking->advance_paid, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Balance Due</td>
                        <td class="text-right grand-total">
                            {{ number_format($booking->booking_price - $booking->advance_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Status</strong></td>
                        <td class="text-right" style="text-transform: capitalize;">{{ $booking->payment_status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- NOTES -->
        @if ($booking->notes)
            <div class="table-container">
                <div class="section-title">Notes & Special Instructions</div>
                <p style="background: #fffbe6; padding: 10px; border: 1px solid #ffe58f; border-radius: 4px;">
                    {{ $booking->notes }}
                </p>
            </div>
        @endif

        <!-- SIGNATURES -->
        <div class="signatures">
            <div class="sign-box">Manager Signature</div>
            <div class="sign-box">Customer Signature</div>
        </div>

        <div class="no-print"
            style="text-align: center; margin-top: 40px; border-top: 1px dashed #ccc; padding-top: 20px;">
            <button onclick="window.print()"
                style="background: #2c3e50; color: #fff; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 4px;">Print
                Invoice</button>
        </div>
    </div>

</body>

</html>
