<x-app-layout>
    <x-slot name="title">Salles</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('timetable.rooms.store') }}',
        updateBase:'{{ url('timetable/rooms/__ID__') }}',
        deleteBase:'{{ url('timetable/rooms/__ID__') }}',
        emptyForm: () => ({ code:'', name:'', capacity:30, type:'td', building:'', floor:'', is_active:true }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Salles</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $rooms->total() }} salle(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('timetable.index') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Type</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Capacité</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Bâtiment</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono font-semibold text-blue-700 dark:text-blue-400">{{ $room->code }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $room->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ strtoupper($room->type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $room->capacity }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $room->building ?? '—' }} {{ $room->floor ? 'Ét.'.$room->floor : '' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($room->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Actif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $room }})" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $room->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucune salle enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($rooms->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $rooms->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="editMode ? 'Modifier la salle' : 'Nouvelle salle'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                        <input type="text" x-model="form.code" placeholder="TD-101"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.code ? 'border-red-400':''">
                        <p x-show="errors.code" x-text="errors.code" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                        <select x-model="form.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="td">Salle TD</option>
                            <option value="amphi">Amphithéâtre</option>
                            <option value="tp">Salle TP</option>
                            <option value="labo">Laboratoire</option>
                            <option value="info">Salle Info</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input type="text" x-model="form.name" placeholder="Salle de travaux dirigés 101"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="errors.name ? 'border-red-400':''">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacité *</label>
                        <input type="number" x-model="form.capacity" min="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bâtiment</label>
                        <input type="text" x-model="form.building" placeholder="A"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Étage</label>
                        <input type="text" x-model="form.floor" placeholder="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 dark:border-gray-600 text-blue-600">
                    Salle active
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-60 inline-flex items-center gap-2">
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
