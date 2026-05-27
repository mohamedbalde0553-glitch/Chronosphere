<x-app-layout>
    <x-slot name="title">Ressources</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('booking.resources.store') }}',
        updateBase:'{{ url('booking/resources/__ID__') }}',
        deleteBase:'{{ url('booking/resources/__ID__') }}',
        emptyForm: () => ({ category_id:'', name:'', description:'', capacity:10, location:'', color:'#EA580C', is_active:true, requires_approval:false, advance_booking_days:30, max_booking_duration_minutes:480 }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Ressources</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $resources->total() }} ressource(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('booking.index') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 w-8"></th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Catégorie</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Lieu</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Capacité</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Approbation</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($resources as $res)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-sm" style="background:{{ $res->color ?? '#EA580C' }}"></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $res->name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $res->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $res->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $res->capacity }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($res->requires_approval)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Requise</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Auto</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($res->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Actif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $res }})" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $res->id }})" class="text-xs text-red-500 dark:text-red-400 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucune ressource enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($resources->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $resources->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg z-10 p-6 space-y-4 max-h-[90vh] overflow-y-auto" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="editMode ? 'Modifier la ressource' : 'Nouvelle ressource'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                        <input type="text" x-model="form.name" placeholder="Salle de réunion A"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                               :class="errors.name ? 'border-red-400' : ''">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                        <select x-model="form.category_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">— Aucune —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lieu</label>
                        <input type="text" x-model="form.location" placeholder="Bâtiment A, 2e étage"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacité *</label>
                        <input type="number" x-model="form.capacity" min="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                               :class="errors.capacity ? 'border-red-400' : ''">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur</label>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="form.color"
                               class="w-10 h-9 rounded border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                        <input type="text" x-model="form.color"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Réservation max. (min) *</label>
                        <input type="number" x-model="form.max_booking_duration_minutes" min="30" step="30"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Ex: 480 = 8h</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Réservation à l'avance (j) *</label>
                        <input type="number" x-model="form.advance_booking_days" min="1" max="365"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea x-model="form.description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"></textarea>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 dark:border-gray-600 text-orange-600">
                        Ressource active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" x-model="form.requires_approval" class="rounded border-gray-300 dark:border-gray-600 text-orange-600">
                        Approbation requise
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg disabled:opacity-60 inline-flex items-center gap-2">
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
    @include('modules.timetable._crud-script')
    @endpush
</x-app-layout>
