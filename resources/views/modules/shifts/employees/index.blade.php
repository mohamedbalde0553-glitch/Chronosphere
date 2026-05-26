<x-app-layout>
    <x-slot name="title">Employés</x-slot>

    <div x-data="crudTable({
        storeUrl:  '{{ route('shifts.employees.store') }}',
        updateBase:'{{ url('shifts/employees/__ID__') }}',
        deleteBase:'{{ url('shifts/employees/__ID__') }}',
        emptyForm: () => ({ user_id:'', department_id:'', position_id:'', employee_code:'', hire_date:'', contract_type:'cdi', weekly_hours_minutes:2400, status:'active' }),
    })">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Employés</h2>
                <p class="text-sm text-gray-500">{{ $employees->total() }} employé(s)</p>
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
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Nom</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Département</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Poste</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Contrat</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Embauche</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $emp)
                    @php
                        $contractColors = ['cdi'=>'bg-blue-50 text-blue-700','cdd'=>'bg-amber-50 text-amber-700','interim'=>'bg-orange-50 text-orange-700','freelance'=>'bg-violet-50 text-violet-700'];
                        $statusColors   = ['active'=>'bg-emerald-50 text-emerald-700','inactive'=>'bg-gray-100 text-gray-500','suspended'=>'bg-red-50 text-red-600'];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $emp->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $emp->user->email }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono font-semibold text-emerald-700">{{ $emp->employee_code }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $emp->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $emp->position?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $contractColors[$emp->contract_type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ strtoupper($emp->contract_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $emp->hire_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$emp->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($emp->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit({{ $emp }})" class="text-xs text-blue-600 hover:text-blue-700 font-medium mr-3">Modifier</button>
                            <button @click="deleteItem({{ $emp->id }})" class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucun employé enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($employees->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $employees->links() }}</div>
            @endif
        </div>

        {{-- Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="editMode ? 'Modifier l\'employé' : 'Nouvel employé'"></h3>

                <div x-show="!editMode">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur *</label>
                    <select x-model="form.user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code employé *</label>
                        <input type="text" x-model="form.employee_code" placeholder="EMP-001"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.employee_code ? 'border-red-400' : ''">
                        <p x-show="errors.employee_code" x-text="errors.employee_code" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche *</label>
                        <input type="date" x-model="form.hire_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.hire_date ? 'border-red-400' : ''">
                        <p x-show="errors.hire_date" x-text="errors.hire_date" class="mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Département *</label>
                        <select x-model="form.department_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                :class="errors.department_id ? 'border-red-400' : ''">
                            <option value="">— Sélectionner —</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.department_id" x-text="errors.department_id" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Poste *</label>
                        <select x-model="form.position_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                :class="errors.position_id ? 'border-red-400' : ''">
                            <option value="">— Sélectionner —</option>
                            @foreach($positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.position_id" x-text="errors.position_id" class="mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de contrat *</label>
                        <select x-model="form.contract_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="cdi">CDI</option>
                            <option value="cdd">CDD</option>
                            <option value="interim">Intérim</option>
                            <option value="freelance">Freelance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">H/semaine (min) *</label>
                        <input type="number" x-model="form.weekly_hours_minutes" min="60" max="3600" step="30"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="mt-0.5 text-xs text-gray-400">Ex: 2400 = 40h</p>
                    </div>
                </div>

                <div x-show="editMode">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select x-model="form.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                        <option value="suspended">Suspendu</option>
                    </select>
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
