<x-app-layout>
    <x-slot name="title">Types de shifts</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('shifts.shift-types.store') }}',
        updateBase:'{{ url('shifts/shift-types/__ID__') }}',
        deleteBase:'{{ url('shifts/shift-types/__ID__') }}',
        emptyForm: () => ({ name:'', start_time:'08:00', end_time:'16:00', color:'#059669', is_night:false, overtime_multiplier:1.5 }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Types de shifts</h2>
                <p class="text-sm text-gray-500">{{ $shiftTypes->total() }} type(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shifts.index') }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 bg-white rounded-lg hover:bg-gray-50">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Couleur</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Nom</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Début</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Fin</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Nuit</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Mult. HS</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shiftTypes as $type)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="inline-block w-6 h-6 rounded-md border border-gray-200"
                                  style="background: {{ $type->color ?? '#059669' }}"></span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $type->name }}</td>
                        <td class="px-4 py-3 text-center font-mono text-gray-700">{{ $type->start_time }}</td>
                        <td class="px-4 py-3 text-center font-mono text-gray-700">{{ $type->end_time }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($type->is_night)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">Nuit</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">× {{ $type->overtime_multiplier }}</td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $type }})" class="text-xs text-blue-600 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $type->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun type de shift enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($shiftTypes->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $shiftTypes->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="editMode ? 'Modifier le type' : 'Nouveau type de shift'"></h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" x-model="form.name" placeholder="Matin, Après-midi, Nuit…"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           :class="errors.name ? 'border-red-400' : ''">
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure début *</label>
                        <input type="time" x-model="form.start_time"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.start_time ? 'border-red-400' : ''">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure fin *</label>
                        <input type="time" x-model="form.end_time"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.end_time ? 'border-red-400' : ''">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="form.color"
                                   class="w-10 h-9 rounded border border-gray-300 cursor-pointer p-0.5">
                            <input type="text" x-model="form.color"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mult. heures sup. *</label>
                        <input type="number" x-model="form.overtime_multiplier" min="1" max="3" step="0.25"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" x-model="form.is_night" class="rounded border-gray-300 text-emerald-600">
                    Shift de nuit
                </label>

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
    @include('modules.timetable._crud-script')
    @endpush
</x-app-layout>
