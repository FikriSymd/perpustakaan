<x-guest-layout>
    <x-slot name="title">Masuk</x-slot>

    @php $attempt = 'F-2026-LOGIN' @endphp

    <div class="mb-8">
        <p class="label">Berkas {{ $attempt }}</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Masuk</h1>
        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-ink-soft">
            Pakai nomor anggota atau email terdaftar untuk membuka dashboard sirkulasi.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-7">
        @csrf

        <div>
            <div class="flex items-baseline justify-between gap-2">
                <x-input-label for="email" :value="'01 — Email / No. Anggota'" />
            </div>
            <x-text-input id="email" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" inputmode="email" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="'02 — Kata Sandi'" />
            <div class="relative">
                <x-text-input
                    id="password"
                    x-bind:type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
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
        </div>

        <div class="flex flex-wrap items-center justify-between gap-y-4">
            <label for="remember_me" class="group inline-flex cursor-pointer items-center gap-2.5">
                <input id="remember_me" type="checkbox" name="remember"
                       class="h-4 w-4 cursor-pointer appearance-none border border-ink-mute bg-paper-soft
                              transition-colors checked:border-moss checked:bg-moss
                              focus-visible:outline-2 focus-visible:outline-brass"
                       style="background-image: none;">
                <span class="text-[0.8125rem] text-ink-soft group-hover:text-ink">Simpan sesi</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link text-[0.8125rem]">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full">
            <x-icon name="key" :size="16" />
            Buka Sesi
        </button>
    </form>

    <div class="mt-8 border-t border-line pt-5">
        <p class="flex flex-wrap items-baseline gap-x-1.5 text-[0.8125rem] text-ink-soft">
            Belum punya kartu anggota?
            <a href="{{ route('register') }}" class="link font-medium">Daftar sekarang</a>
        </p>
    </div>
</x-guest-layout>
