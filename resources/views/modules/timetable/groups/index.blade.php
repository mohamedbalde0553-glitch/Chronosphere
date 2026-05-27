<x-app-layout>
    <x-slot name="title">Groupes</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('timetable.groups.store') }}',
        updateBase:'{{ url('timetable/groups/__ID__') }}',
        deleteBase:'{{ url('timetable/groups/__ID__') }}',
        emptyForm: () => ({ name:'', code:'', level_id:'', academic_year_id:'', capacity:30, parent_id:'' }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Groupes</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $groups->total() }} groupe(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('timetable.index') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">+ Ajouter</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Niveau</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Année académique</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Capacité</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Groupe parent</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($groups as $group)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-mono font-semibold text-amber-700 dark:text-amber-400">{{ $group->code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $group->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            {{ $group->level?->name ?? '—' }}
                            @if($group->level?->faculty)
                                <span class="text-gray-400 dark:text-gray-500">· {{ $group->level->faculty->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $group->academicYear?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $group->capacity }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $group->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $group }})" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $group->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucun groupe enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($groups->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $groups->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="editMode ? 'Modifier le groupe' : 'Nouveau groupe'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                        <input type="text" x-model="form.name" placeholder="Groupe A"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.name ? 'border-red-400' : ''">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                        <input type="text" x-model="form.code" placeholder="L1-INFO-A"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.code ? 'border-red-400' : ''">
                        <p x-show="errors.code" x-text="errors.code" class="mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Niveau *</label>
                    <select x-model="form.level_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="errors.level_id ? 'border-red-400' : ''">
                        <option value="">— Sélectionner —</option>
                        @foreach($levels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }}@if($level->faculty) ({{ $level->faculty->name }})@endif</option>
                        @endforeach
                    </select>
                    <p x-show="errors.level_id" x-text="errors.level_id" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Année académique *</label>
                    <select x-model="form.academic_year_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="errors.academic_year_id ? 'border-red-400' : ''">
                        <option value="">— Sélectionner —</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                    <p x-show="errors.academic_year_id" x-text="errors.academic_year_id" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacité *</label>
                        <input type="number" x-model="form.capacity" min="1" max="200"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               :class="errors.capacity ? 'border-red-400' : ''">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Groupe parent</label>
                        <select x-model="form.parent_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Aucun —</option>
                            @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

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
