<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="label">Berkas Pribadi</p>
            <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Profil</h1>
        </div>
    </x-slot>

    <x-page-container>
        <div class="grid max-w-3xl gap-6">
            <div class="card p-6 sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="card p-6 sm:p-8">
                @include('profile.partials.update-password-form')
            </div>

            <div class="card border-rust/30 p-6 sm:p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </x-page-container>
</x-app-layout>
