@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-400 text-start text-base font-medium text-white bg-white/10 focus:outline-none focus:text-white focus:bg-white/10 focus:border-brand-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-cream/70 hover:text-white hover:bg-white/5 hover:border-cream/30 focus:outline-none focus:text-white focus:bg-white/5 focus:border-cream/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
