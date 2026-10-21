<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1B3125">

        <title>{{ $title ?? 'Masuk' }} — {{ config('app.name', 'Perpustakaan') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=fraunces:400,500,600,700|archivo:400,500,600,700|ibm-plex-mono:400,500&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-paper text-ink font-sans antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1fr)]">

            {{-- ================= Left: the archive panel (desktop) ================= --}}
            <aside class="relative hidden flex-col justify-between overflow-hidden bg-moss-deep px-12 py-12 text-paper lg:flex xl:px-16">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.14]"
                     style="background-image: linear-gradient(to right, #F4F0E4 1px, transparent 1px); background-size: 3.25rem 100%;"></div>

                <div class="relative flex items-center gap-3">
                    <x-application-logo :size="30" class="text-brass-bright" />
                    <div class="leading-none">
                        <span class="block font-mono text-[0.6875rem] uppercase tracking-label text-paper/60">Katalog Digital</span>
                        <span class="mt-1 block font-display text-lg text-paper">{{ config('app.name', 'Perpustakaan') }}</span>
                    </div>
                </div>

                <div class="relative max-w-md">
                    <p class="font-mono text-[0.6875rem] uppercase tracking-label text-brass-bright">Ruang Baca &middot; Sejak 2026</p>
                    <h1 class="mt-5 font-display text-[2.6rem] leading-[1.08] text-paper xl:text-5xl">
                        Setiap buku punya<br>nomor panggilnya.
                    </h1>
                    <p class="mt-5 max-w-sm text-[0.9375rem] leading-relaxed text-paper/70">
                        Telusuri koleksi, catat peminjaman, dan lacak jatuh tempo — tersusun rapi dalam satu kartu katalog.
                    </p>

                    <div class="mt-10 border border-paper/20 bg-paper/[0.04] p-5">
                        <div class="flex items-center justify-between border-b border-paper/15 pb-3">
                            <span class="font-mono text-[0.625rem] uppercase tracking-label text-paper/50">Indeks</span>
                            <span class="font-mono text-[0.625rem] uppercase tracking-label text-brass-bright">000—999</span>
                        </div>
                        <dl class="mt-4 space-y-3">
                            @foreach ([['Judul', 'abjad'], ['Penulis', 'surut'], ['Eksemplar', 'stok langsung']] as $i => [$term, $note])
                                <div class="flex items-baseline gap-3">
                                    <span class="font-mono text-[0.625rem] text-paper/40">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <dt class="font-display text-[0.9375rem] text-paper">{{ $term }}</dt>
                                    <span aria-hidden="true" class="mt-1 h-px flex-1 bg-paper/15"></span>
                                    <dd class="font-mono text-[0.625rem] uppercase tracking-label text-paper/50">{{ $note }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>

                <p class="relative font-mono text-[0.625rem] uppercase tracking-label text-paper/40">
                    {{ now()->translatedFormat('d F Y') }} &middot; Layanan Sirkulasi
                </p>
            </aside>

            {{-- ================= Right: the form ================= --}}
            <main class="flex min-h-screen flex-col bg-paper">
                <header class="flex items-center justify-between gap-4 border-b border-line px-5 py-4 sm:px-8 sm:py-5">
                    <a href="{{ route('catalog.index') }}" class="flex items-center gap-2.5">
                        <x-application-logo :size="26" class="text-moss" />
                        <span class="font-display text-base text-ink">{{ config('app.name', 'Perpustakaan') }}</span>
                    </a>
                    <a href="{{ route('catalog.index') }}" class="group inline-flex items-center gap-1.5 font-mono text-[0.625rem] uppercase tracking-label text-ink-mute transition-colors hover:text-ink">
                        <x-icon name="arrow-left" :size="14" class="transition-transform duration-200 group-hover:-translate-x-0.5" />
                        Katalog
                    </a>
                </header>

                <div class="flex flex-1 items-center justify-center px-5 py-10 sm:px-8 sm:py-14">
                    <div class="w-full max-w-[26rem]">
                        {{ $slot }}
                    </div>
                </div>

                <footer class="border-t border-line px-5 py-4 sm:px-8">
                    <p class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Perpustakaan') }} &mdash; Sistem Sirkulasi
                    </p>
                </footer>
            </main>
        </div>
    </body>
</html>
