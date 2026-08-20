@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'breadcrumbs' => [],
])

{{--
    Standard page heading: icon + title on the left, action buttons on the right,
    breadcrumb trail above. Every screen uses this so headings stay consistent.
--}}
<div class="page-header mb-4">
    @if (count($breadcrumbs))
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0 p-0 small">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                        <i class="material-icons-outlined align-middle fs-6">home</i>
                    </a>
                </li>
                @foreach ($breadcrumbs as $label => $url)
                    @if ($loop->last || $url === null)
                        <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            @if ($icon)
                <span class="page-header__icon d-none d-sm-flex">
                    <i class="material-icons-outlined">{{ $icon }}</i>
                </span>
            @endif
            <div>
                <h4 class="mb-0 fw-semibold">{{ $title }}</h4>
                @if ($subtitle)
                    <p class="mb-0 text-secondary small">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @isset($actions)
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
