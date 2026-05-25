@props(['href', 'active' => false, 'label'])

<a
    href="{{ $href }}"
    @class([
        'flex flex-1 flex-col items-center justify-center gap-1 py-2 text-xs font-medium transition-colors',
        'text-accent' => $active,
        'text-stone-500 hover:text-stone-900' => ! $active,
    ])
    aria-current="{{ $active ? 'page' : 'false' }}"
>
    <span aria-hidden="true">{{ $slot }}</span>
    <span>{{ $label }}</span>
</a>
