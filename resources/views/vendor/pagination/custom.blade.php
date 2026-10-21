{{-- Pagination "arsip": nomor halaman bergaya indeks katalog, hairline border, tanpa rounded. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-4 border-t border-line pt-5">
        {{-- Info rentang --}}
        <p class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">
            @if ($paginator->firstItem())
                Arsip {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} dari {{ $paginator->total() }} entri
            @else
                Tidak ada entri
            @endif
        </p>

        {{-- Tombol halaman --}}
        <div class="flex items-center gap-1">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya"
                      class="inline-flex h-9 items-center gap-1.5 border border-line px-3 font-mono text-[0.625rem] uppercase tracking-label text-ink-mute/50">
                    <x-icon name="chevron-left" :size="13" />
                    <span class="hidden sm:inline">Sebelum</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                   class="inline-flex h-9 items-center gap-1.5 border border-line bg-paper px-3 font-mono text-[0.625rem] uppercase tracking-label text-ink transition-colors hover:bg-paper-deep hover:text-moss-deep">
                    <x-icon name="chevron-left" :size="13" />
                    <span class="hidden sm:inline">Sebelum</span>
                </a>
            @endif

            {{-- Nomor halaman --}}
            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    {{-- "Tiga titik" pemisah --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex h-9 w-9 items-center justify-center font-mono text-[0.625rem] text-ink-mute">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex h-9 w-9 items-center justify-center border border-moss-deep bg-moss-deep font-mono text-xs font-medium text-paper">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="inline-flex h-9 w-9 items-center justify-center border border-line bg-paper font-mono text-xs text-ink transition-colors hover:bg-paper-deep hover:text-moss-deep">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Posisi saat ini (mobile) --}}
            <span class="inline-flex h-9 items-center border border-line bg-paper px-3 font-mono text-xs text-ink sm:hidden">
                {{ $paginator->currentPage() }} <span class="text-ink-mute">/ {{ $paginator->lastPage() }}</span>
            </span>

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                   class="inline-flex h-9 items-center gap-1.5 border border-line bg-paper px-3 font-mono text-[0.625rem] uppercase tracking-label text-ink transition-colors hover:bg-paper-deep hover:text-moss-deep">
                    <span class="hidden sm:inline">Lanjut</span>
                    <x-icon name="chevron-right" :size="13" />
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman berikutnya"
                      class="inline-flex h-9 items-center gap-1.5 border border-line px-3 font-mono text-[0.625rem] uppercase tracking-label text-ink-mute/50">
                    <span class="hidden sm:inline">Lanjut</span>
                    <x-icon name="chevron-right" :size="13" />
                </span>
            @endif
        </div>
    </nav>
@endif
