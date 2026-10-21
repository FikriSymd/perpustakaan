<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Meja Sirkulasi</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Peminjaman</h1>
            </div>
            <a href="{{ route('admin.loans.create') }}" class="btn-primary self-start sm:self-end">
                <x-icon name="return" :size="15" />
                Pinjamkan Buku
            </a>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        <div class="mb-5 flex flex-wrap gap-3">
            <div class="flex items-center gap-2 border border-line bg-paper-soft px-3 py-2">
                <x-icon name="book" :size="15" class="text-ink-mute" />
                <span class="font-mono text-[0.6875rem] uppercase tracking-label text-ink-soft">
                    <strong class="text-ink">{{ $stats['dipinjam'] }}</strong> dipinjam
                </span>
            </div>
            <div class="flex items-center gap-2 border {{ $stats['terlambat'] > 0 ? 'border-rust/40 bg-rust/5' : 'border-line bg-paper-soft' }} px-3 py-2">
                <x-icon name="alert" :size="15" class="{{ $stats['terlambat'] > 0 ? 'text-rust' : 'text-ink-mute' }}" />
                <span class="font-mono text-[0.6875rem] uppercase tracking-label {{ $stats['terlambat'] > 0 ? 'text-rust-deep' : 'text-ink-soft' }}">
                    <strong>{{ $stats['terlambat'] }}</strong> terlambat
                </span>
            </div>
        </div>

        <div class="mb-5 flex flex-wrap gap-0 border border-line">
            @foreach ($statuses as $value => $label)
                <a
                    href="{{ route('admin.loans.index', array_filter(['status' => $value])) }}"
                    class="border-e border-line px-4 py-2 font-mono text-[0.6875rem] uppercase tracking-label transition-colors last:border-e-0 {{ $currentStatus === $value ? 'bg-ink text-paper' : 'bg-paper text-ink-soft hover:bg-paper-deep' }}"
                >{{ $label }}</a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-paper-deep/60">
                            <th class="label px-5 py-3 text-start">Buku</th>
                            <th class="label px-5 py-3 text-start">Peminjam</th>
                            <th class="label hidden px-5 py-3 text-start sm:table-cell">Pinjam</th>
                            <th class="label px-5 py-3 text-start">Jatuh Tempo</th>
                            <th class="label px-5 py-3 text-start">Status</th>
                            <th class="label px-5 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($loans as $loan)
                            <tr class="transition-colors hover:bg-paper-deep/40">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('catalog.show', $loan->book) }}" class="font-medium text-ink transition-colors hover:text-moss">{{ $loan->book->title }}</a>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-ink">{{ $loan->user->name }}</p>
                                    <p class="mt-0.5 font-mono text-[0.6875rem] text-ink-mute">{{ $loan->user->member_code }}</p>
                                </td>
                                <td class="hidden px-5 py-3.5 font-mono text-[0.8125rem] text-ink-soft sm:table-cell">{{ $loan->borrowed_at->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3.5 font-mono text-[0.8125rem] {{ $loan->isOverdue() ? 'font-medium text-rust-deep' : 'text-ink-soft' }}">
                                    {{ $loan->due_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($loan->status === \App\Models\Loan::STATUS_DIPINJAM)
                                        <span class="inline-flex items-center gap-1 border px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label {{ $loan->isOverdue() ? 'border-rust/40 text-rust-deep' : 'border-moss/40 text-moss-deep' }}">
                                            <x-icon name="{{ $loan->isOverdue() ? 'alert' : 'book' }}" :size="11" />
                                            {{ $loan->isOverdue() ? 'Terlambat' : 'Dipinjam' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 border border-line px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-soft">
                                            <x-icon name="check" :size="11" />
                                            Kembali{{ $loan->fine_amount > 0 ? ' · Rp '.number_format($loan->fine_amount, 0, ',', '.') : '' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-end">
                                    @if ($loan->status === \App\Models\Loan::STATUS_DIPINJAM)
                                        <form method="POST" action="{{ route('admin.loans.return', $loan) }}"
                                              onsubmit="return confirm('Konfirmasi pengembalian \\'{{ $loan->book->title }}\\' dari {{ $loan->user->name }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-primary !px-3 !py-1.5 !text-[0.6875rem]">
                                                <x-icon name="check" :size="13" />
                                                Kembalikan
                                            </button>
                                        </form>
                                    @else
                                        <span class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <x-icon name="clipboard" :size="24" class="mx-auto text-ink-mute" />
                                    <p class="mt-3 text-sm text-ink-soft">Tidak ada data peminjaman.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $loans->links() }}</div>
    </x-page-container>
</x-app-layout>
