<x-app-layout>
    <x-slot name="title">Emplois du temps</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['label'=>'Salles',       'value'=>$stats['rooms'],    'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6',  'color'=>'blue'],
            ['label'=>'Matières',     'value'=>$stats['subjects'], 'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color'=>'emerald'],
            ['label'=>'Groupes',      'value'=>$stats['groups'],   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'violet'],
            ['label'=>'Enseignants',  'value'=>$stats['teachers'], 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color'=>'amber'],
            ['label'=>'Séances/sem.', 'value'=>$stats['sessions'], 'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'indigo'],
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
                    ['label'=>'Grille des séances', 'route'=>'timetable.schedule',  'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'blue'],
                    ['label'=>'Salles',              'route'=>'timetable.rooms.index',    'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10',  'color'=>'emerald'],
                    ['label'=>'Matières',            'route'=>'timetable.subjects.index', 'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5', 'color'=>'violet'],
                    ['label'=>'Groupes',             'route'=>'timetable.groups.index',   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857', 'color'=>'amber'],
                    ['label'=>'Enseignants',         'route'=>'timetable.teachers.index', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0z', 'color'=>'rose'],
                ] as $nav)
                <a href="{{ route($nav['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors
                          {{ request()->routeIs($nav['route']) ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium' : '' }}">
                    <svg class="w-4 h-4 text-{{ $nav['color'] }}-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}"/>
                    </svg>
                    {{ $nav['label'] }}
                </a>
                @endforeach
            </nav>
        </div>

        {{-- Upcoming sessions --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Prochaines séances (7 jours)</h3>
                <a href="{{ route('timetable.schedule') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium">Voir tout →</a>
            </div>

            @if($upcomingSessions->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Aucune séance planifiée cette semaine.</p>
            @else
            <div class="space-y-2">
                @foreach($upcomingSessions as $session)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <div class="w-1.5 h-10 rounded-full shrink-0"
                         style="background: {{ $session->course->subject->color ?? '#1E40AF' }}"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $session->course->subject->name }}
                            <span class="font-normal text-gray-500 dark:text-gray-400">— {{ $session->course->classGroup?->name }}</span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $session->start_at->translatedFormat('D d/m') }}
                            {{ $session->start_at->format('H:i') }}–{{ $session->end_at->format('H:i') }}
                            @if($session->room) · {{ $session->room->code }} @endif
                        </p>
                    </div>
                    <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full shrink-0">
                        {{ $session->course->teacher?->user?->name ?? 'TBA' }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            @if($currentYear)
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Année en cours : <strong class="text-gray-700 dark:text-gray-300">{{ $currentYear->name }}</strong>
                ({{ $currentYear->start_date->format('d/m/Y') }} – {{ $currentYear->end_date->format('d/m/Y') }})
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
