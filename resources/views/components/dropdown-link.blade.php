<a {{ $attributes->merge(['class' => 'flex w-full items-center gap-2.5 px-4 py-2.5 text-start text-[0.8125rem] text-ink transition-colors duration-100 last:border-b-0 border-b border-line hover:bg-paper-deep focus:outline-none focus-visible:bg-paper-deep']) }}>
    {{ $slot }}
</a>
