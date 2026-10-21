<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="label">Lemari Katalog &middot; Rak Terbuka</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Koleksi Perpustakaan</h1>
                <p class="mt-2 max-w-xl text-[0.9375rem] leading-relaxed text-ink-soft">
                    Jelajahi koleksi — {{ $books->total() }} {{ $books->total() === 1 ? 'judul' : 'judul' }} tercatat.
                </p>
            </div>

            <form method="GET" action="{{ route('catalog.index') }}" class="w-full md:w-auto">
                @if ($activeCategory)
                    <input type="hidden" name="kategori" value="{{ $activeCategory->slug }}">
                @endif
                <label for="q" class="label mb-1.5 block">Cari Katalog</label>
                <div class="flex w-full md:w-80">
                    <div class="flex flex-1 items-center border border-ink bg-paper-soft px-3">
                        <x-icon name="search" :size="16" class="text-ink-mute" />
                        <input
                            id="q"
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Judul, penulis, ISBN"
                            class="field h-11 border-0"
                            autocomplete="off"
                        >
                    </div>
                    <button type="submit" class="btn-primary shrink-0 !px-4">
                        Telusuri
                    </button>
                </div>
            </form>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        {{-- Category index strip --}}
        <div class="mb-8 border border-line bg-paper-soft">
            <div class="flex items-center justify-between border-b border-line px-4 py-2.5">
                <p class="label !text-[0.625rem]">Indeks Kategori</p>
                @if ($activeCategory || $search)
                    <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1 font-mono text-[0.625rem] uppercase tracking-label text-brass hover:text-brass-bright">
                        <x-icon name="close" :size="12" />
                        Reset
                        @if ($activeCategory) &middot; {{ $activeCategory->name }} @endif
                        @if ($search) &middot; &ldquo;{{ $search }}&rdquo; @endif
                    </a>
                @endif
            </div>
            <div class="flex flex-wrap gap-1.5 p-3">
                <a
                    href="{{ route('catalog.index', array_filter(['q' => $search])) }}"
                    @class([
                        'border px-3 py-1.5 font-mono text-[0.625rem] uppercase tracking-label transition-colors',
                        'border-ink bg-ink text-paper-soft' => ! $activeCategory,
                        'border-line bg-transparent text-ink-soft hover:border-ink hover:text-ink' => $activeCategory,
                    ])
                >Semua</a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('catalog.index', array_filter(['q' => $search, 'kategori' => $category->slug])) }}"
                        @class([
                            'border px-3 py-1.5 font-mono text-[0.625rem] uppercase tracking-label transition-colors',
                            'border-ink bg-ink text-paper-soft' => $activeCategory?->id === $category->id,
                            'border-line bg-transparent text-ink-soft hover:border-ink hover:text-ink' => $activeCategory?->id !== $category->id,
                        ])
                    >
                        {{ $category->name }}
                        <span class="opacity-50">{{ $category->books_count }}</span>
                    </a>
                @endforeach

                <label class="ms-auto inline-flex cursor-pointer items-center gap-2 border border-line px-3 py-1.5 transition-colors hover:border-ink">
                    <input type="checkbox" name="tersedia" value="1"
                           class="h-3.5 w-3.5 cursor-pointer appearance-none border border-ink-mute bg-paper-soft checked:border-moss checked:bg-moss"
                           style="background-image: none;"
                           {{ request('tersedia') ? 'checked' : '' }}
                           onchange="this.closest('form').submit()">
                    <span class="font-mono text-[0.625rem] uppercase tracking-label text-ink-soft">Tersedia</span>
                    @foreach (['q' => $search, 'kategori' => $activeCategory?->slug] as $hk => $hv)
                        @if ($hv)<input type="hidden" name="{{ $hk }}" value="{{ $hv }}">@endif
                    @endforeach
                </label>
            </div>
        </div>

        {{-- Results grid --}}
        @if ($books->isEmpty())
            <div class="border border-line bg-paper-soft px-6 py-16 text-center">
                <x-icon name="search" :size="28" class="mx-auto text-ink-mute" />
                <p class="mt-4 font-display text-xl text-ink">Tidak ada rekaman yang cocok.</p>
                <p class="mt-1.5 text-sm text-ink-soft">Ubah kata kunci atau pilih kategori lain.</p>
                <a href="{{ route('catalog.index') }}" class="btn-ghost mx-auto mt-6">
                    <x-icon name="return" :size="15" />
                    Reset Pencarian
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $books->links() }}
            </div>
        @endif
    </x-page-container>
</x-app-layout>
