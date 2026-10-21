@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-2 border border-moss/40 bg-moss-pale px-3 py-2.5 text-[0.8125rem] text-moss-deep']) }}>
        <x-icon name="check" :size="14" class="mt-0.5 shrink-0" />
        {{ $status }}
    </div>
@endif
