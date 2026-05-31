@php $authUser = auth()->user(); @endphp
<x-app-layout>
    <x-slot name="title">Mon espace — {{ $employee?->user?->name ?? $authUser->name }}</x-slot>

    @if(!$employee)
    {{-- Compte hr_employee sans fiche employée --}}
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Bienvenue, {{ $authUser->name }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
            Votre compte n'est pas encore rattaché à une fiche employé.<br>
            Contactez votre responsable RH pour qu'il complète votre profil.
        </p>
    </div>
    @else
    {{-- En-tête personnel --}}
    <div class="flex items-center gap-4 mb-6">
        <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                {{ strtoupper(substr($employee->user->name, 0, 1)) }}
            </span>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->user->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $employee->position?->title ?? '—' }} · {{ $employee->department?->name ?? '—' }}
                <span class="ml-2 text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded">{{ $employee->employee_code }}</span>
            </p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('shifts.planning') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                Voir mon planning
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php
        $kpis = [
            ['label' => 'Shifts cette semaine', 'value' => $stats['shifts_week'],    'color' => 'emerald', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['label' => 'Shifts ce mois',       'value' => $stats['shifts_month'],   'color' => 'blue',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['label' => 'Congés en attente',    'value' => $stats['leaves_pending'], 'color' => 'amber',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Congés approuvés',     'value' => $stats['leaves_approved'],'color' => 'violet',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        @endphp
        @foreach($kpis as $k)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-{{ $k['color'] }}-100 dark:bg-{{ $k['color'] }}-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-{{ $k['color'] }}-600 dark:text-{{ $k['color'] }}-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
                </svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $k['value'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $k['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Prochains shifts --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">Mes prochains shifts</h2>
                <a href="{{ route('shifts.planning') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Voir tout →</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($upcomingShifts as $shift)
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="w-2 h-2 rounded-full shrink-0" style="background:{{ $shift->shiftType?->color ?? '#059669' }}"></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $shift->shiftType?->name ?? 'Shift' }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $shift->start_at->translatedFormat('l d/m') }}
                            · {{ $shift->start_at->format('H:i') }} – {{ $shift->end_at->format('H:i') }}
                        </div>
                    </div>
                    @php
                    $sc = ['planned'=>'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300','completed'=>'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'];
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc[$shift->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        {{ $shift->status === 'planned' ? 'Planifié' : 'Terminé' }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">Aucun shift à venir.</div>
                @endforelse
            </div>
        </div>

        {{-- Mes congés --}}
        <div x-data="leaveForm()" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">Mes congés</h2>
                <button @click="showForm=!showForm" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
                    + Demander
                </button>
            </div>

            {{-- Formulaire rapide --}}
            <div x-show="showForm" x-cloak class="px-5 py-4 bg-gray-50 dark:bg-gray-700/40 border-b border-gray-100 dark:border-gray-700 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                        <select x-model="form.type" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="conge_paye">Congé payé</option>
                            <option value="rtt">RTT</option>
                            <option value="maladie">Maladie</option>
                            <option value="sans_solde">Sans solde</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Motif</label>
                        <input x-model="form.reason" type="text" placeholder="(optionnel)" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Du</label>
                        <input x-model="form.start_date" type="date" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Au</label>
                        <input x-model="form.end_date" type="date" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="submit()" :disabled="loading" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg disabled:opacity-50 transition-colors">
                        <span x-show="!loading">Envoyer la demande</span>
                        <span x-show="loading" x-cloak>Envoi…</span>
                    </button>
                    <button @click="showForm=false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                </div>
                <p x-show="error" x-cloak class="text-xs text-red-500" x-text="error"></p>
                <p x-show="success" x-cloak class="text-xs text-emerald-600 dark:text-emerald-400">Demande envoyée avec succès !</p>
            </div>

            {{-- Liste --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($myLeaves as $leave)
                @php
                $typeLabels = ['conge_paye'=>'Congé payé','rtt'=>'RTT','maladie'=>'Maladie','sans_solde'=>'Sans solde','autre'=>'Autre'];
                $stColors   = ['pending'=>'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300','approved'=>'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300','rejected'=>'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400','cancelled'=>'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'];
                $stLabels   = ['pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Refusé','cancelled'=>'Annulé'];
                @endphp
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $typeLabels[$leave->type] ?? $leave->type }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $stColors[$leave->status] ?? '' }}">
                        {{ $stLabels[$leave->status] ?? $leave->status }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">Aucune demande de congé.</div>
                @endforelse
            </div>

            @if($myLeaves->count() >= 5)
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('shifts.leaves.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Voir toutes mes demandes →</a>
            </div>
            @endif
        </div>
    </div>
    @endif {{-- fin @else ($employee existe) --}}
</x-app-layout>

@push('scripts')
<script>
function leaveForm() {
    return {
        showForm: false,
        loading: false,
        error: '',
        success: false,
        form: { type: 'conge_paye', start_date: '', end_date: '', reason: '' },
        async submit() {
            this.error = ''; this.success = false; this.loading = true;
            try {
                const res = await fetch('{{ route('shifts.leaves.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify(this.form),
                });
                if (!res.ok) {
                    const d = await res.json();
                    this.error = Object.values(d.errors || {}).flat().join(' ') || 'Erreur.';
                } else {
                    this.success = true;
                    this.form = { type: 'conge_paye', start_date: '', end_date: '', reason: '' };
                    setTimeout(() => { this.showForm = false; location.reload(); }, 1500);
                }
            } catch { this.error = 'Erreur réseau.'; }
            this.loading = false;
        }
    };
}
</script>
@endpush
