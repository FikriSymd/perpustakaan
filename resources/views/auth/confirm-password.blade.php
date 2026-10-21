<x-guest-layout>
    <x-slot name="title">Konfirmasi Kata Sandi</x-slot>

    <div class="mb-8">
        <p class="label">Area Terbatas</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Konfirmasi Kata Sandi</h1>
        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-ink-soft">
            Ini area aman. Konfirmasi kata sandi sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-7">
        @csrf

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="'01 — Kata Sandi'" />
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

        <button type="submit" class="btn-primary w-full">
            <x-icon name="check" :size="16" />
            Konfirmasi
        </button>
    </form>
</x-guest-layout>
