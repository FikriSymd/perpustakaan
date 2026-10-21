<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Kartu Anggota</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Peminjaman Saya</h1>
            </div>
            <span class="chip-brass self-start sm:self-end">
                <x-icon name="book" :size="13" />
                {{ $activeCount }} sedang dipinjam
            </span>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        {{-- Desktop ledger table --}}
        <div class="card hidden overflow-hidden md:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-line bg-paper-deep/60">
                        <th class="label px-5 py-3 text-start">Buku</th>
                        <th class="label px-5 py-3 text-start">Pinjam</th>
                        <th class="label px-5 py-3 text-start">Jatuh Tempo</th>
                        <th class="label px-5 py-3 text-start">Status</th>
                        <th class="label px-5 py-3 text-end">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($loans as $loan)
                        <tr class="transition-colors hover:bg-paper-deep/40">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('catalog.show', $loan->book) }}" class="font-medium text-ink transition-colors hover:text-moss">
                                    {{ $loan->book->title }}
                                </a>
                                <p class="mt-0.5 text-[0.75rem] text-ink-mute">{{ $loan->book->author }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-ink-soft">{{ $loan->borrowed_at->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5 text-ink-soft">
                                {{ $loan->due_at->translatedFormat('d M Y') }}
                                @if ($loan->returned_at)
                                    <p class="text-[0.75rem] text-moss-deep">kembali {{ $loan->returned_at->translatedFormat('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @php $overdue = $loan->status === \App\Models\Loan::STATUS_DIPINJAM && $loan->isOverdue(); @endphp
                                @if ($loan->status === \App\Models\Loan::STATUS_DIPINJAM)
                                    <span class="chip {{ $overdue ? 'chip-rust' : 'chip-brass' }}">
                                        <x-icon name="{{ $overdue ? 'clock' : 'return' }}" :size="12" />
                                        {{ $overdue ? 'Terlambat' : 'Dipinjam' }}
                                    </span>
                                @else
                                    <span class="chip-moss">
                                        <x-icon name="check" :size="12" />
                                        Dikembalikan
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-end">
                                @if ($loan->calculateFine() > 0)
                                    <span class="font-medium text-rust-deep">Rp {{ number_format($loan->calculateFine(), 0, ',', '.') }}</span>
                                @else
                                    <span class="text-ink-mute">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <x-icon name="book-open" :size="24" class="mx-auto text-ink-mute" />
                                <p class="mt-3 text-sm text-ink-soft">Belum ada riwayat peminjaman.</p>
                                <a href="{{ route('catalog.index') }}" class="link mt-2 inline-block text-sm">Mulai jelajahi katalog</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile record cards --}}
        <div class="space-y-3 md:hidden">
            @forelse ($loans as $loan)
                @php $overdue = $loan->status === \App\Models\Loan::STATUS_DIPINJAM && $loan->isOverdue(); @endphp
                <article class="card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('catalog.show', $loan->book) }}" class="font-display text-[1.0625rem] leading-snug text-ink transition-colors hover:text-moss">
                            {{ $loan->book->title }}
                        </a>
                        @if ($loan->status === \App\Models\Loan::STATUS_DIPINJAM)
                            <span class="chip {{ $overdue ? 'chip-rust' : 'chip-brass' }} shrink-0">
                                <x-icon name="{{ $overdue ? 'clock' : 'return' }}" :size="12" />
                                {{ $overdue ? 'Terlambat' : 'Dipinjam' }}
                            </span>
                        @else
                            <span class="chip-moss shrink-0">
                                <x-icon name="check" :size="12" />
                                Kembali
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-[0.8125rem] text-ink-mute">{{ $loan->book->author }}</p>

                    <dl class="mt-3 grid grid-cols-3 gap-3 border-t border-line pt-3">
                        <div>
                            <dt class="label !text-[0.5625rem]">Pinjam</dt>
                            <dd class="mt-1 text-[0.8125rem] text-ink-soft">{{ $loan->borrowed_at->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="label !text-[0.5625rem]">Tempo</dt>
                            <dd class="mt-1 text-[0.8125rem] text-ink-soft">{{ $loan->due_at->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="label !text-[0.5625rem]">Denda</dt>
                            <dd class="mt-1 text-[0.8125rem] {{ $loan->calculateFine() > 0 ? 'font-medium text-rust-deep' : 'text-ink-mute' }}">
                                {{ $loan->calculateFine() > 0 ? 'Rp ' . number_format($loan->calculateFine(), 0, ',', '.') : '—' }}
                            </dd>
                        </div>
                    </dl>
                </article>
            @empty
                <div class="card px-6 py-12 text-center">
                    <x-icon name="book-open" :size="24" class="mx-auto text-ink-mute" />
                    <p class="mt-3 text-sm text-ink-soft">Belum ada riwayat peminjaman.</p>
                    <a href="{{ route('catalog.index') }}" class="btn-ghost mx-auto mt-4">
                        <x-icon name="search" :size="15" />
                        Jelajahi Katalog
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $loans->links() }}
        </div>
    </x-page-container>
</x-app-layout>
