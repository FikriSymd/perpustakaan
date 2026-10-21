@props(['size' => 32, 'stroke' => 1.5])

{{-- Mark: a card-catalogue drawer seen head-on — stacked records under an index
     tab. Drawn on the same 24-unit grid as the icon set. --}}
<svg
    {{ $attributes->merge([
        'width' => $size,
        'height' => $size,
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => $stroke,
        'stroke-linecap' => 'square',
        'stroke-linejoin' => 'miter',
        'aria-hidden' => 'true',
        'focusable' => 'false',
    ]) }}
>
    <path d="M3 8.5h18v12H3z" />
    <path d="M3 8.5 6 3h12l3 5.5" />
    <path d="M9 8.5V6.2M15 8.5V6.2" />
    <path d="M6.5 12.5h11M6.5 16h11" />
    <circle cx="12" cy="19.4" r="0.9" fill="currentColor" stroke="none" />
</svg>
