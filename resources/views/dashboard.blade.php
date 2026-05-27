<x-app-layout>
    <x-slot name="title">Tableau de bord</x-slot>

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Bonjour, {{ auth()->user()->name }} !</h2>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Choisissez un module pour commencer.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

        @php $canUni = auth()->user()->can('timetable.view'); @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden
                    {{ $canUni ? 'card-hover' : 'opacity-60' }}">
            <div class="h-1.5 bg-blue-600"></div>
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/>
                        </svg>
                    </div>
                    @unless($canUni)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 px-2 py-0.5 rounded-full font-medium">Accès refusé</span>
                    @endunless
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Universitaire</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Emplois du temps, cours, salles et enseignants.</p>
                @if($canUni)
                    <a href="{{ route('timetable.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700">
                        Accéder <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>

        @php $canHr = auth()->user()->can('shifts.view'); @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden
                    {{ $canHr ? 'card-hover' : 'opacity-60' }}">
            <div class="h-1.5 bg-emerald-600"></div>
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    @unless($canHr)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 px-2 py-0.5 rounded-full font-medium">Accès refusé</span>
                    @endunless
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Employés</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Quarts de travail, congés et gestion des compétences.</p>
                @if($canHr)
                    <a href="{{ route('shifts.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700">
                        Accéder <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>

        @php $canCal = auth()->user()->can('calendar.view'); @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden
                    {{ $canCal ? 'card-hover' : 'opacity-60' }}">
            <div class="h-1.5 bg-violet-600"></div>
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    @unless($canCal)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 px-2 py-0.5 rounded-full font-medium">Accès refusé</span>
                    @endunless
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Agenda</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Calendriers personnels, événements et invitations.</p>
                @if($canCal)
                    <a href="{{ route('calendar.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-violet-600 dark:text-violet-400 hover:text-violet-700">
                        Accéder <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>

        @php $canBook = auth()->user()->can('booking.view'); @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden
                    {{ $canBook ? 'card-hover' : 'opacity-60' }}">
            <div class="h-1.5 bg-orange-500"></div>
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-500 dark:text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @unless($canBook)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 px-2 py-0.5 rounded-full font-medium">Accès refusé</span>
                    @endunless
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Réservation</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Réservation de salles et ressources partagées.</p>
                @if($canBook)
                    <a href="{{ route('booking.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-orange-500 dark:text-orange-400 hover:text-orange-600">
                        Accéder <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>

        @php $canProj = auth()->user()->can('project.view'); @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden
                    {{ $canProj ? 'card-hover' : 'opacity-60' }}">
            <div class="h-1.5 bg-indigo-600"></div>
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
                        </svg>
                    </div>
                    @unless($canProj)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 px-2 py-0.5 rounded-full font-medium">Accès refusé</span>
                    @endunless
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Projet / Gantt</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Suivi de projets, tâches et diagramme de Gantt.</p>
                @if($canProj)
                    <a href="{{ route('project.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">
                        Accéder <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>

    </div>

    <div class="mt-8 px-5 py-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-wrap gap-6 text-sm text-gray-600 dark:text-gray-400">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span><strong class="text-gray-900 dark:text-white">Rôles :</strong> {{ auth()->user()->getRoleNames()->join(', ') ?: '—' }}</span>
        </span>
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
            <span><strong class="text-gray-900 dark:text-white">Fuseau :</strong> {{ auth()->user()->timezone ?? 'Europe/Paris' }}</span>
        </span>
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            <span><strong class="text-gray-900 dark:text-white">Langue :</strong> {{ auth()->user()->language === 'fr' ? 'Français' : 'English' }}</span>
        </span>
    </div>

</x-app-layout>
