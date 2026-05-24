@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'accessible-field rounded-md']) }}>
