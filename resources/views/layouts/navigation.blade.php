{{-- Top rule bar + full-bleed mobile drawer. Icons only, no emoji. --}}
@php
    $links = [
        ['route' => 'catalog.index', 'icon' => 'library', 'label' => 'Katalog', 'active' => request()->routeIs('catalog.*')],
    ];

    if (auth()->check()) {
        $links[] = ['route' => 'dashboard', 'icon' => 'grid', 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard')];
        $links[] = ['route' => 'reservations.index', 'icon' => 'bookmark', 'label' => 'Reservasi', 'active' => request()->routeIs('reservations.*')];

        if (auth()->user()->isAdmin()) {
            $links[] = ['route' => 'admin.books.index', 'icon' => 'book', 'label' => 'Buku', 'active' => request()->routeIs('admin.books.*')];
            $links[] = ['route' => 'admin.categories.index', 'icon' => 'tag', 'label' => 'Kategori', 'active' => request()->routeIs('admin.categories.*')];
            $links[] = ['route' => 'admin.loans.index', 'icon' => 'return', 'label' => 'Peminjaman', 'active' => request()->routeIs('admin.loans.*')];
            $links[] = ['route' => 'admin.reservations.index', 'icon' => 'clipboard', 'label' => 'Antrean', 'active' => request()->routeIs('admin.reservations.*')];
            $links[] = ['route' => 'admin.reports.index', 'icon' => 'trending', 'label' => 'Laporan', 'active' => request()->routeIs('admin.reports.*')];
        }
    }

    $user = auth()->user();
    $initials = $user
        ? collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('')
        : '';
@endphp

<nav x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-line bg-paper/95 backdrop-blur-sm">
    {{-- Brass hairline cap — the "archive stamp" detail. --}}
    <div aria-hidden="true" class="h-1 w-full bg-gradient-to-r from-moss via-brass to-rust"></div>

    <div class="mx-auto flex w-full max-w-[80rem] items-center justify-between gap-3 px-5 sm:px-8 lg:px-10">
        {{-- Wordmark --}}
        <a href="{{ route('catalog.index') }}" class="flex shrink-0 items-center gap-2.5 py-3.5">
            <x-application-logo :size="28" class="text-moss" />
            <span class="flex flex-col leading-none">
                <span class="font-display text-[1.0625rem] font-medium text-ink">{{ config('app.name', 'Perpustakaan') }}</span>
                <span class="mt-0.5 font-mono text-[0.5625rem] uppercase tracking-label text-ink-mute">Katalog Digital</span>
            </span>
        </a>

        {{-- Desktop links --}}
        <div class="hidden items-stretch self-stretch xl:flex">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    @class([
                        'group relative flex items-center gap-1.5 border-b-2 px-2.5 font-mono text-[0.6875rem] uppercase tracking-label transition-colors duration-150 2xl:px-3.5',
                        'border-brass text-ink' => $link['active'],
                        'border-transparent text-ink-mute hover:border-line-dark hover:text-ink' => ! $link['active'],
                    ])
                    @if ($link['active']) aria-current="page" @endif
                >
                    <x-icon :name="$link['icon']" :size="15" />
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Desktop account --}}
        <div class="hidden items-center gap-3 xl:flex">
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 font-mono text-[0.6875rem] uppercase tracking-label text-ink-soft transition-colors hover:text-ink">
                    <x-icon name="key" :size="15" />
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="btn-primary btn-sm">
                    <x-icon name="user" :size="14" />
                    Daftar
                </a>
            @else
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button type="button" class="group flex items-center gap-2.5 border border-line bg-paper-soft py-1.5 pe-2.5 ps-1.5 transition-colors hover:border-ink">
                            <span class="flex h-7 w-7 items-center justify-center bg-moss font-mono text-[0.6875rem] font-medium text-paper-soft">{{ $initials }}</span>
                            <span class="flex flex-col items-start leading-none">
                                <span class="max-w-[7rem] truncate text-[0.8125rem] font-medium text-ink">{{ $user->name }}</span>
                                <span class="mt-0.5 font-mono text-[0.5625rem] uppercase tracking-label text-ink-mute">{{ $user->isAdmin() ? 'Petugas' : 'Anggota' }}</span>
                            </span>
                            <x-icon name="chevron-down" :size="14" class="text-ink-mute transition-transform duration-200 group-aria-expanded:rotate-180" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-line px-4 py-3">
                            <p class="label">Nomor Anggota</p>
                            <p class="mt-1 font-mono text-[0.8125rem] text-ink">{{ $user->member_code ?: '—' }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            <x-icon name="user" :size="15" />
                            Profil
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                <x-icon name="logout" :size="15" />
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @endguest
        </div>

        {{-- Mobile trigger --}}
        <button
            type="button"
            @click="open = ! open"
            :aria-expanded="open ? 'true' : 'false'"
            aria-controls="mobile-menu"
            class="flex h-10 w-10 items-center justify-center border border-line text-ink transition-colors hover:border-ink xl:hidden"
        >
            <span class="sr-only">Buka menu navigasi</span>
            <x-icon name="menu" x-show="! open" :size="20" />
            <x-icon name="close" x-show="open" x-cloak :size="20" />
        </button>
    </div>

    {{-- Mobile drawer --}}
    <div
        id="mobile-menu"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out-quart duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="border-t border-line bg-paper xl:hidden"
    >
        <div class="mx-auto w-full max-w-[80rem] px-5 py-4 sm:px-8">
            <p class="label mb-3">Navigasi</p>
            <div class="flex flex-col border border-line">
                @foreach ($links as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        @class([
                            'flex items-center gap-3 border-b border-line px-4 py-3.5 font-mono text-[0.6875rem] uppercase tracking-label transition-colors last:border-b-0',
                            'bg-moss text-paper-soft' => $link['active'],
                            'text-ink-soft hover:bg-paper-deep hover:text-ink' => ! $link['active'],
                        ])
                        @if ($link['active']) aria-current="page" @endif
                    >
                        <x-icon :name="$link['icon']" :size="16" />
                        {{ $link['label'] }}
                        <x-icon name="chevron-right" :size="15" class="ms-auto opacity-50" />
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                @guest
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="btn-ghost">
                            <x-icon name="key" :size="15" />
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary">
                            <x-icon name="user" :size="15" />
                            Daftar
                        </a>
                    </div>
                @else
                    <div class="flex items-center gap-3 border border-line bg-paper-soft p-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-moss font-mono text-sm text-paper-soft">{{ $initials }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ $user->name }}</p>
                            <p class="font-mono text-[0.625rem] uppercase tracking-label text-ink-mute">
                                {{ $user->isAdmin() ? 'Petugas' : 'Anggota' }} &middot; {{ $user->member_code ?: '—' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn-ghost">
                            <x-icon name="user" :size="15" />
                            Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-danger w-full">
                                <x-icon name="logout" :size="15" />
                                Keluar
                            </button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</nav>
