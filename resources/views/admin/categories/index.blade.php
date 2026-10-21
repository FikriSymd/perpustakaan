<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="label">Skema Klasifikasi</p>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Kategori Buku</h1>
            </div>
        </div>
    </x-slot>

    <x-page-container>
        <x-flash />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="card overflow-hidden lg:col-span-2">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-line bg-paper-deep/60">
                                <th class="label px-5 py-3 text-start">Nama</th>
                                <th class="label px-5 py-3 text-start">Jumlah Buku</th>
                                <th class="label px-5 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($categories as $category)
                                <tr class="transition-colors hover:bg-paper-deep/40">
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-ink">{{ $category->name }}</p>
                                        @if ($category->description)
                                            <p class="mt-0.5 line-clamp-1 text-[0.75rem] text-ink-mute">{{ $category->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-[0.8125rem] text-ink-soft">{{ $category->books_count }}</td>
                                    <td class="px-5 py-3.5 text-end">
                                        <div class="inline-flex items-center gap-1 whitespace-nowrap">
                                            <button
                                                type="button"
                                                x-data
                                                @click="$dispatch('edit-category', @js(['id' => $category->id, 'name' => $category->name, 'description' => $category->description]))"
                                                class="inline-flex h-8 w-8 items-center justify-center border border-line text-ink-soft transition-colors hover:border-brass hover:text-brass"
                                                title="Edit"
                                            >
                                                <x-icon name="pencil" :size="15" />
                                                <span class="sr-only">Edit {{ $category->name }}</span>
                                            </button>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline"
                                                  onsubmit="return confirm('Hapus kategori \\'{{ $category->name }}\\'?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex h-8 w-8 items-center justify-center border border-line text-ink-soft transition-colors hover:border-rust hover:text-rust"
                                                        title="Hapus">
                                                    <x-icon name="trash" :size="15" />
                                                    <span class="sr-only">Hapus {{ $category->name }}</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center">
                                        <x-icon name="tag" :size="24" class="mx-auto text-ink-mute" />
                                        <p class="mt-3 text-sm text-ink-soft">Belum ada kategori.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-line px-5 py-4">{{ $categories->links() }}</div>
            </div>

            <div
                x-data="{
                    editing: null,
                    listen(e) { this.editing = e.detail; this.$refs.name.value = e.detail.name; this.$refs.desc.value = e.detail.description ?? ''; this.$refs.name.focus(); },
                    reset() { this.editing = null; this.$root.querySelector('form').reset(); }
                }"
                @edit-category.window="listen($event)"
                class="card h-fit p-6"
                x-cloak
            >
                <div class="mb-5 flex items-center gap-2">
                    <x-icon name="tag" :size="16" class="text-brass" />
                    <h2 class="font-display text-lg text-ink" x-text="editing ? 'Edit Kategori' : 'Tambah Kategori'">Tambah Kategori</h2>
                </div>
                <form method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <x-input-label for="name" value="Nama Kategori *" />
                        <x-text-input id="name" name="name" type="text" x-ref="name" :value="old('name')" required maxlength="100" />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea id="description" name="description" rows="3" x-ref="desc" class="field-area">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" />
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn-primary" x-text="editing ? 'Simpan' : 'Tambah'">Tambah</button>
                        <button type="button" x-show="editing" @click="reset()" class="btn-ghost">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
