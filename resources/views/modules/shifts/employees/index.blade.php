<x-app-layout>
    <x-slot name="title">Employés</x-slot>

    <div x-data="employeesApp({
        storeUrl:  '{{ route('shifts.employees.store') }}',
        updateBase:'{{ url('shifts/employees/__ID__') }}',
        deleteBase:'{{ url('shifts/employees/__ID__') }}',
    })">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Employés</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $employees->total() }} employé(s) enregistré(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shifts.index') }}"
                   class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    ← Retour
                </a>
                <button @click="openCreate()"
                        class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </button>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 mb-5 flex flex-wrap gap-3 items-center">
            <input type="text" x-model="search" placeholder="Rechercher…"
                   class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 w-48">
            <select x-model="filterDept"
                    class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <option value="">Tous les départements</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <select x-model="filterStatus"
                    class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
                <option value="suspended">Suspendu</option>
            </select>
            <span class="ml-auto text-xs text-gray-400 dark:text-gray-500" x-text="filtered.length + ' résultat(s)'"></span>
        </div>

        {{-- Trombinoscope --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="emp in filtered" :key="emp.id">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col card-hover cursor-pointer"
                     @click="window.location.href = '{{ url('shifts/employees') }}/' + emp.id">

                    {{-- Color bar by department --}}
                    <div class="h-1.5 bg-emerald-500"></div>

                    <div class="p-5 flex flex-col flex-1">

                        {{-- Avatar + status --}}
                        <div class="flex items-start gap-3 mb-3">
                            <img :src="emp.photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(emp.user?.name ?? 'E')}&background=047857&color=fff&size=80`"
                                 class="w-14 h-14 rounded-full object-cover border-2 border-emerald-100 dark:border-emerald-900/50 shrink-0"
                                 :alt="emp.user?.name">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white truncate" x-text="emp.user?.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="emp.position?.title ?? emp.position?.name ?? '—'"></p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-mono mt-0.5" x-text="emp.employee_code"></p>
                            </div>
                        </div>

                        {{-- Infos --}}
                        <div class="space-y-1.5 flex-1">
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                </svg>
                                <span x-text="emp.department?.name ?? '—'" class="truncate"></span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span x-text="emp.hire_date ? 'Depuis ' + new Date(emp.hire_date).toLocaleDateString('fr-FR', {year:'numeric',month:'short'}) : '—'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="(emp.weekly_hours_minutes / 60).toFixed(1) + 'h/sem'"></span>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex gap-1.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300': emp.status === 'active',
                                          'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400':               emp.status === 'inactive',
                                          'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400':                 emp.status === 'suspended',
                                      }"
                                      x-text="emp.status === 'active' ? 'Actif' : emp.status === 'inactive' ? 'Inactif' : 'Suspendu'">
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300':     emp.contract_type === 'cdi',
                                          'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300': emp.contract_type === 'cdd',
                                          'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300': emp.contract_type === 'interim',
                                          'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300': emp.contract_type === 'freelance',
                                      }"
                                      x-text="emp.contract_type?.toUpperCase()">
                                </span>
                            </div>
                            <div class="flex gap-1">
                                <a :href="`{{ url('shifts/employees') }}/${emp.id}`"
                                   class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors"
                                   title="Voir la fiche">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <button @click.stop="openEdit(emp)"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click.stop="deleteItem(emp.id)"
                                        class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="filtered.length === 0">
                <div class="col-span-full py-16 text-center">
                    <p class="text-gray-400 dark:text-gray-500 text-sm">Aucun employé ne correspond aux filtres.</p>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        @if($employees->hasPages())
        <div class="mt-6">{{ $employees->links() }}</div>
        @endif

        {{-- Modal créer / modifier --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg z-10 flex flex-col max-h-[90vh]" @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                        x-text="editMode ? 'Modifier l\'employé' : 'Nouvel employé'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-4 overflow-y-auto">

                    <div x-show="!editMode">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Utilisateur *</label>
                        <select x-model="form.user_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                :class="errors.user_id ? 'border-red-400' : ''">
                            <option value="">— Sélectionner —</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <p x-show="errors.user_id" x-text="errors.user_id" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code employé *</label>
                            <input type="text" x-model="form.employee_code" placeholder="EMP-001"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   :class="errors.employee_code ? 'border-red-400' : ''">
                            <p x-show="errors.employee_code" x-text="errors.employee_code" class="mt-1 text-xs text-red-600"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date d'embauche *</label>
                            <input type="date" x-model="form.hire_date"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   :class="errors.hire_date ? 'border-red-400' : ''">
                            <p x-show="errors.hire_date" x-text="errors.hire_date" class="mt-1 text-xs text-red-600"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Département *</label>
                            <select x-model="form.department_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    :class="errors.department_id ? 'border-red-400' : ''">
                                <option value="">— Sélectionner —</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <p x-show="errors.department_id" x-text="errors.department_id" class="mt-1 text-xs text-red-600"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Poste *</label>
                            <select x-model="form.position_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    :class="errors.position_id ? 'border-red-400' : ''">
                                <option value="">— Sélectionner —</option>
                                @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                @endforeach
                            </select>
                            <p x-show="errors.position_id" x-text="errors.position_id" class="mt-1 text-xs text-red-600"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contrat *</label>
                            <select x-model="form.contract_type"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="cdi">CDI</option>
                                <option value="cdd">CDD</option>
                                <option value="interim">Intérim</option>
                                <option value="freelance">Freelance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">H/semaine (min) *</label>
                            <input type="number" x-model="form.weekly_hours_minutes" min="60" max="3600" step="30"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Ex: 2400 = 40h</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL Photo</label>
                        <input type="url" x-model="form.photo_url" placeholder="https://…"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Laisser vide pour utiliser l'avatar généré automatiquement.</p>
                    </div>

                    <div x-show="editMode">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                        <select x-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shrink-0">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:text-gray-300 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-60 transition-colors inline-flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-show="!loading">Enregistrer</span>
                        <span x-show="loading">Enregistrement…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const _employees = @json($employees->items());

        function employeesApp({ storeUrl, updateBase, deleteBase }) {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const empty = () => ({ user_id:'', department_id:'', position_id:'', employee_code:'', hire_date:'', contract_type:'cdi', weekly_hours_minutes: 2400, status:'active', photo_url:'' });

            return {
                all:          _employees,
                search:       '',
                filterDept:   '',
                filterStatus: '',
                showModal:    false,
                editMode:     false,
                loading:      false,
                itemId:       null,
                form:         empty(),
                errors:       {},

                get filtered() {
                    return this.all.filter(e => {
                        const name = (e.user?.name ?? '').toLowerCase();
                        const code = (e.employee_code ?? '').toLowerCase();
                        const q    = this.search.toLowerCase();
                        if (q && !name.includes(q) && !code.includes(q)) return false;
                        if (this.filterDept   && String(e.department_id) !== String(this.filterDept))   return false;
                        if (this.filterStatus && e.status !== this.filterStatus) return false;
                        return true;
                    });
                },

                openCreate() { this.editMode = false; this.itemId = null; this.errors = {}; this.form = empty(); this.showModal = true; },
                openEdit(emp) {
                    this.editMode = true; this.itemId = emp.id; this.errors = {};
                    this.form = {
                        user_id: String(emp.user_id), department_id: String(emp.department_id), position_id: String(emp.position_id),
                        employee_code: emp.employee_code, hire_date: emp.hire_date?.substring(0,10) ?? '',
                        contract_type: emp.contract_type, weekly_hours_minutes: emp.weekly_hours_minutes,
                        status: emp.status, photo_url: emp.photo_url ?? '',
                    };
                    this.showModal = true;
                },
                closeModal() { this.showModal = false; },

                async save() {
                    this.errors = {}; this.loading = true;
                    const url    = this.editMode ? updateBase.replace('__ID__', this.itemId) : storeUrl;
                    const method = this.editMode ? 'PUT' : 'POST';
                    try {
                        const res = await fetch(url, {
                            method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify(this.form),
                        });
                        if (res.status === 422) {
                            const d = await res.json();
                            this.errors = Object.fromEntries(Object.entries(d.errors ?? {}).map(([k,v]) => [k, Array.isArray(v) ? v[0] : v]));
                            return;
                        }
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Employé enregistré',type:'success'}}));
                        this.closeModal();
                        setTimeout(() => window.location.reload(), 600);
                    } catch(e) {
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de la sauvegarde',type:'error'}}));
                    } finally { this.loading = false; }
                },

                async deleteItem(id) {
                    if (!confirm('Supprimer cet employé ?')) return;
                    try {
                        const res = await fetch(deleteBase.replace('__ID__', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Employé supprimé',type:'success'}}));
                        this.all = this.all.filter(e => e.id !== id);
                    } catch(e) {
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de la suppression',type:'error'}}));
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
