@php
    $callNumber = mb_strtoupper(mb_substr(preg_replace('/[^A-Za-z]/', '', $book->category?->name ?? 'Umum') ?: 'UMM', 0, 3))
        . '.' . str_pad((string) $book->id, 3, '0', STR_PAD_LEFT);
@endphp
<x-app-layout>
    <x-page-container>
        <a href="{{ route('catalog.index') }}" class="group inline-flex items-center gap-1.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-mute transition-colors hover:text-ink">
            <x-icon name="arrow-left" :size="14" class="transition-transform duration-200 group-hover:-translate-x-0.5" />
            Kembali ke katalog
        </a>

        <div class="mt-6 grid gap-8 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)] lg:gap-10">
            {{-- Cover plate --}}
            <div class="lg:sticky lg:top-28 lg:self-start">
                <div class="card relative">
                    <div class="relative aspect-[3/4] overflow-hidden bg-moss-deep">
                        <div aria-hidden="true" class="absolute inset-0 opacity-[0.16]"
                             style="background-image: linear-gradient(to right, #F4F0E4 1px, transparent 1px); background-size: 1.75rem 100%;"></div>

                        @if ($book->cover_path)
                            <img src="{{ Storage::url($book->cover_path) }}" alt="Sampul {{ $book->title }}" class="absolute inset-0 h-full w-full object-cover">
                        @else
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center">
                                <span class="font-mono text-[0.625rem] uppercase tracking-label text-brass-bright">{{ $callNumber }}</span>
                                <span class="font-display text-2xl leading-snug text-paper">{{ $book->title }}</span>
                                <span class="font-mono text-[0.625rem] uppercase tracking-label text-paper/50">oleh {{ $book->author }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 border border-line bg-paper-soft px-4 py-3">
                    @if ($book->hasStock())
                        <p class="flex items-center gap-2 text-[0.8125rem] text-moss-deep">
                            <span aria-hidden="true" class="h-2 w-2 bg-moss-bright"></span>
                            Tersedia &mdash; {{ $book->stock_available }} dari {{ $book->stock_total }} eksemplar
                        </p>
                    @else
                        <p class="flex items-center gap-2 text-[0.8125rem] text-rust-deep">
                            <span aria-hidden="true" class="h-2 w-2 bg-rust"></span>
                            Semua eksemplar sedang dipinjam
                        </p>
                    @endif
                </div>
            </div>

            {{-- Record sheet --}}
            <div>
                <div class="card p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="chip-brass">{{ $book->category->name }}</span>
                        <span class="chip">{{ 'No. Panggil ' . $callNumber }}</span>
                    </div>

                    <h1 class="mt-4 font-display text-3xl leading-tight text-ink sm:text-4xl">{{ $book->title }}</h1>
                    <p class="mt-2 text-[0.9375rem] text-ink-soft">oleh <span class="font-medium text-ink">{{ $book->author }}</span></p>

                    <dl class="mt-7 grid grid-cols-2 gap-x-6 gap-y-5 border-t border-line pt-6 sm:grid-cols-4">
                        @foreach ([
                            'Penerbit' => $book->publisher,
                            'Tahun Terbit' => $book->publication_year,
                            'ISBN' => $book->isbn,
                            'Total Eksemplar' => $book->stock_total,
                        ] as $dtLabel => $dtValue)
                            @if ($dtValue)
                                <div>
                                    <dt class="label !text-[0.5625rem]">{{ $dtLabel }}</dt>
                                    <dd class="mt-1.5 text-[0.9375rem] font-medium text-ink">{{ $dtValue }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>

                    @if ($book->description)
                        <div class="mt-7 border-t border-line pt-6">
                            <h2 class="label-strong">Sinopsis</h2>
                            <p class="mt-3 leading-relaxed text-ink-soft whitespace-pre-line">{{ $book->description }}</p>
                        </div>
                    @endif

                    @if (auth()->check())
                        @php
                            $myOpenReservation = auth()->user()->reservations()
                                ->where('book_id', $book->id)
                                ->open()
                                ->first();
                            $isHolding = $book->loans()
                                ->where('user_id', auth()->id())
                                ->where('status', \App\Models\Loan::STATUS_DIPINJAM)
                                ->exists();
                        @endphp
                        <div class="mt-7 border-t border-line pt-6">
                            @if ($isHolding)
                                <p class="flex items-center gap-2 font-mono text-[0.6875rem] uppercase tracking-label text-ink-soft">
                                    <x-icon name="book" :size="14" />
                                    Buku ini sedang Anda pinjam
                                </p>
                            @elseif ($myOpenReservation)
                                <p class="flex items-center gap-2 font-mono text-[0.6875rem] uppercase tracking-label text-moss-deep">
                                    <x-icon name="bookmark" :size="14" />
                                    @if ($myOpenReservation->isReady())
                                        Reservasi Anda siap — ambil sebelum {{ $myOpenReservation->expires_at?->translatedFormat('d M Y') }}
                                    @else
                                        Anda di antrean #{{ $myOpenReservation->queue_position ?? '—' }}
                                    @endif
                                </p>
                            @else
                                <form method="POST" action="{{ route('reservations.store', $book) }}">
                                    @csrf
                                    <button type="submit" class="btn-ghost">
                                        <x-icon name="bookmark" :size="15" />
                                        @if ($book->hasStock())
                                            Reservasi Buku Ini
                                        @else
                                            Masuk Antrean Reservasi
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($related->isNotEmpty())
                    <h2 class="mt-10 font-display text-xl text-ink">Buku lain di kategori {{ $book->category->name }}</h2>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($related as $relatedBook)
                            <x-book-card :book="$relatedBook" />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-page-container>
</x-app-layout>
