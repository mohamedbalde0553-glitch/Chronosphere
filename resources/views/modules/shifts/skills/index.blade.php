<x-app-layout>
    <x-slot name="title">Compétences</x-slot>

    <div x-data="skillsApp()">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Compétences</h2>
                <p class="text-sm text-gray-500">{{ $skills->count() }} compétence(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shifts.index') }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 bg-white rounded-lg hover:bg-gray-50">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        {{-- Grille par catégorie --}}
        @php $grouped = $skills->groupBy('category'); @endphp
        @forelse($grouped as $cat => $items)
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                {{ $cat ?: 'Sans catégorie' }}
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($items as $skill)
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-sm font-semibold text-gray-800 leading-tight">{{ $skill->name }}</span>
                        <div class="flex gap-1 shrink-0">
                            <button @click="openEdit({{ $skill }})"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="deleteItem({{ $skill->id }})"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $skill->employees_count }} employé(s)</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400 text-sm">Aucune compétence. Ajoutez-en une.</p>
        </div>
        @endforelse

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="editMode ? 'Modifier la compétence' : 'Nouvelle compétence'"></h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" x-model="form.name" placeholder="ex: SQL, Java, Gestion de projet…"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           :class="errors.name ? 'border-red-400' : ''">
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <input type="text" x-model="form.category" placeholder="ex: Technique, Management…"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-60">
                        <span x-show="!loading">Enregistrer</span><span x-show="loading">…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const skillUrls = {
            store:      '{{ route('shifts.skills.store') }}',
            updateBase: '{{ url('shifts/skills/__ID__') }}',
            deleteBase: '{{ url('shifts/skills/__ID__') }}',
        };

        function skillsApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const empty = () => ({ name: '', category: '' });
            return {
                showModal: false,
                editMode:  false,
                loading:   false,
                itemId:    null,
                form:      empty(),
                errors:    {},

                openCreate() { this.editMode = false; this.itemId = null; this.errors = {}; this.form = empty(); this.showModal = true; },
                openEdit(item) { this.editMode = true; this.itemId = item.id; this.errors = {}; this.form = { name: item.name, category: item.category ?? '' }; this.showModal = true; },
                closeModal() { this.showModal = false; },

                async save() {
                    this.errors = {}; this.loading = true;
                    const url    = this.editMode ? skillUrls.updateBase.replace('__ID__', this.itemId) : skillUrls.store;
                    const method = this.editMode ? 'PUT' : 'POST';
                    try {
                        const res = await fetch(url, {
                            method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify(this.form),
                        });
                        if (res.status === 422) { const d = await res.json(); this.errors = Object.fromEntries(Object.entries(d.errors ?? {}).map(([k,v]) => [k, Array.isArray(v) ? v[0] : v])); return; }
                        if (!res.ok) throw new Error();
                        this.closeModal();
                        window.location.reload();
                    } catch(e) { console.error(e); } finally { this.loading = false; }
                },

                async deleteItem(id) {
                    if (!confirm('Supprimer cette compétence ?')) return;
                    try {
                        const res = await fetch(skillUrls.deleteBase.replace('__ID__', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
                        if (!res.ok) throw new Error();
                        window.location.reload();
                    } catch(e) { console.error(e); }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
