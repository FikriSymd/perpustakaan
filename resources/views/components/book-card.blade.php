@props(['book'])

@php
    $available = ($book->stock_available ?? $book->copies_available ?? 0) > 0;
    $code = collect(str_split(mb_strtoupper(mb_substr(preg_replace('/[^A-Za-z]/', '', $book->title ?? 'X') ?: 'X', 0, 3))))
        ->pad(3, '·')->implode('');
    $callNumber = mb_strtoupper(mb_substr(preg_replace('/[^A-Za-z]/', '', $book->category?->name ?? 'Umum') ?: 'UMM', 0, 3))
        . '.' . str_pad((string) $book->id, 3, '0', STR_PAD_LEFT);
@endphp

<article class="card group relative flex h-full flex-col transition-all duration-150 hover:-translate-y-0.5 hover:shadow-stamp">
    <a href="{{ route('catalog.show', $book) }}" class="flex h-full flex-col">
        {{-- Cover plate --}}
        <div class="relative aspect-[4/3] overflow-hidden border-b border-line bg-moss-deep">
            <div aria-hidden="true" class="absolute inset-0 opacity-[0.16]"
                 style="background-image: linear-gradient(to right, #F4F0E4 1px, transparent 1px); background-size: 1.75rem 100%;"></div>

            @if (! empty($book->cover_path))
                <img src="{{ Storage::url($book->cover_path) }}" alt="Sampul {{ $book->title }}"
                     class="absolute inset-0 h-full w-full object-cover">
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 px-4 text-center">
                    <span class="font-mono text-[0.625rem] uppercase tracking-label text-brass-bright">{{ $code }}</span>
                    <span class="line-clamp-3 font-display text-lg leading-snug text-paper">{{ $book->title }}</span>
                </div>
            @endif

            <span class="absolute start-0 top-3 bg-brass px-2 py-1 font-mono text-[0.5625rem] uppercase tracking-label text-ink">
                {{ $callNumber }}
            </span>
        </div>

        {{-- Record body --}}
        <div class="flex flex-1 flex-col p-4">
            <p class="label !text-[0.5625rem]">{{ $book->category?->name ?? 'Umum' }}</p>
            <h3 class="mt-2 line-clamp-2 font-display text-[1.0625rem] leading-snug text-ink transition-colors group-hover:text-moss">
                {{ $book->title }}
            </h3>
            <p class="mt-1.5 text-[0.8125rem] text-ink-soft">{{ $book->author }}</p>

            <div class="mt-auto flex items-center justify-between gap-2 border-t border-line pt-3 [margin-top:auto]">
                <span class="flex items-center gap-1.5 font-mono text-[0.625rem] uppercase tracking-label">
                    <span aria-hidden="true" class="h-1.5 w-1.5 {{ $available ? 'bg-moss-bright' : 'bg-rust' }}"></span>
                    <span class="{{ $available ? 'text-moss-deep' : 'text-rust-deep' }}">{{ $available ? 'Tersedia' : 'Dipinjam' }}</span>
                </span>
                <x-icon name="arrow-right" :size="14" class="text-ink-mute transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-brass" />
            </div>
        </div>
    </a>
</article>
