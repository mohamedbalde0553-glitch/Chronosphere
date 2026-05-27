<x-app-layout>
    <x-slot name="title">Réservations</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label'=>'Ressources actives',       'value'=>$stats['resources'],        'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'orange'],
            ['label'=>'Catégories',               'value'=>$stats['categories'],       'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color'=>'blue'],
            ['label'=>'Résa. cette semaine',      'value'=>$stats['bookings_week'],    'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'indigo'],
            ['label'=>'En attente d\'approbation','value'=>$stats['pending_approval'], 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'amber'],
        ] as $s)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-{{ $s['color'] }}-50 dark:bg-{{ $s['color'] }}-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $s['color'] }}-600 dark:text-{{ $s['color'] }}-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Quick nav --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Gestion</h3>
            <nav class="space-y-1">
                @foreach([
                    ['label'=>'Calendrier des réservations', 'route'=>'booking.calendar',           'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'indigo'],
                    ['label'=>'Toutes les réservations',     'route'=>'booking.reservations.index', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color'=>'orange'],
                    ['label'=>'Ressources',                  'route'=>'booking.resources.index',    'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'blue'],
                    ['label'=>'Catégories',                  'route'=>'booking.categories.index',   'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color'=>'violet'],
                ] as $nav)
                <a href="{{ route($nav['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors
                          {{ request()->routeIs($nav['route']) ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 font-medium' : '' }}">
                    <svg class="w-4 h-4 text-{{ $nav['color'] }}-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}"/>
                    </svg>
                    {{ $nav['label'] }}
                </a>
                @endforeach
            </nav>

            @if($resources->isNotEmpty())
            <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Ressources</h4>
                <div class="space-y-1.5">
                    @foreach($resources->take(6) as $r)
                    <a href="{{ route('booking.calendar', ['by'=>'resource','id'=>$r->id]) }}"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $r->color ?? '#EA580C' }}"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $r->name }}</span>
                        <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ $r->capacity }} pl.</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="lg:col-span-2 space-y-5">

            {{-- Upcoming --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Prochaines réservations (7 jours)</h3>
                    <a href="{{ route('booking.calendar') }}" class="text-xs text-orange-600 dark:text-orange-400 hover:text-orange-700 font-medium">Voir calendrier →</a>
                </div>
                @if($upcomingBookings->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Aucune réservation à venir.</p>
                @else
                <div class="space-y-2">
                    @foreach($upcomingBookings as $b)
                    @php $statusBg = $b->status === 'confirmed' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'; @endphp
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="w-1.5 h-10 rounded-full shrink-0"
                             style="background: {{ $b->resource->color ?? '#EA580C' }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $b->title }}
                                <span class="font-normal text-gray-500 dark:text-gray-400">— {{ $b->resource->name }}</span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $b->start_at->translatedFormat('D d/m') }}
                                {{ $b->start_at->format('H:i') }}–{{ $b->end_at->format('H:i') }}
                                · {{ $b->user->name }}
                            </p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $statusBg }}">
                            {{ $b->status === 'confirmed' ? 'Confirmé' : 'En attente' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            @if($pendingBookings->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-amber-200 dark:border-amber-800 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-amber-700 dark:text-amber-400">En attente d'approbation</h3>
                    <a href="{{ route('booking.reservations.index') }}" class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-700 font-medium">Gérer →</a>
                </div>
                <div class="space-y-2">
                    @foreach($pendingBookings as $b)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $b->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $b->resource->name }} ·
                                {{ $b->start_at->format('d/m H:i') }}–{{ $b->end_at->format('H:i') }} ·
                                {{ $b->user->name }}
                            </p>
                        </div>
                        <span class="text-xs bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">En attente</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
