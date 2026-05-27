<x-app-layout>
    <x-slot name="title">{{ $employee->user->name }} — Fiche employé</x-slot>

    <div x-data="employeeShow()" x-init="init()">

        {{-- ===== EN-TÊTE ===== --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ $employee->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($employee->user->name) . '&background=047857&color=fff&size=128' }}"
                     alt="{{ $employee->user->name }}"
                     class="w-16 h-16 rounded-full object-cover border-2 border-emerald-200 dark:border-emerald-800 shrink-0">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->user->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $employee->position?->title ?? '—' }}
                        @if($employee->department)
                            &nbsp;·&nbsp; {{ $employee->department->name }}
                        @endif
                    </p>
                    <p class="text-xs font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $employee->employee_code }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shifts.employees.index') }}"
                   class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    ← Retour
                </a>
                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium
                    @if($employee->status === 'active') bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300
                    @elseif($employee->status === 'suspended') bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400
                    @else bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 @endif">
                    {{ ['active' => 'Actif', 'inactive' => 'Inactif', 'suspended' => 'Suspendu'][$employee->status] ?? $employee->status }}
                </span>
            </div>
        </div>

        {{-- ===== TABS NAV ===== --}}
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex gap-6">
                @foreach([
                    ['id' => 'infos',    'label' => 'Informations'],
                    ['id' => 'planning', 'label' => 'Planning'],
                    ['id' => 'leaves',   'label' => 'Congés (' . count($leaves) . ')'],
                    ['id' => 'schedule', 'label' => 'Horaire actif'],
                ] as $tab)
                <button @click="switchTab('{{ $tab['id'] }}')"
                        :class="activeTab === '{{ $tab['id'] }}'
                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="py-3 px-1 border-b-2 text-sm font-medium transition-colors whitespace-nowrap">
                    {{ $tab['label'] }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- ===== TAB: INFORMATIONS ===== --}}
        <div x-show="activeTab === 'infos'" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Infos contrat --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Informations contractuelles</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Email</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $employee->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Date d'embauche</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $employee->hire_date->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Type de contrat</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ strtoupper($employee->contract_type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Heures / semaine</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ number_format($employee->weekly_hours_minutes / 60, 1) }}h</dd>
                        </div>
                        @if($employee->department)
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Département</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $employee->department->name }}</dd>
                        </div>
                        @endif
                        @if($employee->position)
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Poste</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $employee->position->title }}</dd>
                        </div>
                        @endif
                        @if($employee->max_daily_minutes)
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Max. quotidien</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ number_format($employee->max_daily_minutes / 60, 1) }}h</dd>
                        </div>
                        @endif
                        @if($employee->min_rest_minutes)
                        <div>
                            <dt class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Repos min.</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ number_format($employee->min_rest_minutes / 60, 1) }}h</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Compétences --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Compétences</h3>
                    @if($employee->skills->isEmpty())
                        <p class="text-sm text-gray-400 dark:text-gray-500">Aucune compétence enregistrée.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($employee->skills as $skill)
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $skill->name }}</span>
                                    @if($skill->category)
                                        <span class="ml-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $skill->category }}</span>
                                    @endif
                                </div>
                                <div class="flex gap-0.5">
                                    @for($s = 1; $s <= 5; $s++)
                                    <div class="w-2 h-2 rounded-full {{ $s <= ($skill->pivot->level ?? 0) ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- Stats du mois --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($monthWorked / 60, 1) }}h</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Travaillées ce mois</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $expectedMinutes > 0 ? number_format($expectedMinutes / 60, 1) . 'h' : '—' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Attendues ce mois</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                    @php $diff = $monthWorked - $expectedMinutes; @endphp
                    <p class="text-2xl font-bold {{ $diff >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff / 60, 1) }}h
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Écart</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $leaves->count() }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Demandes congés</p>
                </div>
            </div>
        </div>

        {{-- ===== TAB: PLANNING ===== --}}
        <div x-show="activeTab === 'planning'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Shifts — {{ $employee->user->name }}</h3>
                    <a href="{{ route('shifts.planning') }}?by=employee&id={{ $employee->id }}"
                       class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-medium">
                        Voir dans la grille →
                    </a>
                </div>
                <div id="employeeCalendar" class="h-[520px]"></div>
            </div>
        </div>

        {{-- ===== TAB: CONGÉS ===== --}}
        <div x-show="activeTab === 'leaves'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Demandes de congés</h3>
                    <a href="{{ route('shifts.leaves.index') }}"
                       class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-medium">
                        Gérer toutes les demandes →
                    </a>
                </div>
                @if($leaves->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-10">Aucune demande enregistrée.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <th class="px-5 py-3 text-left font-semibold">Type</th>
                                    <th class="px-5 py-3 text-left font-semibold">Début</th>
                                    <th class="px-5 py-3 text-left font-semibold">Fin</th>
                                    <th class="px-5 py-3 text-left font-semibold">Motif</th>
                                    <th class="px-5 py-3 text-left font-semibold">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($leaves as $leave)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                        {{ match($leave->type) {
                                            'conge_paye'  => 'Congé payé',
                                            'maladie'     => 'Maladie',
                                            'maternite'   => 'Maternité',
                                            'formation'   => 'Formation',
                                            default       => ucfirst(str_replace('_', ' ', $leave->type))
                                        } }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $leave->start_date->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $leave->end_date->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $leave->reason ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ match($leave->status) {
                                                'approved'  => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                                'pending'   => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'rejected'  => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                                'cancelled' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                                default     => 'bg-gray-100 text-gray-500',
                                            } }}">
                                            {{ ['approved' => 'Approuvé', 'pending' => 'En attente', 'rejected' => 'Refusé', 'cancelled' => 'Annulé'][$leave->status] ?? $leave->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== TAB: HORAIRE ACTIF ===== --}}
        <div x-show="activeTab === 'schedule'" x-cloak>
            @if($activeSchedule)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Infos horaire --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full shrink-0" style="background: {{ $activeSchedule->color ?? '#3B82F6' }}"></div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">{{ $activeSchedule->name }}</h3>
                        </div>
                        <a href="{{ route('shifts.schedules.show', $activeSchedule) }}"
                           class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                            Voir l'horaire →
                        </a>
                    </div>
                    @if($activeSchedule->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $activeSchedule->description }}</p>
                    @endif

                    {{-- Jours configurés --}}
                    <div class="space-y-2">
                        @php
                            $dayNames = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                        @endphp
                        @foreach($activeSchedule->days->sortBy('day_of_week') as $day)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="w-20 text-xs font-semibold text-gray-500 dark:text-gray-400 shrink-0">
                                {{ $dayNames[$day->day_of_week] ?? "Jour {$day->day_of_week}" }}
                            </span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ substr($day->start_time, 0, 5) }} → {{ substr($day->end_time, 0, 5) }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                ({{ number_format($day->workedMinutes() / 60, 1) }}h net)
                            </span>
                            @if($day->is_overtime_eligible)
                                <span class="text-xs bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 px-1.5 py-0.5 rounded">HS éligible</span>
                            @endif
                            @if($day->multiplier > 1)
                                <span class="text-xs bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 px-1.5 py-0.5 rounded">×{{ $day->multiplier }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Stats du mois --}}
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">
                            Ce mois — {{ now()->translatedFormat('F Y') }}
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Heures attendues</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($expectedMinutes / 60, 1) }}h</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Heures travaillées</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($monthWorked / 60, 1) }}h</span>
                                </div>
                                @if($expectedMinutes > 0)
                                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $monthWorked >= $expectedMinutes ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                         style="width: {{ min(100, $expectedMinutes > 0 ? round($monthWorked / $expectedMinutes * 100) : 0) }}%"></div>
                                </div>
                                @endif
                            </div>
                            @php $diff = $monthWorked - $expectedMinutes; @endphp
                            @if($diff !== 0)
                            <div class="flex justify-between text-sm pt-1 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">Écart</span>
                                <span class="font-semibold {{ $diff >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                                    {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff / 60, 1) }}h
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                        <h4 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Période de l'horaire</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Du {{ $activeSchedule->start_date->format('d/m/Y') }}
                            @if($activeSchedule->end_date)
                                au {{ $activeSchedule->end_date->format('d/m/Y') }}
                            @else
                                (sans date de fin)
                            @endif
                        </p>
                    </div>
                </div>

            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-10 text-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Aucun horaire actif</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">Aucun horaire périodique n'est configuré pour cet employé aujourd'hui.</p>
                <a href="{{ route('shifts.schedules.index') }}"
                   class="inline-block mt-4 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                    Gérer les horaires →
                </a>
            </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
        function employeeShow() {
            return {
                activeTab: 'infos',
                calendar: null,

                init() {
                    // Restaure l'onglet depuis l'URL hash
                    const hash = window.location.hash.replace('#', '');
                    if (['infos', 'planning', 'leaves', 'schedule'].includes(hash)) {
                        this.activeTab = hash;
                        if (hash === 'planning') this.$nextTick(() => this.initCalendar());
                    }
                },

                switchTab(tab) {
                    this.activeTab = tab;
                    window.location.hash = tab;
                    if (tab === 'planning') {
                        this.$nextTick(() => this.initCalendar());
                    }
                },

                initCalendar() {
                    if (this.calendar) return;
                    const el = document.getElementById('employeeCalendar');
                    if (!el || !window.FullCalendar) return;

                    const isDark = document.documentElement.classList.contains('dark');

                    this.calendar = new FullCalendar.Calendar(el, {
                        plugins: ['timeGrid', 'dayGrid', 'list', 'interaction'],
                        initialView: 'timeGridWeek',
                        locale: 'fr',
                        firstDay: 1,
                        allDaySlot: true,
                        nowIndicator: true,
                        slotMinTime: '06:00',
                        slotMaxTime: '23:00',
                        height: '100%',
                        headerToolbar: {
                            left:   'prev,next today',
                            center: 'title',
                            right:  'timeGridWeek,timeGridDay,listMonth',
                        },
                        events: {
                            url: '{{ route('shifts.planning.feed') }}',
                            extraParams: {
                                by: 'employee',
                                id: {{ $employee->id }},
                            },
                        },
                        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
                    });
                    this.calendar.render();
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
