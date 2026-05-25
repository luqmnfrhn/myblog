@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @class([
        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        'bg-stone-100 text-stone-900' => $active,
        'text-stone-600 hover:bg-stone-50 hover:text-stone-900' => ! $active,
    ])
>
    <span @class(['shrink-0', 'text-stone-500' => ! $active, 'text-stone-900' => $active]) aria-hidden="true">
        {{ $icon }}
    </span>
    {{ $slot }}
</a>
