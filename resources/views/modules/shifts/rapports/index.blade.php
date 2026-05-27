<x-app-layout>
    <x-slot name="title">Rapports RH</x-slot>

    {{-- Filtres --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="{{ route('shifts.rapports.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Période</label>
                <select name="period" onchange="this.form.submit()"
                        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="week"       {{ $period === 'week'      ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month"      {{ $period === 'month'     ? 'selected' : '' }}>Ce mois</option>
                    <option value="last_month" {{ $period === 'last_month'? 'selected' : '' }}>Mois dernier</option>
                    <option value="custom"     {{ $period === 'custom'    ? 'selected' : '' }}>Personnalisé</option>
                </select>
            </div>

            @if($period === 'custom')
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Du</label>
                <input type="date" name="from" value="{{ $start->toDateString() }}"
                       class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Au</label>
                <input type="date" name="to" value="{{ $end->toDateString() }}"
                       class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Département</label>
                <select name="department_id" onchange="this.form.submit()"
                        class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous les départements</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $deptId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($period === 'custom')
            <button type="submit"
                    class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                Appliquer
            </button>
            @endif

            {{-- Exports --}}
            <div class="ml-auto flex gap-2">
                <a href="{{ route('shifts.rapports.pdf', request()->query()) }}"
                   class="flex items-center gap-1.5 text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    PDF
                </a>
                <a href="{{ route('shifts.rapports.excel', request()->query()) }}"
                   class="flex items-center gap-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel
                </a>
            </div>
        </form>
    </div>

    {{-- Période courante --}}
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Période : <strong class="text-gray-700 dark:text-gray-200">{{ $start->format('d/m/Y') }}</strong>
        au <strong class="text-gray-700 dark:text-gray-200">{{ $end->format('d/m/Y') }}</strong>
    </p>

    {{-- KPIs --}}
    @php
        $totalH    = intdiv($stats['totalWorked'], 60);
        $totalMin  = $stats['totalWorked'] % 60;
        $otH       = intdiv($stats['totalOvertime'], 60);
        $otMin     = $stats['totalOvertime'] % 60;
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 card-hover">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Heures travaillées</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalH }}h{{ sprintf('%02d', $totalMin) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 card-hover">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Heures supplémentaires</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $otH }}h{{ sprintf('%02d', $otMin) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 card-hover">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Employés actifs</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['totalEmployees'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 card-hover">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Taux absentéisme</p>
            <p class="text-2xl font-bold {{ $stats['absenteeism'] > 10 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                {{ $stats['absenteeism'] }} %
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Top 5 employés --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden card-hover">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top 5 employés</h3>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[400px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Employé</th>
                        <th class="text-right px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Heures</th>
                        <th class="text-right px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Heures sup</th>
                        <th class="text-right px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Shifts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($stats['top5'] as $i => $emp)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-semibold flex items-center justify-center">{{ $i + 1 }}</span>
                                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $emp['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-2.5 text-right font-mono text-gray-700 dark:text-gray-300">{{ $emp['hours'] }}h</td>
                        <td class="px-5 py-2.5 text-right font-mono {{ $emp['overtime'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">{{ $emp['overtime'] }}h</td>
                        <td class="px-5 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $emp['shifts'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-sm text-gray-400">Aucune donnée</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Par département --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden card-hover">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Heures par département</h3>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[400px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Département</th>
                        <th class="text-right px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Shifts</th>
                        <th class="text-right px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Heures</th>
                        <th class="text-right px-5 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Heures sup</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($stats['byDept'] as $dept => $d)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-2.5 font-medium text-gray-800 dark:text-gray-200">{{ $dept }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $d['shifts'] }}</td>
                        <td class="px-5 py-2.5 text-right font-mono text-gray-700 dark:text-gray-300">{{ $d['hours'] }}h</td>
                        <td class="px-5 py-2.5 text-right font-mono {{ $d['overtime'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">{{ $d['overtime'] }}h</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-sm text-gray-400">Aucune donnée</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- Lien rapports dans nav --}}
    @push('scripts')
    <script>
        // Highlight rapports nav link
        document.querySelectorAll('nav a').forEach(a => {
            if (a.href.includes('/shifts/rapports')) a.classList.add('bg-emerald-700', 'text-white');
        });
    </script>
    @endpush
</x-app-layout>
