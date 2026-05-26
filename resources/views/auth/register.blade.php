<x-guest-layout>
    <x-slot name="title">Inscription</x-slot>

    <h2 class="text-xl font-bold text-gray-900 mb-1">Créer un compte</h2>
    <p class="text-sm text-gray-500 mb-6">Rejoignez ChronoSphere</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <button type="submit"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg
                       transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Créer mon compte
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:text-indigo-700">Se connecter</a>
    </p>
</x-guest-layout>
