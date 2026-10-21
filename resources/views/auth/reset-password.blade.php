<x-guest-layout>
    <x-slot name="title">Atur Ulang Kata Sandi</x-slot>

    <div class="mb-8">
        <p class="label">Pemulihan Akses</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Atur Ulang Kata Sandi</h1>
        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-ink-soft">
            Tentukan kata sandi baru untuk akun kamu.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-7">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="'01 — Email'" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="'02 — Kata Sandi Baru'" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="'03 — Ulangi Kata Sandi'" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="btn-primary w-full">
            <x-icon name="lock" :size="16" />
            Simpan Kata Sandi
        </button>
    </form>
</x-guest-layout>
