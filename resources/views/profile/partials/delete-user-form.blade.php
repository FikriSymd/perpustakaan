<section class="space-y-6">
    <header class="flex items-start gap-3">
        <x-icon name="trash" :size="18" class="mt-0.5 shrink-0 text-rust" />
        <div>
            <h2 class="font-display text-lg text-rust-deep">Hapus Akun</h2>
            <p class="mt-1 text-[0.8125rem] text-ink-soft">
                Setelah akun dihapus, seluruh sumber daya dan datanya akan terhapus permanen. Unduh data yang ingin Anda simpan terlebih dahulu.
            </p>
        </div>
    </header>

    <button
        type="button"
        class="btn-danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <x-icon name="trash" :size="15" />
        Hapus Akun
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <div class="flex items-start gap-3">
                <x-icon name="alert" :size="20" class="mt-0.5 shrink-0 text-rust" />
                <div>
                    <h2 class="font-display text-lg text-ink">Yakin ingin menghapus akun?</h2>
                    <p class="mt-1 text-[0.8125rem] text-ink-soft">
                        Seluruh data akan terhapus permanen. Masukkan kata sandi Anda untuk mengonfirmasi.
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="password" value="Kata Sandi" class="sr-only" />
                <x-text-input id="password" name="password" type="password" placeholder="Kata sandi" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="mt-7 flex justify-end gap-3 border-t border-line pt-6">
                <button type="button" class="btn-ghost" x-on:click="$dispatch('close')">
                    Batal
                </button>
                <button type="submit" class="btn-danger">
                    <x-icon name="trash" :size="15" />
                    Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
