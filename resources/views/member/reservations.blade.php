<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="label">Berkas Pribadi</p>
            <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Reservasi Saya</h1>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-paper-deep/60">
                            <th class="label px-5 py-3 text-start">Buku</th>
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
                                    <p class="mt-0.5 font-mono text-[0.6875rem] text-ink-mute">{{ $reservation->book->author }}</p>
                                </td>
                                <td class="hidden px-5 py-3.5 font-mono text-[0.8125rem] text-ink-soft sm:table-cell">
                                    {{ $reservation->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($reservation->isReady())
                                        <span class="inline-flex items-center gap-1 border border-moss/40 bg-moss/5 px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-moss-deep">
                                            <x-icon name="check" :size="11" />
                                            Siap diambil
                                        </span>
                                        <p class="mt-1 font-mono text-[0.6875rem] text-ink-soft">
                                            Ambil sebelum {{ $reservation->expires_at?->translatedFormat('d M Y') }}
                                            @if ($holdLeft !== null) ({{ $holdLeft }} hari lagi) @endif
                                        </p>
                                    @elseif ($reservation->isWaiting())
                                        <span class="inline-flex items-center gap-1 border border-line bg-paper-soft px-2 py-0.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-soft">
                                            <x-icon name="clock" :size="11" />
                                            Antrean #{{ $reservation->queue_position ?? '—' }}
                                        </span>
                                        <p class="mt-1 font-mono text-[0.6875rem] text-ink-soft">Diberitahu lewat email saat siap.</p>
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
                                    @if ($reservation->isOpen())
                                        <form method="POST" action="{{ route('reservations.destroy', $reservation) }}"
                                              onsubmit="return confirm('Batalkan reservasi \\'{{ $reservation->book->title }}\\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost !px-3 !py-1.5 !text-[0.6875rem] text-rust-deep hover:!border-rust/40">
                                                <x-icon name="close" :size="13" />
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <x-icon name="bookmark" :size="24" class="mx-auto text-ink-mute" />
                                    <p class="mt-3 text-sm text-ink-soft">Anda belum punya reservasi.</p>
                                    <a href="{{ route('catalog.index') }}" class="btn-ghost mt-4">
                                        <x-icon name="search" :size="14" />
                                        Jelajahi Katalog
                                    </a>
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
