<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Registrasi Koleksi</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Kelola Buku</h1>
            </div>
            <a href="{{ route('admin.books.create') }}" class="btn-primary self-start sm:self-end">
                <x-icon name="plus" :size="15" />
                Tambah Buku
            </a>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        <div class="card overflow-hidden">
            <div class="border-b border-line px-5 py-4">
                <form method="GET" class="flex max-w-md gap-0">
                    <div class="flex flex-1 items-center border border-line border-e-0 bg-paper-soft px-3">
                        <x-icon name="search" :size="15" class="text-ink-mute" />
                        <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul / penulis" class="field h-10 border-0" autocomplete="off">
                    </div>
                    <button type="submit" class="btn-ghost">Cari</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-paper-deep/60">
                            <th class="label px-5 py-3 text-start">Buku</th>
                            <th class="label hidden px-5 py-3 text-start sm:table-cell">Kategori</th>
                            <th class="label px-5 py-3 text-start">Stok</th>
                            <th class="label hidden px-5 py-3 text-start md:table-cell">Dipinjam</th>
                            <th class="label px-5 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($books as $book)
                            <tr class="transition-colors hover:bg-paper-deep/40">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-ink">{{ $book->title }}</p>
                                    <p class="mt-0.5 text-[0.75rem] text-ink-mute">{{ $book->author }}@if ($book->isbn) &middot; ISBN {{ $book->isbn }}@endif</p>
                                </td>
                                <td class="hidden px-5 py-3.5 text-ink-soft sm:table-cell">{{ $book->category->name }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-[0.8125rem] {{ $book->stock_available === 0 ? 'font-medium text-rust-deep' : 'text-ink' }}">
                                        {{ $book->stock_available }} / {{ $book->stock_total }}
                                    </span>
                                </td>
                                <td class="hidden px-5 py-3.5 font-mono text-[0.8125rem] text-ink-soft md:table-cell">{{ $book->active_loans_count }}</td>
                                <td class="px-5 py-3.5 text-end">
                                    <div class="inline-flex items-center gap-1 whitespace-nowrap">
                                        <a href="{{ route('admin.loans.create', ['book_id' => $book->id]) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center border border-line text-ink-soft transition-colors hover:border-moss hover:text-moss"
                                           title="Pinjamkan">
                                            <x-icon name="return" :size="15" />
                                            <span class="sr-only">Pinjamkan {{ $book->title }}</span>
                                        </a>
                                        <a href="{{ route('admin.books.edit', $book) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center border border-line text-ink-soft transition-colors hover:border-brass hover:text-brass"
                                           title="Edit">
                                            <x-icon name="pencil" :size="15" />
                                            <span class="sr-only">Edit {{ $book->title }}</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.books.destroy', $book) }}" class="inline"
                                              onsubmit="return confirm('Hapus buku \\'{{ $book->title }}\\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center border border-line text-ink-soft transition-colors hover:border-rust hover:text-rust"
                                                    title="Hapus">
                                                <x-icon name="trash" :size="15" />
                                                <span class="sr-only">Hapus {{ $book->title }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <x-icon name="book" :size="24" class="mx-auto text-ink-mute" />
                                    <p class="mt-3 text-sm text-ink-soft">
                                        @if ($search)
                                            Tidak ada buku yang cocok dengan &ldquo;{{ $search }}&rdquo;.
                                        @else
                                            Belum ada buku terdaftar.
                                        @endif
                                    </p>
                                    @unless ($search)
                                        <a href="{{ route('admin.books.create') }}" class="btn-ghost mx-auto mt-4">
                                            <x-icon name="plus" :size="15" />
                                            Tambah Buku Pertama
                                        </a>
                                    @endunless
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $books->links() }}</div>
    </x-page-container>
</x-app-layout>
