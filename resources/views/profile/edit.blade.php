<x-app-layout>
    <x-slot name="title">Mon profil</x-slot>

    <div class="max-w-3xl space-y-6">

        {{-- Profile info --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Informations personnelles</h3>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Password --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-5">Changer le mot de passe</h3>
            @include('profile.partials.update-password-form')
        </div>

        {{-- Delete --}}
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-red-700 mb-5">Zone de danger</h3>
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
