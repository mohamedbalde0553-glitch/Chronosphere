<x-app-layout>
    <x-slot name="title">Salles</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('timetable.rooms.store') }}',
        updateBase:'{{ url('timetable/rooms/__ID__') }}',
        deleteBase:'{{ url('timetable/rooms/__ID__') }}',
        emptyForm: () => ({ code:'', name:'', capacity:30, type:'td', building:'', floor:'', is_active:true }),
    })">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Salles</h2>
                <p class="text-sm text-gray-500">{{ $rooms->total() }} salle(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('timetable.index') }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 bg-white rounded-lg hover:bg-gray-50">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Type</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Capacité</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Bâtiment</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-semibold text-blue-700">{{ $room->code }}</td>
                        <td class="px-4 py-3 text-gray-900">{{ $room->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">{{ strtoupper($room->type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $room->capacity }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $room->building ?? '—' }} {{ $room->floor ? 'Ét.'.$room->floor : '' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($room->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Actif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $room }})" class="text-xs text-blue-600 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $room->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucune salle enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($rooms->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $rooms->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900" x-text="editMode ? 'Modifier la salle' : 'Nouvelle salle'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                        <input type="text" x-model="form.code" placeholder="TD-101"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.code ? 'border-red-400':''">
                        <p x-show="errors.code" x-text="errors.code" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select x-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" x-model="form.name" placeholder="Salle de travaux dirigés 101"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="errors.name ? 'border-red-400':''">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacité *</label>
                        <input type="number" x-model="form.capacity" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bâtiment</label>
                        <input type="text" x-model="form.building" placeholder="A"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Étage</label>
                        <input type="text" x-model="form.floor" placeholder="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 text-blue-600">
                    Salle active
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-60">
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
