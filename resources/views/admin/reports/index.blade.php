<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="label">Rekap Sirkulasi &amp; Koleksi</p>
            <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Laporan</h1>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        {{-- Ringkasan angka --}}
        <div class="mb-8 grid grid-cols-2 gap-px border border-line bg-line sm:grid-cols-4">
            @foreach ([
                ['icon' => 'library', 'label' => 'Judul Koleksi', 'value' => $stats['total_books']],
                ['icon' => 'book', 'label' => 'Eksemplar Tersedia', 'value' => $stats['available_copies'].' / '.$stats['total_copies']],
                ['icon' => 'return', 'label' => 'Sedang Dipinjam', 'value' => $stats['active_loans']],
                ['icon' => 'alert', 'label' => 'Terlambat', 'value' => $stats['overdue_loans'], 'warn' => $stats['overdue_loans'] > 0],
                ['icon' => 'bookmark', 'label' => 'Reservasi Aktif', 'value' => $stats['open_reservations']],
                ['icon' => 'check', 'label' => 'Siap Diambil', 'value' => $stats['ready_reservations']],
                ['icon' => 'users', 'label' => 'Anggota', 'value' => $stats['members']],
            ] as $stat)
                <div class="bg-paper p-4 sm:p-5">
                    <x-icon name="{{ $stat['icon'] }}" :size="16" class="{{ ($stat['warn'] ?? false) ? 'text-rust' : 'text-ink-mute' }}" />
                    <p class="mt-3 font-display text-2xl text-ink">{{ $stat['value'] }}</p>
                    <p class="label mt-1">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Form ekspor --}}
        <div class="card p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-2">
                <x-icon name="download" :size="16" class="text-ink-mute" />
                <h2 class="font-display text-xl text-ink">Ekspor CSV</h2>
            </div>

            <form method="GET" action="{{ route('admin.reports.export') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="jenis" class="label mb-1.5 block">Jenis Laporan</label>
                    <select id="jenis" name="jenis" class="field-select w-full">
                        <option value="peminjaman">Peminjaman</option>
                        <option value="buku">Koleksi Buku</option>
                        <option value="reservasi">Reservasi</option>
                    </select>
                </div>
                <div>
                    <label for="dari" class="label mb-1.5 block">Dari Tanggal</label>
                    <input type="date" id="dari" name="dari" class="field w-full">
                    <p class="mt-1 font-mono text-[0.625rem] text-ink-mute">opsional — kosongkan untuk semua</p>
                </div>
                <div>
                    <label for="sampai" class="label mb-1.5 block">Sampai Tanggal</label>
                    <input type="date" id="sampai" name="sampai" class="field w-full">
                </div>
                <div>
                    <label for="status" class="label mb-1.5 block">Status</label>
                    <select id="status" name="status" class="field-select w-full">
                        <option value="">Semua status</option>
                        @foreach (['dipinjam' => 'Dipinjam', 'dikembalikan' => 'Dikembalikan', 'menunggu' => 'Menunggu', 'tersedia' => 'Siap Diambil', 'diambil' => 'Diambil', 'dibatalkan' => 'Dibatalkan', 'kadaluarsa' => 'Kadaluarsa'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <button type="submit" class="btn-primary">
                        <x-icon name="download" :size="15" />
                        Unduh CSV
                    </button>
                    <p class="mt-3 font-mono text-[0.6875rem] leading-relaxed text-ink-mute">
                        Filter tanggal berlaku untuk peminjaman &amp; reservasi. Filter status menyesuaikan jenis laporan yang dipilih.
                    </p>
                </div>
            </form>
        </div>

        {{-- Info pengingat otomatis --}}
        <div class="card mt-6 p-5 sm:p-6">
            <div class="mb-3 flex items-center gap-2">
                <x-icon name="mail" :size="16" class="text-ink-mute" />
                <h2 class="font-display text-xl text-ink">Pengingat Terlambat</h2>
            </div>
            <p class="text-sm leading-relaxed text-ink-soft">
                Email pengingat dikirim otomatis setiap hari pukul 07:00 kepada anggota yang melewati jatuh tempo,
                sekaligus mengaktifkan antrean reservasi dan mengkadaluarsakan tahanan buku yang lewat batas.
                Untuk menguji atau menjalankan manual:
            </p>
            <code class="mt-3 block border border-line bg-paper-deep/60 px-3 py-2 font-mono text-[0.75rem] text-ink-soft">php artisan library:notify-overdue --dry-run</code>
        </div>
    </x-page-container>
</x-app-layout>
