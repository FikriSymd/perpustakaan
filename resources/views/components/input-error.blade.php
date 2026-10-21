@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-2 flex items-start gap-1.5 text-[0.8125rem] text-rust-deep']) }} role="alert">
        <x-icon name="alert" :size="14" class="mt-0.5 shrink-0" />
        <ul class="space-y-1">
            @foreach ((array) $messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </ul>
@endif
