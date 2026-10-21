<x-guest-layout>
    <x-slot name="title">Verifikasi Email</x-slot>

    <div class="mb-8">
        <p class="label">Verifikasi Alamat</p>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Verifikasi Email</h1>
        <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-ink-soft">
            Cek kotak masuk dan klik tautan verifikasi yang baru dikirim.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status :status="'Tautan verifikasi baru telah dikirim.'" />
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <button type="submit" class="btn-primary w-full sm:w-auto">
                <x-icon name="mail" :size="16" />
                Kirim Ulang Tautan
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost w-full sm:w-auto">
                <x-icon name="logout" :size="16" />
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
