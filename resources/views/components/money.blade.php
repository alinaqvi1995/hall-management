@props([
    'amount' => 0,
    'decimals' => 0,
    'tone' => null,
    'zero' => null,
])

{{-- Rupee amounts, always formatted the same way across the app. --}}
@php
    $value = (float) $amount;
    $classes = trim('money '.($tone ? 'text-'.$tone : ''));
@endphp

<span class="{{ $classes }}" @if ($value != 0) title="Rs. {{ number_format($value, 2) }}" @endif>
    @if ($value == 0 && $zero !== null)
        {{ $zero }}
    @else
        <span class="money__unit">Rs.</span> {{ number_format($value, $decimals) }}
    @endif
</span>
