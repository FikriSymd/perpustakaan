@props(['active'])

<a {{ $attributes->merge(['class' => ($active ?? false)
    ? 'flex items-center gap-2 text-ink'
    : 'flex items-center gap-2 text-ink-mute transition-colors hover:text-ink']) }}>
    {{ $slot }}
</a>
