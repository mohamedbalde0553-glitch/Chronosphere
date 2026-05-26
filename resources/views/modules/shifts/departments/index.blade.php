<x-app-layout>
    <x-slot name="title">Départements</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('shifts.departments.store') }}',
        updateBase:'{{ url('shifts/departments/__ID__') }}',
        deleteBase:'{{ url('shifts/departments/__ID__') }}',
        emptyForm: () => ({ name:'', code:'', color:'#059669', description:'', manager_id:'', parent_id:'' }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Départements</h2>
                <p class="text-sm text-gray-500">{{ $departments->total() }} département(s)</p>
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
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Responsable</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Parent</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $dept)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="inline-block w-6 h-6 rounded-md border border-gray-200"
                                  style="background: {{ $dept->color ?? '#059669' }}"></span>
                        </td>
                        <td class="px-4 py-3 font-mono font-semibold text-blue-700">{{ $dept->code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $dept->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $dept->manager?->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $dept->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 truncate max-w-xs">{{ $dept->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $dept }})" class="text-xs text-blue-600 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $dept->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun département enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($departments->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $departments->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="editMode ? 'Modifier le département' : 'Nouveau département'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" x-model="form.name" placeholder="Informatique"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.name ? 'border-red-400' : ''">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                        <input type="text" x-model="form.code" placeholder="IT"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.code ? 'border-red-400' : ''">
                        <p x-show="errors.code" x-text="errors.code" class="mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <select x-model="form.manager_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Aucun —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Département parent</label>
                    <select x-model="form.parent_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Aucun —</option>
                        @foreach($allDepts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
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
    @include('modules.timetable._crud-script')
    @endpush
</x-app-layout>
