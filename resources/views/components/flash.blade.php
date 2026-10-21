@props(['type' => 'success'])

@php
    $config = [
        'success' => ['icon' => 'check',  'border' => 'border-moss/40',   'bg' => 'bg-moss-pale',   'text' => 'text-moss-deep'],
        'error'   => ['icon' => 'alert',  'border' => 'border-rust/40',   'bg' => 'bg-rust-pale',   'text' => 'text-rust-deep'],
        'status'  => ['icon' => 'info',   'border' => 'border-brass/40',  'bg' => 'bg-brass-pale',  'text' => 'text-brass'],
    ];
    $c = $config[$type] ?? $config['success'];
@endphp

@if (session($type))
    <div class="mb-6 flex items-start gap-3 border {{ $c['border'] }} {{ $c['bg'] }} px-4 py-3" role="alert">
        <x-icon :name="$c['icon']" :size="16" class="mt-0.5 {{ $c['text'] }}" />
        <p class="text-sm leading-relaxed {{ $c['text'] }}">{{ session($type) }}</p>
    </div>
@endif
