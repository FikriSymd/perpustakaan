@php
    $user = auth()->user();
    $target = match ($user->role) {
        'admin', 'petugas' => 'admin.dashboard',
        default => 'member.dashboard',
    };
@endphp

<x-app-layout>
    <x-page-container>
        <div class="card p-8">
            <p class="label">Berkas Anggota</p>
            <h1 class="mt-2 font-display text-3xl text-ink">Selamat datang, {{ $user->name }}</h1>
            <p class="mt-3 max-w-prose text-sm text-ink-soft">
                Akun Anda terdaftar. Lanjutkan ke meja kerja untuk melihat koleksi dan catatan peminjaman.
            </p>
            <a href="{{ route($target) }}" class="btn-primary mt-6">
                Buka Meja Kerja
                <x-icon name="arrow-right" :size="15" />
            </a>
        </div>
    </x-page-container>
</x-app-layout>
