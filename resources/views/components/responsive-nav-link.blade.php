@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-2.5 border-s-2 border-moss bg-paper-deep px-4 py-3 text-[0.9375rem] font-medium text-ink transition-colors'
            : 'flex items-center gap-2.5 border-s-2 border-transparent px-4 py-3 text-[0.9375rem] text-ink-soft transition-colors hover:border-line hover:bg-paper-deep/60 hover:text-ink';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
