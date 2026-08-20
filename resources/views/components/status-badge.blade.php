@props([
    'label',
    'tone' => 'secondary',
    'icon' => null,
])

<span class="badge rounded-pill text-bg-{{ $tone }} d-inline-flex align-items-center gap-1">
    @if ($icon)<i class="material-icons-outlined fs-6">{{ $icon }}</i>@endif
    {{ $label }}
</span>
