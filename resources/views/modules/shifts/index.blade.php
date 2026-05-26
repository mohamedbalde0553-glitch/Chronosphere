<x-app-layout>
    <x-slot name="title">Planning des shifts</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label'=>'Employés actifs',  'value'=>$stats['employees'],      'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'emerald'],
            ['label'=>'Départements',     'value'=>$stats['departments'],    'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'blue'],
            ['label'=>'Shifts cette sem.','value'=>$stats['shifts_week'],    'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'indigo'],
            ['label'=>'Congés en attente','value'=>$stats['leaves_pending'], 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color'=>'amber'],
        ] as $s)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-{{ $s['color'] }}-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $s['color'] }}-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Quick nav --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Gestion</h3>
            <nav class="space-y-1">
                @foreach([
                    ['label'=>'Grille de planning',  'route'=>'shifts.planning',          'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',  'color'=>'indigo'],
                    ['label'=>'Employés',             'route'=>'shifts.employees.index',   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'emerald'],
                    ['label'=>'Départements',         'route'=>'shifts.departments.index', 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'blue'],
                    ['label'=>'Types de shifts',      'route'=>'shifts.shift-types.index', 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'violet'],
                    ['label'=>'Demandes de congés',   'route'=>'shifts.leaves.index',      'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color'=>'amber'],
                ] as $nav)
                <a href="{{ route($nav['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors
                          {{ request()->routeIs($nav['route']) ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                    <svg class="w-4 h-4 text-{{ $nav['color'] }}-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}"/>
                    </svg>
                    {{ $nav['label'] }}
                </a>
                @endforeach
            </nav>
        </div>

        <div class="lg:col-span-2 space-y-5">

            {{-- Upcoming shifts --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Prochains shifts (7 jours)</h3>
                    <a href="{{ route('shifts.planning') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Voir tout →</a>
                </div>
                @if($upcomingShifts->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Aucun shift planifié cette semaine.</p>
                @else
                <div class="space-y-2">
                    @foreach($upcomingShifts as $shift)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                        <div class="w-1.5 h-10 rounded-full shrink-0"
                             style="background: {{ $shift->shiftType?->color ?? '#059669' }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $shift->employee->user->name }}
                                @if($shift->shiftType)
                                    <span class="font-normal text-gray-500">— {{ $shift->shiftType->name }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $shift->start_at->translatedFormat('D d/m') }}
                                {{ $shift->start_at->format('H:i') }}–{{ $shift->end_at->format('H:i') }}
                            </p>
                        </div>
                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full shrink-0">
                            {{ $shift->employee->department?->name ?? '—' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Pending leaves --}}
            @if($pendingLeaves->isNotEmpty())
            <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-amber-700">Congés en attente de validation</h3>
                    <a href="{{ route('shifts.leaves.index') }}" class="text-xs text-amber-600 hover:text-amber-700 font-medium">Gérer →</a>
                </div>
                <div class="space-y-2">
                    @foreach($pendingLeaves as $leave)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-amber-50">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $leave->employee->user->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }}
                                · {{ $leave->type }}
                            </p>
                        </div>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">En attente</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
