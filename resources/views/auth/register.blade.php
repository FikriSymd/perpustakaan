<x-guest-layout>
    <x-slot name="title">Daftar Anggota</x-slot>

    <div class="mb-8">
        <p class="label">Berkas F-2026-REG</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Pendaftaran Anggota</h1>
        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-ink-soft">
            Lengkapi formulir berikut. Nomor anggota otomatis terbit setelah pendaftaran.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-7">
        @csrf

        <div>
            <x-input-label for="name" :value="'01 — Nama Lengkap'" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="'02 — Email'" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" inputmode="email" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="'03 — Kata Sandi'" />
            <div class="relative">
                <x-text-input
                    id="password"
                    x-bind:type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="pe-11"
                />
                <button
                    type="button"
                    @click="show = ! show"
                    class="absolute bottom-2.5 end-0 flex h-6 w-8 items-center justify-center text-ink-mute transition-colors hover:text-ink"
                    x-bind:aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                >
                    <x-icon name="eye" x-show="! show" :size="18" />
                    <x-icon name="eye-off" x-show="show" x-cloak :size="18" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
            <p class="mt-2 flex items-start gap-1.5 text-[0.75rem] text-ink-mute">
                <x-icon name="info" :size="13" class="mt-0.5 shrink-0" />
                Minimal 8 karakter.
            </p>
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="'04 — Ulangi Kata Sandi'" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="btn-primary w-full">
            <x-icon name="user" :size="16" />
            Terbitkan Kartu
        </button>
    </form>

    <div class="mt-8 border-t border-line pt-5">
        <p class="flex flex-wrap items-baseline gap-x-1.5 text-[0.8125rem] text-ink-soft">
            Sudah punya kartu anggota?
            <a href="{{ route('login') }}" class="link font-medium">Masuk di sini</a>
        </p>
    </div>
</x-guest-layout>
