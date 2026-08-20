@props([
    'label',
    'value',
    'icon' => 'insights',
    'tone' => 'primary',
    'hint' => null,
    'href' => null,
])

{{-- Compact KPI tile. Wrapped in a link when `href` is given. --}}
@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    class="stat-card card h-100 border-0 shadow-sm text-decoration-none {{ $href ? 'stat-card--link' : '' }}">
    <div class="card-body d-flex align-items-start gap-3">
        <span class="stat-card__icon bg-{{ $tone }}-subtle text-{{ $tone }}">
            <i class="material-icons-outlined">{{ $icon }}</i>
        </span>
        <div class="min-w-0">
            <p class="stat-card__label mb-1">{{ $label }}</p>
            <p class="stat-card__value mb-0">{{ $value }}</p>
            @if ($hint)
                <p class="stat-card__hint mb-0">{{ $hint }}</p>
            @endif
        </div>
    </div>
</{{ $tag }}>
