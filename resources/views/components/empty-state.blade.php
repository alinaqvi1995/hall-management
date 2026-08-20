@props([
    'icon' => 'inbox',
    'title' => 'Nothing here yet',
    'message' => null,
    'colspan' => null,
])

{{--
    Shown instead of a bare empty table. Pass `colspan` to render it as a table
    row; otherwise it renders as a block.
--}}
@if ($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="text-center py-5 border-0">
            <div class="empty-state">
                <i class="material-icons-outlined">{{ $icon }}</i>
                <p class="empty-state__title">{{ $title }}</p>
                @if ($message)<p class="empty-state__message">{{ $message }}</p>@endif
                @isset($action)<div class="mt-3">{{ $action }}</div>@endisset
            </div>
        </td>
    </tr>
@else
    <div class="empty-state py-5 text-center">
        <i class="material-icons-outlined">{{ $icon }}</i>
        <p class="empty-state__title">{{ $title }}</p>
        @if ($message)<p class="empty-state__message">{{ $message }}</p>@endif
        @isset($action)<div class="mt-3">{{ $action }}</div>@endisset
    </div>
@endif
