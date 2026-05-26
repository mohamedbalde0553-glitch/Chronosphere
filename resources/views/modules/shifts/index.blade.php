<x-app-layout>
    <x-slot name="title">Module RH — Tableau de bord</x-slot>

    <div x-data="hrDashboard()" x-init="loadStats()">

        {{-- ===== EN-TÊTE ===== --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Tableau de bord RH</h2>
                <p class="text-sm text-gray-500 mt-0.5">Semaine du {{ now()->startOfWeek()->translatedFormat('d F Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shifts.export.excel') }}"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    Export Excel
                </a>
                <button @click="exportPdf()"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>

        {{-- ===== KPI CARDS ===== --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Heures / semaine</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats ? (stats.week.total_minutes/60).toFixed(1)+'h' : '…'"></p>
                <p class="text-xs text-gray-500 mt-1" x-text="stats ? stats.week.employees_active + ' employés actifs' : ''"></p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Heures sup</span>
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats ? (stats.week.overtime_minutes/60).toFixed(1)+'h' : '…'"></p>
                <p class="text-xs text-gray-500 mt-1">À valider / payer</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Congés en attente</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats ? stats.week.leaves_pending : '…'"></p>
                <p class="text-xs text-gray-500 mt-1">
                    <a href="{{ route('shifts.leaves.index') }}" class="text-amber-600 hover:underline">Gérer →</a>
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Absentéisme</span>
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="stats ? stats.week.absenteeism_rate+'%' : '…'"></p>
                <p class="text-xs text-gray-500 mt-1">Taux cette semaine</p>
            </div>

        </div>

        {{-- ===== GRAPHIQUES + NAV ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

            {{-- Graphique heures par département --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Heures travaillées par département (semaine)</h3>
                <div class="relative h-52">
                    <canvas id="deptChart"></canvas>
                    <p x-show="!stats" class="absolute inset-0 flex items-center justify-center text-sm text-gray-300">Chargement…</p>
                </div>
            </div>

            {{-- Nav modules --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Accès rapide</h3>
                <nav class="space-y-1">
                    @foreach([
                        ['label'=>'Grille de planning',  'route'=>'shifts.planning',          'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',  'color'=>'indigo'],
                        ['label'=>'Employés',             'route'=>'shifts.employees.index',   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'emerald'],
                        ['label'=>'Départements',         'route'=>'shifts.departments.index', 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'blue'],
                        ['label'=>'Types de shifts',      'route'=>'shifts.shift-types.index', 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'violet'],
                        ['label'=>'Demandes de congés',   'route'=>'shifts.leaves.index',      'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color'=>'amber'],
                        ['label'=>'Compétences',           'route'=>'shifts.skills.index',      'icon'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'color'=>'purple'],
                    ] as $nav)
                    <a href="{{ route($nav['route']) }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors
                              {{ request()->routeIs($nav['route']) ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}">
                        <svg class="w-4 h-4 text-{{ $nav['color'] }}-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}"/>
                        </svg>
                        {{ $nav['label'] }}
                    </a>
                    @endforeach
                </nav>
            </div>

        </div>

        {{-- ===== TOP 5 + SHIFTS RÉCENTS ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Top 5 employés --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Top 5 — Heures cette semaine</h3>
                <div x-show="!stats" class="space-y-3">
                    @for($i=0;$i<5;$i++)
                    <div class="h-8 bg-gray-100 rounded animate-pulse"></div>
                    @endfor
                </div>
                <div x-show="stats" class="space-y-3">
                    <template x-for="(emp, i) in stats?.top5_employees ?? []" :key="i">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-gray-400 w-4" x-text="i+1"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="emp.name"></p>
                                <div class="mt-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full"
                                         :style="'width:' + Math.min(100, emp.minutes / (stats.top5_employees[0]?.minutes || 1) * 100) + '%'"></div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 shrink-0"
                                  x-text="(emp.minutes/60).toFixed(1)+'h'"></span>
                        </div>
                    </template>
                    <p x-show="stats?.top5_employees?.length === 0" class="text-sm text-gray-400 text-center py-4">
                        Aucun shift cette semaine.
                    </p>
                </div>
            </div>

            {{-- Prochains shifts --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Prochains shifts (7 jours)</h3>
                    <a href="{{ route('shifts.planning') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Voir le planning →</a>
                </div>
                @if($upcomingShifts->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">Aucun shift planifié.</p>
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

                @if($pendingLeaves->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-semibold text-amber-700 mb-2">Congés en attente de validation</p>
                    <div class="space-y-2">
                        @foreach($pendingLeaves as $leave)
                        <div class="flex items-center gap-3 p-2.5 rounded-lg bg-amber-50">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $leave->employee->user->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }}
                                </p>
                            </div>
                            <a href="{{ route('shifts.leaves.index') }}"
                               class="text-xs text-amber-600 hover:text-amber-700 font-medium shrink-0">Valider →</a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function hrDashboard() {
            return {
                stats: null,
                deptChart: null,

                async loadStats() {
                    try {
                        const res = await fetch('{{ route('shifts.stats') }}');
                        this.stats = await res.json();
                        this.$nextTick(() => this.renderCharts());
                    } catch(e) { console.error(e); }
                },

                renderCharts() {
                    if (!this.stats) return;

                    // Heures par département
                    const deptCtx = document.getElementById('deptChart');
                    if (deptCtx && this.stats.hours_by_dept) {
                        const labels = Object.keys(this.stats.hours_by_dept);
                        const data   = Object.values(this.stats.hours_by_dept);
                        const colors = ['#059669','#0891B2','#7C3AED','#EA580C','#DC2626','#DB2777','#6B7280','#047857'];
                        if (this.deptChart) this.deptChart.destroy();
                        this.deptChart = new Chart(deptCtx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Heures travaillées',
                                    data,
                                    backgroundColor: colors.slice(0, labels.length),
                                    borderRadius: 6,
                                    borderSkipped: false,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } },
                                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                                },
                            },
                        });
                    }
                },

                async exportPdf() {
                    try {
                        const res  = await fetch('{{ route('shifts.export.pdf-data') }}');
                        const data = await res.json();
                        const { jsPDF } = window.jspdf ?? {};
                        if (!jsPDF) { alert('jsPDF non disponible. Utilisez l\'export Excel.'); return; }
                        const doc   = new jsPDF();
                        doc.setFontSize(14);
                        doc.text('Planning — Semaine du ' + data.week, 14, 20);
                        doc.setFontSize(9);
                        let y = 32;
                        doc.text('Employé', 14, y); doc.text('Type', 60, y); doc.text('Début', 100, y); doc.text('Fin', 130, y); doc.text('Heures', 160, y);
                        y += 4; doc.line(14, y, 196, y); y += 5;
                        data.shifts.forEach(s => {
                            if (y > 270) { doc.addPage(); y = 20; }
                            doc.text(s.employee.substring(0,22), 14, y);
                            doc.text(s.type.substring(0,18), 60, y);
                            doc.text(s.start, 100, y);
                            doc.text(s.end, 130, y);
                            doc.text(String(s.hours)+'h', 160, y);
                            y += 7;
                        });
                        doc.save('planning_' + data.week.replace(/\//g,'-') + '.pdf');
                    } catch(e) { console.error(e); alert('Erreur export PDF.'); }
                },
            };
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    @endpush
</x-app-layout>
