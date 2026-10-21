<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Meja Sirkulasi</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Pinjamkan Buku</h1>
            </div>
            <a href="{{ route('admin.loans.index') }}" class="group inline-flex items-center gap-1.5 self-start font-mono text-[0.625rem] uppercase tracking-label text-ink-mute transition-colors hover:text-ink sm:self-end">
                <x-icon name="arrow-left" :size="14" class="transition-transform duration-200 group-hover:-translate-x-0.5" />
                Daftar Peminjaman
            </a>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        <form method="POST" action="{{ route('admin.loans.store') }}" class="max-w-2xl">
            @csrf

            <div class="card space-y-7 p-6 sm:p-8">
                <div>
                    <x-input-label for="user_id" value="01 — Anggota Peminjam *" />
                    <select id="user_id" name="user_id" class="field-select" required>
                        <option value="">Pilih anggota</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>
                                {{ $member->name }} ({{ $member->member_code }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" />
                </div>

                <div>
                    <x-input-label for="book_id" value="02 — Buku *" />
                    @if ($preselected)
                        <input type="hidden" name="book_id" value="{{ $preselected->id }}">
                        <div class="mt-2 flex items-start gap-3 border border-moss/30 bg-moss/5 px-4 py-3">
                            <x-icon name="book" :size="16" class="mt-0.5 shrink-0 text-moss" />
                            <div>
                                <p class="font-medium text-ink">{{ $preselected->title }}</p>
                                <p class="mt-0.5 text-[0.75rem] text-ink-soft">{{ $preselected->author }} &middot; sisa stok {{ $preselected->stock_available }}</p>
                            </div>
                        </div>
                    @else
                        <select id="book_id" name="book_id" class="field-select" required>
                            <option value="">Pilih buku (hanya yang tersedia)</option>
                            @foreach ($books as $book)
                                <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>
                                    {{ $book->title }} — {{ $book->author }} (sisa {{ $book->stock_available }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <x-input-error :messages="$errors->get('book_id')" />
                </div>

                <div>
                    <x-input-label for="notes" value="03 — Catatan (opsional)" />
                    <textarea id="notes" name="notes" rows="2" class="field-area">{{ old('notes') }}</textarea>
                </div>

                <div class="border-t border-line pt-7">
                    <div class="flex items-start gap-3 bg-paper-deep/60 px-4 py-3">
                        <x-icon name="info" :size="16" class="mt-0.5 shrink-0 text-brass" />
                        <div class="space-y-1 text-[0.8125rem] text-ink-soft">
                            <p>Lama pinjam <strong class="text-ink">{{ $loanDays }} hari</strong> &middot; batas <strong class="text-ink">3 buku</strong> per anggota &middot; denda <strong class="text-ink">Rp 1.000/hari</strong>.</p>
                            <p class="text-[0.75rem] text-ink-mute">Jatuh tempo dihitung otomatis dari hari ini.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" :size="15" />
                    Catat Peminjaman
                </button>
                <a href="{{ route('admin.loans.index') }}" class="btn-ghost">Batal</a>
            </div>
        </form>
    </x-page-container>
</x-app-layout>
