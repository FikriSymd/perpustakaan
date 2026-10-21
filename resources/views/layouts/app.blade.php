<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#F4F0E4">

        <title>{{ $title ?? config('app.name', 'Perpustakaan') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=fraunces:400,500,600,700|archivo:400,500,600,700|ibm-plex-mono:400,500&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-paper text-ink font-sans antialiased">
        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-line bg-paper">
                    <div class="mx-auto w-full max-w-[80rem] px-5 py-7 sm:px-8 sm:py-9 lg:px-10">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="mt-auto border-t border-line">
                <div class="mx-auto flex w-full max-w-[80rem] flex-col gap-3 px-5 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
                    <div class="flex items-center gap-2.5">
                        <x-application-logo :size="20" class="text-ink-mute" />
                        <span class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">
                            {{ config('app.name', 'Perpustakaan') }}
                        </span>
                    </div>
                    <p class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">
                        Sistem Sirkulasi &middot; {{ now()->translatedFormat('Y') }}
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
