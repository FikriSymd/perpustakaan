<section>
    <header class="flex items-start gap-3">
        <x-icon name="lock" :size="18" class="mt-0.5 shrink-0 text-brass" />
        <div>
            <h2 class="font-display text-lg text-ink">Ubah Kata Sandi</h2>
            <p class="mt-1 text-[0.8125rem] text-ink-soft">Gunakan kata sandi yang panjang dan acak agar akun tetap aman.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-7 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="'Kata Sandi Saat Ini'" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="'Kata Sandi Baru'" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="'Konfirmasi Kata Sandi'" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4 border-t border-line pt-6">
            <button type="submit" class="btn-primary">
                <x-icon name="check" :size="15" />
                Simpan
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="flex items-center gap-1.5 font-mono text-[0.6875rem] uppercase tracking-label text-moss-deep"
                >
                    <x-icon name="check" :size="13" />
                    Tersimpan
                </p>
            @endif
        </div>
    </form>
</section>
