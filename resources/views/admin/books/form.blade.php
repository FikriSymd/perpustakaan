@php
    $isEdit = isset($book);
    $action = $isEdit ? route('admin.books.update', $book) : route('admin.books.store');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Registrasi Koleksi</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">{{ $isEdit ? 'Edit Buku' : 'Tambah Buku Baru' }}</h1>
            </div>
            <a href="{{ route('admin.books.index') }}" class="group inline-flex items-center gap-1.5 self-start font-mono text-[0.625rem] uppercase tracking-label text-ink-mute transition-colors hover:text-ink sm:self-end">
                <x-icon name="arrow-left" :size="14" class="transition-transform duration-200 group-hover:-translate-x-0.5" />
                Daftar Buku
            </a>
        </div>
    </x-slot>

    <x-page-container>
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="max-w-3xl">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="card space-y-7 p-6 sm:p-8">
                <div>
                    <x-input-label for="title" :value="'01 — Judul Buku *'" />
                    <x-text-input id="title" name="title" type="text" :value="old('title', $book->title ?? '')" required autofocus />
                    <x-input-error :messages="$errors->get('title')" />
                </div>

                <div class="grid gap-7 sm:grid-cols-2">
                    <div>
                        <x-input-label for="author" :value="'02 — Penulis *'" />
                        <x-text-input id="author" name="author" type="text" :value="old('author', $book->author ?? '')" required />
                        <x-input-error :messages="$errors->get('author')" />
                    </div>
                    <div>
                        <x-input-label for="category_id" :value="'03 — Kategori *'" />
                        <select id="category_id" name="category_id" class="field-select" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $book->category_id ?? '') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" />
                    </div>
                </div>

                <div class="grid gap-7 sm:grid-cols-3">
                    <div>
                        <x-input-label for="publisher" :value="'04 — Penerbit'" />
                        <x-text-input id="publisher" name="publisher" type="text" :value="old('publisher', $book->publisher ?? '')" />
                        <x-input-error :messages="$errors->get('publisher')" />
                    </div>
                    <div>
                        <x-input-label for="publication_year" :value="'05 — Tahun Terbit'" />
                        <x-text-input id="publication_year" name="publication_year" type="number" min="1800" max="{{ date('Y') }}" :value="old('publication_year', $book->publication_year ?? '')" />
                        <x-input-error :messages="$errors->get('publication_year')" />
                    </div>
                    <div>
                        <x-input-label for="isbn" :value="'06 — ISBN'" />
                        <x-text-input id="isbn" name="isbn" type="text" :value="old('isbn', $book->isbn ?? '')" />
                        <x-input-error :messages="$errors->get('isbn')" />
                    </div>
                </div>

                <div class="border-t border-line pt-7">
                    <x-input-label for="stock_total" :value="'07 — Jumlah Eksemplar *'" />
                    <x-text-input id="stock_total" name="stock_total" type="number" min="0" max="10000" class="w-40" :value="old('stock_total', $book->stock_total ?? 1)" required />
                    @if ($isEdit)
                        <p class="mt-2 flex items-center gap-1.5 text-[0.75rem] text-ink-mute">
                            <x-icon name="info" :size="13" class="shrink-0" />
                            Tersedia saat ini: {{ $book->stock_available }} &middot; sedang dipinjam: {{ $book->active_loans_count ?? $book->activeLoans()->count() }}
                        </p>
                    @endif
                    <x-input-error :messages="$errors->get('stock_total')" />
                </div>

                <div class="border-t border-line pt-7">
                    <x-input-label for="cover" :value="'08 — Sampul (jpg/png/webp, maks 2MB)'" />
                    <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" class="field-file mt-2" />
                    <x-input-error :messages="$errors->get('cover')" />
                    @if ($isEdit && $book->cover_path)
                        <img src="{{ Storage::url($book->cover_path) }}" alt="Sampul saat ini" class="mt-3 w-24 border border-line">
                    @endif
                </div>

                <div class="border-t border-line pt-7">
                    <x-input-label for="description" :value="'09 — Sinopsis'" />
                    <textarea id="description" name="description" rows="5" class="field-area mt-2">{{ old('description', $book->description ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" :size="15" />
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Buku' }}
                </button>
                <a href="{{ route('admin.books.index') }}" class="btn-ghost">Batal</a>
            </div>
        </form>
    </x-page-container>
</x-app-layout>
