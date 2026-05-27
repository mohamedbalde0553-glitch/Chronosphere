<x-app-layout>
    <x-slot name="title">Enseignants</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('timetable.teachers.store') }}',
        updateBase:'{{ url('timetable/teachers/__ID__') }}',
        deleteBase:'{{ url('timetable/teachers/__ID__') }}',
        emptyForm: () => ({ user_id:'', employee_code:'', title:'', speciality:'', contract_type:'permanent', is_active:true }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Enseignants</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $teachers->total() }} enseignant(s)</p>
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
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Titre</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Spécialité</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Contrat</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $teacher->user->name }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $teacher->user->email }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono font-semibold text-rose-700 dark:text-rose-400">{{ $teacher->employee_code }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $teacher->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $teacher->speciality ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $contractLabels = ['permanent'=>'Permanent','contractuel'=>'Contractuel','vacataire'=>'Vacataire'];
                                $contractColors = ['permanent'=>'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300','contractuel'=>'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300','vacataire'=>'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'];
                                $ct = $teacher->contract_type ?? 'permanent';
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $contractColors[$ct] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $contractLabels[$ct] ?? $ct }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($teacher->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Actif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $teacher }})" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $teacher->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucun enseignant enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($teachers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $teachers->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="editMode ? 'Modifier l\'enseignant' : 'Nouvel enseignant'"></h3>

                <div x-show="!editMode">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Utilisateur *</label>
                    <select x-model="form.user_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="errors.user_id ? 'border-red-400' : ''">
                        <option value="">— Sélectionner un utilisateur —</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <p x-show="errors.user_id" x-text="errors.user_id" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code employé *</label>
                    <input type="text" x-model="form.employee_code" placeholder="EMP-2024-001"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="errors.employee_code ? 'border-red-400' : ''">
                    <p x-show="errors.employee_code" x-text="errors.employee_code" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre</label>
                        <input type="text" x-model="form.title" placeholder="Dr., Pr., …"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de contrat</label>
                        <select x-model="form.contract_type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="permanent">Permanent</option>
                            <option value="contractuel">Contractuel</option>
                            <option value="vacataire">Vacataire</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Spécialité</label>
                    <input type="text" x-model="form.speciality" placeholder="Informatique, Mathématiques…"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div x-show="editMode">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 dark:border-gray-600 text-blue-600">
                        Enseignant actif
                    </label>
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
