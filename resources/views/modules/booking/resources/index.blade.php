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
                <h2 class="text-lg font-bold text-gray-900">Ressources</h2>
                <p class="text-sm text-gray-500">{{ $resources->total() }} ressource(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('booking.index') }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 bg-white rounded-lg hover:bg-gray-50">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 w-8"></th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Catégorie</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Lieu</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Capacité</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Approbation</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($resources as $res)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="inline-block w-4 h-4 rounded-sm" style="background:{{ $res->color ?? '#EA580C' }}"></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $res->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $res->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $res->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $res->capacity }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($res->requires_approval)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Requise</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Auto</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($res->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Actif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $res }})" class="text-xs text-blue-600 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $res->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucune ressource enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($resources->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $resources->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10 p-6 space-y-4 max-h-[90vh] overflow-y-auto" @click.stop>
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="editMode ? 'Modifier la ressource' : 'Nouvelle ressource'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" x-model="form.name" placeholder="Salle de réunion A"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                               :class="errors.name ? 'border-red-400' : ''">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select x-model="form.category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">— Aucune —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                        <input type="text" x-model="form.location" placeholder="Bâtiment A, 2e étage"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacité *</label>
                        <input type="number" x-model="form.capacity" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                               :class="errors.capacity ? 'border-red-400' : ''">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="form.color"
                               class="w-10 h-9 rounded border border-gray-300 cursor-pointer p-0.5">
                        <input type="text" x-model="form.color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Réservation max. (min) *</label>
                        <input type="number" x-model="form.max_booking_duration_minutes" min="30" step="30"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <p class="mt-0.5 text-xs text-gray-400">Ex: 480 = 8h</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Réservation à l'avance (j) *</label>
                        <input type="number" x-model="form.advance_booking_days" min="1" max="365"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 resize-none"></textarea>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 text-orange-600">
                        Ressource active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" x-model="form.requires_approval" class="rounded border-gray-300 text-orange-600">
                        Approbation requise
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg disabled:opacity-60">
                        <span x-show="!loading">Enregistrer</span><span x-show="loading">…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    @include('modules.timetable._crud-script')
    @endpush
</x-app-layout>
