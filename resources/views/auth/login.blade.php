<x-guest-layout>
    <x-slot name="title">Connexion</x-slot>

    <h2 class="text-xl font-bold text-gray-900 mb-1">Connexion</h2>
    <p class="text-sm text-gray-500 mb-6">Accédez à votre espace ChronoSphere</p>

    {{-- Session status --}}
    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Se souvenir de moi
            </label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button type="submit"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg
                       transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Se connecter
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:text-indigo-700">S'inscrire</a>
    </p>

    {{-- Dev credentials --}}
    @if(app()->environment('local'))
    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
        <strong>Démo :</strong> admin@chronosphere.local / password
    </div>
    @endif
</x-guest-layout>
