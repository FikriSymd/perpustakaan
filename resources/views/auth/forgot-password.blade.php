<x-guest-layout>
    <x-slot name="title">Lupa Kata Sandi</x-slot>

    <div class="mb-8">
        <p class="label">Pemulihan Akses</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Lupa Kata Sandi</h1>
        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-ink-soft">
            Masukkan email terdaftar — tautan pemulihan akan dikirim.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-7">
        @csrf

        <div>
            <x-input-label for="email" :value="'01 — Email'" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <button type="submit" class="btn-primary w-full">
            <x-icon name="mail" :size="16" />
            Kirim Tautan Pemulihan
        </button>
    </form>

    <div class="mt-8 border-t border-line pt-5">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-[0.8125rem] text-ink-soft transition-colors hover:text-ink">
            <x-icon name="arrow-left" :size="14" />
            Kembali ke halaman masuk
        </a>
    </div>
</x-guest-layout>
