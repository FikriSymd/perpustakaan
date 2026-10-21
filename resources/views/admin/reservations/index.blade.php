<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Antrean Reservasi</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Reservasi</h1>
            </div>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        <div class="mb-5 flex flex-wrap gap-3">
            <div class="flex items-center gap-2 border border-moss/40 bg-moss/5 px-3 py-2">
                <x-icon name="bookmark" :size="15" class="text-moss-deep" />
                <span class="font-mono text-[0.6875rem] uppercase tracking-label text-moss-deep">
                    <strong>{{ $counts['tersedia'] }}</strong> siap diambil
                </span>
            </div>
            <div class="flex items-center gap-2 border border-line bg-paper-soft px-3 py-2">
                <x-icon name="clock" :size="15" class="text-ink-mute" />
                <span class="font-mono text-[0.6875rem] uppercase tracking-label text-ink-soft">
                    <strong>{{ $counts['menunggu'] }}</strong> menunggu
                </span>
            </div>
        </div>

        <div class="mb-5 flex flex-wrap gap-0 border border-line">
            @foreach ([
                '' => 'Aktif',
                'tersedia' => 'Siap Diambil',
                'menunggu' => 'Menunggu',
                'diambil' => 'Diambil',
                'dibatalkan' => 'Dibatalkan',
                'kadaluarsa' => 'Kadaluarsa',
            ] as $value => $label)
                <a
                    href="{{ route('admin.reservations.index', array_filter(['status' => $value])) }}"
                    class="border-e border-line px-4 py-2 font-mono text-[0.6875rem] uppercase tracking-label transition-colors last:border-e-0 {{ $filter === $value ? 'bg-ink text-paper' : 'bg-paper text-ink-soft hover:bg-paper-deep' }}"
                >{{ $label }}</a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-paper-deep/60">
                            <th class="label px-5 py-3 text-start">Buku</th>
                            <th class="label px-5 py-3 text-start">Pemesan</th>
                            <th class="label hidden px-5 py-3 text-start sm:table-cell">Dibuat</th>
                            <th class="label px-5 py-3 text-start">Status</th>
                            <th class="label px-5 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($reservations as $reservation)
                            @php
                                $holdLeft = $reservation->holdDaysLeft();
                            @endphp
                            <tr class="transition-colors hover:bg-paper-deep/40">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('catalog.show', $reservation->book) }}" class="font-medium text-ink transition-colors hover:text-moss">{{ $reservation->book->title }}</a>
                                    <p class="mt-0.5 font-mono text-[0.6875rem] text-ink-mute">{{ $reservation->book->category?->name }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-ink">{{ $reservation->user->name }}</p>
                                    <p class="mt-0.5 font-mono text-[0.6875rem] text-ink-mute">{{ $reservation->user->member_code }}</p>
                                </td>
                                <td class="hidden px-5 py-3.5 font-mono text-[0.8125rem] text-ink-soft sm:table-cell">
                                    {{ $reservation->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($reservation->isReady())
                                        <span class="inline-flex items-center gap-1 border border-moss/40 bg-moss/5 px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-moss-deep">
                                            <x-icon name="check" :size="11" />
                                            Siap diambil
                                            @if ($holdLeft !== null)
                                                · {{ $holdLeft }} hr
                                            @endif
                                        </span>
                                    @elseif ($reservation->isWaiting())
                                        <span class="inline-flex items-center gap-1 border border-line bg-paper-soft px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-soft">
                                            <x-icon name="clock" :size="11" />
                                            Antrean #{{ $reservation->queue_position ?? '—' }}
                                        </span>
                                    @elseif ($reservation->status === \App\Models\Reservation::STATUS_DIAMBIL)
                                        <span class="inline-flex items-center gap-1 border border-line px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-soft">
                                            <x-icon name="book" :size="11" />
                                            Sudah diambil
                                        </span>
                                    @elseif ($reservation->status === \App\Models\Reservation::STATUS_KADALUARSA)
                                        <span class="inline-flex items-center gap-1 border border-rust/40 px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-rust-deep">
                                            <x-icon name="alert" :size="11" />
                                            Kadaluarsa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 border border-line px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">
                                            <x-icon name="close" :size="11" />
                                            Dibatalkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-end">
                                    @if ($reservation->isReady())
                                        <form method="POST" action="{{ route('admin.reservations.fulfill', $reservation) }}"
                                              onsubmit="return confirm('Proses pengambilan \\'{{ $reservation->book->title }}\\' oleh {{ $reservation->user->name }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-primary !px-3 !py-1.5 !text-[0.6875rem]">
                                                <x-icon name="book" :size="13" />
                                                Proses Ambil
                                            </button>
                                        </form>
                                    @endif
                                    @if ($reservation->isOpen())
                                        <form method="POST" action="{{ route('admin.reservations.destroy', $reservation) }}"
                                              onsubmit="return confirm('Batalkan reservasi {{ $reservation->user->name }} untuk \\'{{ $reservation->book->title }}\\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost !px-3 !py-1.5 !text-[0.6875rem] text-rust-deep hover:!border-rust/40">
                                                <x-icon name="close" :size="13" />
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                    @if (! $reservation->isOpen())
                                        <span class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <x-icon name="bookmark" :size="24" class="mx-auto text-ink-mute" />
                                    <p class="mt-3 text-sm text-ink-soft">Tidak ada reservasi pada filter ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reservations->links() }}</div>
    </x-page-container>
</x-app-layout>
