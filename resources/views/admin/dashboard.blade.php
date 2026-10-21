<x-app-layout>
    <x-slot name="header">
        <p class="label">Meja Sirkulasi</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Dashboard Petugas</h1>
    </x-slot>

    <x-page-container>
        <x-flash />

        {{-- Stat plates --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'book',     'label' => 'Total Judul',        'value' => number_format($totalTitles),  'note' => number_format($totalBooks) . ' eksemplar'],
                ['icon' => 'return',   'label' => 'Sedang Dipinjam',    'value' => number_format($activeLoans),  'note' => null],
                ['icon' => 'clock',    'label' => 'Terlambat',          'value' => number_format($overdueLoans), 'note' => null, 'tone' => $overdueLoans > 0 ? 'rust' : null],
                ['icon' => 'layers',   'label' => 'Stok Menipis',       'value' => number_format($lowStock),     'note' => 'judul dengan sisa ≤ 1', 'tone' => $lowStock > 0 ? 'brass' : null],
            ] as $stat)
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <p class="label">{{ $stat['label'] }}</p>
                        <x-icon :name="$stat['icon']" :size="18"
                                class="{{ ($stat['tone'] ?? null) === 'rust' ? 'text-rust' : (($stat['tone'] ?? null) === 'brass' ? 'text-brass' : 'text-ink-mute') }}" />
                    </div>
                    <p class="mt-3 font-display text-3xl {{ ($stat['tone'] ?? null) === 'rust' ? 'text-rust-deep' : 'text-ink' }}">{{ $stat['value'] }}</p>
                    @if ($stat['note'])
                        <p class="mt-1 text-[0.75rem] text-ink-mute">{{ $stat['note'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Recent loans ledger --}}
        <div class="card mt-8">
            <div class="flex items-center justify-between border-b border-line px-5 py-4">
                <h2 class="font-display text-lg text-ink">Peminjaman Terbaru</h2>
                <a href="{{ route('admin.loans.index') }}" class="group inline-flex items-center gap-1 font-mono text-[0.625rem] uppercase tracking-label text-ink-mute transition-colors hover:text-ink">
                    Lihat semua
                    <x-icon name="arrow-right" :size="13" class="transition-transform duration-200 group-hover:translate-x-0.5" />
                </a>
            </div>

            <ul class="divide-y divide-line">
                @forelse ($recentLoans as $loan)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-[0.9375rem] font-medium text-ink">{{ $loan->book->title }}</p>
                            <p class="mt-0.5 text-[0.8125rem] text-ink-mute">
                                {{ $loan->user->name }} &middot; pinjam {{ $loan->borrowed_at->translatedFormat('d M Y') }}
                            </p>
                        </div>

                        @php $overdue = $loan->status === \App\Models\Loan::STATUS_DIPINJAM && $loan->isOverdue(); @endphp
                        @if ($loan->status === \App\Models\Loan::STATUS_DIPINJAM)
                            <span class="chip {{ $overdue ? 'chip-rust' : 'chip-brass' }}">
                                <x-icon name="{{ $overdue ? 'clock' : 'return' }}" :size="12" />
                                {{ $overdue ? 'Terlambat' : 'Dipinjam' }}
                            </span>
                        @else
                            <span class="chip-moss">
                                <x-icon name="check" :size="12" />
                                Kembali
                            </span>
                        @endif
                    </li>
                @empty
                    <li class="px-5 py-10 text-center">
                        <x-icon name="archive" :size="22" class="mx-auto text-ink-mute" />
                        <p class="mt-3 text-sm text-ink-mute">Belum ada peminjaman tercatat.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </x-page-container>
</x-app-layout>
