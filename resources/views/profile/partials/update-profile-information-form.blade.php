<form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span class="text-gray-400">(optionnel)</span></label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

        @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <p class="text-sm mt-2 text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Votre adresse e-mail n'est pas vérifiée.
                <button form="send-verification" class="underline font-medium">Renvoyer le lien</button>
            </p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Langue</label>
            <select id="language" name="language"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="fr" @selected(old('language', $user->language) === 'fr')>Français</option>
                <option value="en" @selected(old('language', $user->language) === 'en')>English</option>
            </select>
        </div>

        <div>
            <label for="theme" class="block text-sm font-medium text-gray-700 mb-1">Thème</label>
            <select id="theme" name="theme"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="light" @selected(old('theme', $user->theme) === 'light')>Clair</option>
                <option value="dark"  @selected(old('theme', $user->theme) === 'dark')>Sombre</option>
                <option value="system" @selected(old('theme', $user->theme) === 'system')>Système</option>
            </select>
        </div>

        <div>
            <label for="default_module" class="block text-sm font-medium text-gray-700 mb-1">Module par défaut</label>
            <select id="default_module" name="default_module"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">— Aucun —</option>
                @foreach(['timetable' => 'Universitaire', 'shifts' => 'Employés', 'calendar' => 'Agenda', 'booking' => 'Réservation', 'project' => 'Projet'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('default_module', $user->default_module) === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Fuseau horaire</label>
        <select id="timezone" name="timezone"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
            @foreach(\DateTimeZone::listIdentifiers(\DateTimeZone::EUROPE) as $tz)
                <option value="{{ $tz }}" @selected(old('timezone', $user->timezone) === $tz)>{{ $tz }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-4 pt-2">
        <button type="submit"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
            Enregistrer
        </button>
        @if(session('status') === 'profile-updated')
            <p x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,2500)"
               class="text-sm text-emerald-600 font-medium">Modifications sauvegardées.</p>
        @endif
    </div>
</form>
