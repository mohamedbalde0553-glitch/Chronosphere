<x-app-layout>
    <x-slot name="title">Horaires périodiques</x-slot>

    <div x-data="scheduleIndex()" x-init="init()">

        {{-- En-tête --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Horaires périodiques</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schedules->total() }} horaire(s)</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('shifts.index') }}"
                   class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    ← Retour
                </a>
                <button @click="openCreate()"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg">
                    + Nouvel horaire
                </button>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="flex flex-wrap gap-3 mb-5">
            <select name="department_id" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg px-3 py-2">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            <select name="active" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg px-3 py-2">
                <option value="">Tous les statuts</option>
                <option value="1" @selected(request('active') === '1')>Actifs</option>
                <option value="0" @selected(request('active') === '0')>Inactifs</option>
            </select>
        </form>

        {{-- Tableau --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 w-8"></th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Département</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Période</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Jours</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-full"
                                  style="background:{{ $schedule->color }}"></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            <a href="{{ route('shifts.schedules.show', $schedule) }}"
                               class="hover:text-emerald-600 dark:hover:text-emerald-400">
                                {{ $schedule->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ $schedule->department?->name ?? '<span class="italic text-gray-400">Tous</span>' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 text-xs">
                            {{ $schedule->start_date->format('d/m/Y') }}
                            → {{ $schedule->end_date ? $schedule->end_date->format('d/m/Y') : '∞' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                            {{ $schedule->days_count ?? '—' }} j
                        </td>
                        <td class="px-4 py-3">
                            @if($schedule->is_active)
                                <span class="px-2 py-1 text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full font-medium">Actif</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-full">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('shifts.schedules.show', $schedule) }}"
                               class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium mr-2">Détail</a>
                            <button @click="openEdit({{ $schedule->toJson() }})"
                                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mr-2">Modifier</button>
                            <button @click="deleteSchedule({{ $schedule->id }})"
                                    class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                            Aucun horaire périodique enregistré.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($schedules->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $schedules->links() }}</div>
            @endif
        </div>

        {{-- Modal Créer / Modifier --}}
        <div x-show="modal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.outside="modal=false"
                 class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white" x-text="editing ? 'Modifier l\'horaire' : 'Nouvel horaire'"></h3>
                    <button @click="modal=false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                </div>

                <form @submit.prevent="save()" class="overflow-y-auto px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nom *</label>
                            <input type="text" x-model="form.name" required
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                            <textarea x-model="form.description" rows="2"
                                      class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Date de début *</label>
                            <input type="date" x-model="form.start_date" required
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Date de fin</label>
                            <input type="date" x-model="form.end_date"
                                   class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Département</label>
                            <select x-model="form.department_id"
                                    class="w-full text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2">
                                <option value="">Tous les départements</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-4 items-end">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Couleur</label>
                                <input type="color" x-model="form.color"
                                       class="w-10 h-9 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                            </div>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer mb-1">
                                <input type="checkbox" x-model="form.is_active" class="rounded">
                                Actif
                            </label>
                        </div>
                    </div>

                    {{-- Jours de la semaine --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Jours travaillés *</label>
                            <button type="button" @click="addDay()"
                                    class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium">+ Ajouter un jour</button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(day, i) in form.days" :key="i">
                                <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                    <div class="col-span-2">
                                        <select x-model.number="day.day_of_week"
                                                class="w-full text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2 py-1">
                                            <option value="1">Lun</option>
                                            <option value="2">Mar</option>
                                            <option value="3">Mer</option>
                                            <option value="4">Jeu</option>
                                            <option value="5">Ven</option>
                                            <option value="6">Sam</option>
                                            <option value="0">Dim</option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input type="time" x-model="day.start_time"
                                               class="w-full text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2 py-1">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="time" x-model="day.end_time"
                                               class="w-full text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2 py-1">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" x-model.number="day.break_minutes" min="0" max="480" placeholder="Pause (min)"
                                               class="w-full text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2 py-1">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" x-model.number="day.multiplier" step="0.05" min="1" max="3" placeholder="×1.00"
                                               class="w-full text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2 py-1">
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <label class="cursor-pointer">
                                            <input type="checkbox" x-model="day.is_overtime_eligible" class="rounded">
                                        </label>
                                    </div>
                                    <div class="col-span-1 text-right">
                                        <button type="button" @click="removeDay(i)"
                                                class="text-red-400 hover:text-red-600 text-sm leading-none">&times;</button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="form.days.length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">
                                Cliquez sur "+ Ajouter un jour" pour configurer les jours travaillés.
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Colonnes : Jour · Début · Fin · Pause (min) · Multiplicateur · Heure sup éligible
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="modal=false"
                                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            Annuler
                        </button>
                        <button type="submit" :disabled="saving"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg flex items-center gap-2">
                            <svg x-show="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span x-text="saving ? 'Enregistrement...' : 'Enregistrer'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function scheduleIndex() {
        return {
            modal: false, editing: false, saving: false,
            form: { name:'', description:'', start_date:'', end_date:'', department_id:'', color:'#3B82F6', is_active:true, days:[] },
            editId: null,

            init() {},

            defaultDay() {
                return { day_of_week: 1, start_time: '08:00', end_time: '17:00', break_minutes: 60, is_overtime_eligible: false, multiplier: 1.00 };
            },

            addDay() { this.form.days.push(this.defaultDay()); },
            removeDay(i) { this.form.days.splice(i, 1); },

            openCreate() {
                this.editing = false;
                this.editId  = null;
                this.form    = { name:'', description:'', start_date:'', end_date:'', department_id:'', color:'#3B82F6', is_active:true, days:[] };
                this.addDay();
                this.modal = true;
            },

            openEdit(schedule) {
                this.editing = true;
                this.editId  = schedule.id;
                this.form    = {
                    name:          schedule.name,
                    description:   schedule.description ?? '',
                    start_date:    schedule.start_date,
                    end_date:      schedule.end_date ?? '',
                    department_id: schedule.department_id ?? '',
                    color:         schedule.color ?? '#3B82F6',
                    is_active:     schedule.is_active,
                    days:          schedule.days ?? [],
                };
                this.modal = true;
            },

            async save() {
                this.saving = true;
                const url    = this.editing ? `/shifts/schedules/${this.editId}` : '/shifts/schedules';
                const method = this.editing ? 'PUT' : 'POST';
                try {
                    const res = await fetch(url, {
                        method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(this.form),
                    });
                    if (!res.ok) throw await res.json();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'success', message: this.editing ? 'Horaire mis à jour.' : 'Horaire créé.' }}));
                    this.modal = false;
                    setTimeout(() => location.reload(), 600);
                } catch (e) {
                    const msg = e?.message || (e?.errors ? Object.values(e.errors).flat().join(' ') : 'Erreur');
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'error', message: msg }}));
                } finally {
                    this.saving = false;
                }
            },

            async deleteSchedule(id) {
                if (!confirm('Supprimer cet horaire ?')) return;
                const res = await fetch(`/shifts/schedules/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                if (res.ok) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type:'success', message:'Horaire supprimé.' }}));
                    setTimeout(() => location.reload(), 600);
                }
            },
        }
    }
    </script>
    @endpush
</x-app-layout>
