<section>
    <header class="flex items-start gap-3">
        <x-icon name="user" :size="18" class="mt-0.5 shrink-0 text-brass" />
        <div>
            <h2 class="font-display text-lg text-ink">Informasi Profil</h2>
            <p class="mt-1 text-[0.8125rem] text-ink-soft">Perbarui nama dan alamat email akun Anda.</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-7 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="'Nama'" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 flex items-start gap-2 border border-brass/30 bg-brass-pale/40 px-3 py-2.5">
                    <x-icon name="alert" :size="15" class="mt-0.5 shrink-0 text-brass" />
                    <div class="text-[0.8125rem] text-ink-soft">
                        <p>Alamat email Anda belum diverifikasi.</p>
                        <button form="send-verification" class="link mt-1 font-mono text-[0.625rem] uppercase tracking-label">
                            Kirim ulang tautan verifikasi
                        </button>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 flex items-center gap-1.5 text-moss-deep">
                                <x-icon name="check" :size="13" />
                                Tautan verifikasi baru telah dikirim.
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 border-t border-line pt-6">
            <button type="submit" class="btn-primary">
                <x-icon name="check" :size="15" />
                Simpan
            </button>

            @if (session('status') === 'profile-updated')
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
