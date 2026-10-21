@props(['value'])

<label {{ $attributes->merge(['class' => 'label-strong block']) }}>
    {{ $value ?? $slot }}
</label>
