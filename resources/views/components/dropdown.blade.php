@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'border border-ink bg-paper-soft shadow-stamp'])

@php
    $alignmentClasses = match ($align) {
        'left' => 'start-0 origin-top-left',
        'top' => 'origin-top',
        default => 'end-0 origin-top-right',
    };

    $width = match ($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        default => $width,
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out-quart duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-50 mt-2 {{ $width }} {{ $alignmentClasses }}"
        @click="open = false"
    >
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
