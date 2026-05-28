<x-app-layout>
    <x-slot name="title">Grille de planning</x-slot>

    @push('styles')
    <style>
        .fc { font-family: 'Inter', sans-serif; }
        .fc-toolbar-title { font-size: 1rem !important; font-weight: 600; }
        .fc-button { border-radius: 0.5rem !important; font-size: 0.78rem !important; font-weight: 500 !important; }
        .fc-button-primary { background-color: #059669 !important; border-color: #059669 !important; }
        .fc-button-primary:hover { background-color: #047857 !important; }
        .fc-button-active { background-color: #047857 !important; }
        .fc-event { border-radius: 3px !important; font-size: 0.75rem; cursor: pointer; }
        .fc-timegrid-slot { height: 2.5rem; }
        .fc-col-header-cell-cushion { font-size: 0.78rem; font-weight: 600; }
        .dark .fc { color: #e5e7eb; }
        .dark .fc-scrollgrid, .dark .fc-scrollgrid td, .dark .fc-scrollgrid th { border-color: #374151 !important; }
        .dark .fc-col-header-cell { background: #1f2937; }
        .dark .fc-timegrid-slot-lane { background: #111827; }
        .dark .fc-daygrid-day { background: #111827; }
        .dark .fc-day-today { background: #1e3a2e !important; }
        .dark .fc-toolbar-title { color: #f9fafb; }
        .dark .fc-col-header-cell-cushion { color: #9ca3af; }
        .dark .fc-timegrid-axis-cushion { color: #9ca3af; }
        .dark .fc-daygrid-day-number { color: #9ca3af; }
    </style>
    @endpush

    <div x-data="planningApp()" class="space-y-4">

        {{-- Toolbar --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-500 dark:text-gray-400 font-medium">Voir par :</span>
                <select x-model="filterType" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <option value="department">Département</option>
                    <option value="employee">Employé</option>
                </select>
            </div>

            <div x-show="filterType === 'department'">
                <select x-model="filterId" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ $filterId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="filterType === 'employee'">
                <select x-model="filterId" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    @foreach($employees as $e)
                    <option value="{{ $e->id }}" {{ $filterId == $e->id ? 'selected' : '' }}>
                        {{ $e->user->name }}@if($e->department) ({{ $e->department->name }})@endif
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('shifts.index') }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    ← Retour
                </a>
                <button @click="openNewShift()"
                        class="flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un shift
                </button>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div id="cs-shifts"
                 data-feed-url="{{ route('shifts.planning.feed') }}"
                 data-filter-type="{{ $filterType }}"
                 data-filter-id="{{ $filterId }}">
            </div>
        </div>

        {{-- Weekly limit warning --}}
        <div x-show="warnings.length > 0" x-cloak
             class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-700">Avertissements</p>
                    <ul class="mt-1 space-y-1">
                        <template x-for="w in warnings">
                            <li class="text-sm text-amber-600" x-text="w.message"></li>
                        </template>
                    </ul>
                    <button @click="warnings=[]" class="mt-2 text-xs text-amber-500 hover:underline">Ignorer</button>
                </div>
            </div>
        </div>

        {{-- Conflict alert --}}
        <div x-show="conflicts.length > 0" x-cloak
             class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-red-700">Conflits détectés</p>
                    <ul class="mt-1 space-y-1">
                        <template x-for="c in conflicts">
                            <li class="text-sm text-red-600" x-text="c.message"></li>
                        </template>
                    </ul>
                    <div class="mt-3 flex gap-3">
                        <button @click="forceSave()" class="px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Forcer l'enregistrement
                        </button>
                        <button @click="conflicts=[]" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-100 rounded-lg">Annuler</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Shift modal --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md z-10" @click.stop>

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                        x-text="editMode ? 'Modifier le shift' : 'Nouveau shift'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employé <span class="text-red-500">*</span></label>
                        <select x-model="form.employee_id"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                :class="errors.employee_id ? 'border-red-400' : ''">
                            <option value="">— Sélectionner —</option>
                            @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->user->name }}@if($e->department) — {{ $e->department->name }}@endif</option>
                            @endforeach
                        </select>
                        <p x-show="errors.employee_id" x-text="errors.employee_id" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de shift</label>
                        <select x-model="form.shift_type_id"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">— Aucun —</option>
                            @foreach($shiftTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->start_time }}–{{ $st->end_time }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Début</label>
                            <input type="datetime-local" x-model="form.start_at"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   :class="errors.start_at ? 'border-red-400' : ''">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fin</label>
                            <input type="datetime-local" x-model="form.end_at"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   :class="errors.end_at ? 'border-red-400' : ''">
                        </div>
                    </div>
                    <p x-show="errors.start_at" x-text="errors.start_at" class="text-xs text-red-600"></p>
                    <p x-show="errors.end_at" x-text="errors.end_at" class="text-xs text-red-600"></p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <input type="text" x-model="form.notes" placeholder="Optionnel…"
                               class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <button x-show="editMode" @click="deleteShift()"
                            class="text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 px-3 py-2 rounded-lg">Supprimer</button>
                    <div class="flex gap-3 ml-auto">
                        <button @click="closeModal()" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Annuler</button>
                        <button @click="saveShift()" :disabled="loading"
                                class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-60">
                            <span x-show="!loading">Enregistrer</span>
                            <span x-show="loading">…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        window.csShiftUrls = {
            store:      '{{ route('shifts.shifts.store') }}',
            updateBase: '{{ url('shifts/shifts/__ID__') }}',
            deleteBase: '{{ url('shifts/shifts/__ID__') }}',
        };

        function planningApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            return {
                filterType:  '{{ $filterType }}',
                filterId:    '{{ $filterId }}',
                showModal:   false,
                editMode:    false,
                loading:     false,
                shiftId:     null,
                pendingData: null,
                conflicts:   [],
                warnings:    [],
                errors:      {},
                form: {
                    employee_id:   '',
                    shift_type_id: '',
                    start_at:      '',
                    end_at:        '',
                    notes:         '',
                },

                applyFilter() {
                    if (window.csShifts) {
                        const src = window.csShifts.getEventSources()[0];
                        src.remove();
                        window.csShifts.addEventSource({
                            url:         document.getElementById('cs-shifts').dataset.feedUrl,
                            extraParams: { by: this.filterType, id: this.filterId },
                        });
                    }
                },

                openNewShift(start, end) {
                    this.editMode  = false;
                    this.shiftId   = null;
                    this.errors    = {};
                    this.conflicts = [];
                    this.form = { employee_id:'', shift_type_id:'', start_at: start??'', end_at: end??'', notes:'' };
                    this.showModal = true;
                },

                openEditShift(detail) {
                    this.editMode  = true;
                    this.shiftId   = detail.id;
                    this.errors    = {};
                    this.conflicts = [];
                    this.form = {
                        employee_id:   String(detail.employee_id),
                        shift_type_id: detail.shift_type_id ? String(detail.shift_type_id) : '',
                        start_at:      detail.start.substring(0, 16),
                        end_at:        detail.end.substring(0, 16),
                        notes:         detail.notes ?? '',
                    };
                    this.showModal = true;
                },

                closeModal() { this.showModal = false; this.conflicts = []; this.warnings = []; },

                async saveShift(force = false) {
                    this.errors  = {};
                    this.loading = true;
                    const url    = this.editMode
                        ? window.csShiftUrls.updateBase.replace('__ID__', this.shiftId)
                        : window.csShiftUrls.store;
                    const method = this.editMode ? 'PUT' : 'POST';
                    const body   = force ? { ...this.form, force: true } : this.form;

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body:    JSON.stringify(body),
                        });
                        if (res.status === 422) {
                            const d  = await res.json();
                            this.errors = Object.fromEntries(
                                Object.entries(d.errors ?? {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                            );
                            return;
                        }
                        if (res.status === 409) {
                            const d = await res.json();
                            this.pendingData = body;
                            this.conflicts   = d.conflicts ?? [];
                            this.showModal   = false;
                            return;
                        }
                        if (!res.ok) throw new Error();
                        const d = await res.json().catch(() => ({}));
                        if (d.warnings?.length) { this.warnings = d.warnings; }
                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('cs:refresh-shifts'));
                    } catch(e) { console.error(e); }
                    finally { this.loading = false; }
                },

                async forceSave() {
                    this.showModal = true;
                    await this.saveShift(true);
                },

                async deleteShift() {
                    if (!confirm('Supprimer ce shift ?')) return;
                    this.loading = true;
                    try {
                        const res = await fetch(
                            window.csShiftUrls.deleteBase.replace('__ID__', this.shiftId),
                            { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } }
                        );
                        if (!res.ok) throw new Error();
                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('cs:refresh-shifts'));
                    } catch(e) { console.error(e); }
                    finally { this.loading = false; }
                },
            };
        }
    </script>
    @vite('resources/js/shifts.js')
    @endpush
</x-app-layout>
