<x-app-layout>
    <x-slot name="title">{{ $schedule->name }}</x-slot>

    <div x-data="scheduleShow()" x-init="init()">

        {{-- En-tête --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <div class="flex items-start gap-3">
                <span class="mt-1 inline-block w-5 h-5 rounded-full flex-shrink-0"
                      style="background:{{ $schedule->color }}"></span>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $schedule->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $schedule->department?->name ?? 'Tous les départements' }}
                        · {{ $schedule->start_date->format('d/m/Y') }} → {{ $schedule->end_date ? $schedule->end_date->format('d/m/Y') : '∞' }}
                    </p>
                    @if($schedule->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $schedule->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('shifts.schedules.index') }}"
                   class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    ← Retour
                </a>
                @if($schedule->is_active)
                <span class="px-3 py-2 text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg font-medium">Actif</span>
                @else
                <span class="px-3 py-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-lg">Inactif</span>
                @endif
            </div>
        </div>

        @if($conflicts->count())
        <div class="mb-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-4 py-3">
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400 mb-1">⚠ Chevauchements détectés</p>
            <ul class="text-xs text-amber-600 dark:text-amber-300 space-y-0.5">
                @foreach($conflicts as $c)
                <li>→ <a href="{{ route('shifts.schedules.show', $c) }}" class="underline">{{ $c->name }}</a>
                    ({{ $c->start_date->format('d/m/Y') }} – {{ $c->end_date ? $c->end_date->format('d/m/Y') : '∞' }})
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Colonne gauche : jours --}}
            <div class="lg:col-span-1 space-y-5">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 dark:text-white text-sm mb-3">Jours travaillés</h3>
                    @forelse($schedule->days as $day)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-20">
                            {{ \App\Modules\Shifts\Models\WorkScheduleDay::dayLabel($day->day_of_week) }}
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ substr($day->start_time,0,5) }} – {{ substr($day->end_time,0,5) }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $day->break_minutes }}min pause</span>
                        @if($day->is_overtime_eligible)
                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">×{{ number_format($day->multiplier,2) }}</span>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 dark:text-gray-500">Aucun jour configuré.</p>
                    @endforelse
                </div>

                {{-- Générer les shifts --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 dark:text-white text-sm mb-3">Générer les shifts</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Crée automatiquement les shifts pour tous les employés du département sur la période choisie.
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Du</label>
                            <input type="date" x-model="genStart"
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Au</label>
                            <input type="date" x-model="genEnd"
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                        <button @click="generateShifts()" :disabled="generating"
                                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg flex items-center justify-center gap-2">
                            <svg x-show="generating" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span x-text="generating ? 'Génération...' : 'Générer'"></span>
                        </button>
                        <p x-show="genResult !== null" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium text-center"
                           x-text="genResult + ' shift(s) créé(s).'"></p>
                    </div>
                </div>
            </div>

            {{-- Colonne droite : employés + exceptions --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Employés concernés --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">
                            Employés concernés
                            <span class="ml-2 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full text-xs">{{ $employees->count() }}</span>
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto">
                        @forelse($employees as $emp)
                        <div class="flex items-center gap-3 px-5 py-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-xs font-bold text-emerald-700 dark:text-emerald-400 flex-shrink-0">
                                {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $emp->user->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $emp->employee_code }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500">Aucun employé actif dans ce département.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Exceptions individuelles --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Exceptions individuelles</h3>
                        <button @click="overrideModal=true"
                                class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium">
                            + Ajouter
                        </button>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($overrides as $ov)
                        <div class="flex items-center justify-between px-5 py-3 gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $ov->employee->user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ov->override_start_date->format('d/m/Y') }} → {{ $ov->override_end_date->format('d/m/Y') }}
                                    @if($ov->reason) · {{ $ov->reason }} @endif
                                </p>
                            </div>
                            <button @click="deleteOverride({{ $ov->id }})"
                                    class="text-xs text-red-400 hover:text-red-600 flex-shrink-0">Supprimer</button>
                        </div>
                        @empty
                        <p class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500">Aucune exception enregistrée.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        {{-- Modal exception --}}
        <div x-show="overrideModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.outside="overrideModal=false"
                 class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white">Ajouter une exception</h3>
                    <button @click="overrideModal=false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                </div>
                <form @submit.prevent="saveOverride()" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Employé *</label>
                        <select x-model.number="ovForm.employee_id" required
                                class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                            <option value="">Sélectionner...</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->user->name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Du *</label>
                            <input type="date" x-model="ovForm.override_start_date" required
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Au *</label>
                            <input type="date" x-model="ovForm.override_end_date" required
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Raison</label>
                        <input type="text" x-model="ovForm.reason" placeholder="Ex : congé maternité, mi-temps..."
                               class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="overrideModal=false"
                                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            Annuler
                        </button>
                        <button type="submit" :disabled="savingOv"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg">
                            <span x-text="savingOv ? 'Enregistrement...' : 'Enregistrer'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function scheduleShow() {
        return {
            genStart: '', genEnd: '', generating: false, genResult: null,
            overrideModal: false, savingOv: false,
            ovForm: { employee_id: '', override_start_date: '', override_end_date: '', reason: '' },

            init() {
                const today = new Date();
                const pad   = n => String(n).padStart(2,'0');
                this.genStart = `${today.getFullYear()}-${pad(today.getMonth()+1)}-01`;
                const last    = new Date(today.getFullYear(), today.getMonth()+1, 0);
                this.genEnd   = `${last.getFullYear()}-${pad(last.getMonth()+1)}-${pad(last.getDate())}`;
            },

            async generateShifts() {
                if (!this.genStart || !this.genEnd) return;
                this.generating = true; this.genResult = null;
                try {
                    const res = await fetch('{{ route('shifts.schedules.generate', $schedule) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ start_date: this.genStart, end_date: this.genEnd }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw data;
                    this.genResult = data.created;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'success', message: data.created + ' shift(s) créé(s).' }}));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'error', message: e?.message || 'Erreur lors de la génération.' }}));
                } finally {
                    this.generating = false;
                }
            },

            async saveOverride() {
                this.savingOv = true;
                try {
                    const res = await fetch('{{ route('shifts.schedules.overrides.store', $schedule) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(this.ovForm),
                    });
                    if (!res.ok) throw await res.json();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'success', message:'Exception ajoutée.' }}));
                    this.overrideModal = false;
                    setTimeout(() => location.reload(), 600);
                } catch (e) {
                    const msg = e?.message || (e?.errors ? Object.values(e.errors).flat().join(' ') : 'Erreur');
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'error', message: msg }}));
                } finally {
                    this.savingOv = false;
                }
            },

            async deleteOverride(id) {
                if (!confirm('Supprimer cette exception ?')) return;
                await fetch(`/shifts/overrides/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                window.dispatchEvent(new CustomEvent('toast', { detail: { type:'success', message:'Exception supprimée.' }}));
                setTimeout(() => location.reload(), 600);
            },
        }
    }
    </script>
    @endpush
</x-app-layout>
