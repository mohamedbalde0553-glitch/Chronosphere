<x-app-layout>
    <x-slot name="title">Demandes de congés</x-slot>

    <div x-data="leavesApp()" @keydown.escape.window="closeModal()">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Demandes de congés</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $leaves->total() }} demande(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('shifts.index') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Retour</a>
                <button @click="openCreate()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg">+ Nouvelle demande</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Employé</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Type</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Début</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Fin</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Motif</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($leaves as $leave)
                    @php
                        $statusColors = [
                            'pending'  => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
                            'approved' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
                            'rejected' => 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                        ];
                        $statusLabels = ['pending'=>'En attente','approved'=>'Approuvé','rejected'=>'Rejeté'];
                        $typeLabels   = ['conge_paye'=>'Congé payé','rtt'=>'RTT','maladie'=>'Maladie','sans_solde'=>'Sans solde','autre'=>'Autre'];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $leave->employee->user->name }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $typeLabels[$leave->type] ?? $leave->type }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $leave->start_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $leave->end_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $leave->reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$leave->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $statusLabels[$leave->status] ?? $leave->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($leave->status === 'pending')
                            <button @click="approve({{ $leave->id }})"
                                    class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium mr-2">Approuver</button>
                            <button @click="openReject({{ $leave->id }})"
                                    class="text-xs text-red-500 hover:text-red-600 font-medium mr-3">Rejeter</button>
                            @endif
                            <button @click="openEdit({{ $leave }})"
                                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mr-2">Modifier</button>
                            <button @click="deleteItem({{ $leave->id }})"
                                    class="text-xs text-red-500 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucune demande de congé.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($leaves->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $leaves->links() }}</div>
            @endif
        </div>

        {{-- Create / Edit modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="editMode ? 'Modifier la demande' : 'Nouvelle demande de congé'"></h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employé *</label>
                    <select x-model="form.employee_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            :class="errors.employee_id ? 'border-red-400' : ''">
                        <option value="">— Sélectionner —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                        @endforeach
                    </select>
                    <p x-show="errors.employee_id" x-text="errors.employee_id" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                    <select x-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="conge_paye">Congé payé</option>
                        <option value="rtt">RTT</option>
                        <option value="maladie">Maladie</option>
                        <option value="sans_solde">Sans solde</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Début *</label>
                        <input type="date" x-model="form.start_date"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.start_date ? 'border-red-400' : ''">
                        <p x-show="errors.start_date" x-text="errors.start_date" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fin *</label>
                        <input type="date" x-model="form.end_date"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               :class="errors.end_date ? 'border-red-400' : ''">
                        <p x-show="errors.end_date" x-text="errors.end_date" class="mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif</label>
                    <textarea x-model="form.reason" rows="2" placeholder="Optionnel…"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg disabled:opacity-60 inline-flex items-center gap-2">
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

        {{-- Reject modal --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showRejectModal=false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rejeter la demande</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif du rejet</label>
                    <textarea x-model="rejectReason" rows="3" placeholder="Optionnel…"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="showRejectModal=false" class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                    <button @click="confirmReject()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg disabled:opacity-60 inline-flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-show="!loading">Confirmer le rejet</span>
                        <span x-show="loading">Envoi…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const leaveUrls = {
            store:         '{{ route('shifts.leaves.store') }}',
            updateBase:    '{{ url('shifts/leaves/__ID__') }}',
            deleteBase:    '{{ url('shifts/leaves/__ID__') }}',
            approveBase:   '{{ url('shifts/leaves/__ID__/approve') }}',
            rejectBase:    '{{ url('shifts/leaves/__ID__/reject') }}',
        };

        function leavesApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const emptyForm = () => ({ employee_id:'', type:'conge_paye', start_date:'', end_date:'', reason:'' });

            return {
                showModal:       false,
                showRejectModal: false,
                editMode:        false,
                loading:         false,
                itemId:          null,
                rejectId:        null,
                rejectReason:    '',
                form:            emptyForm(),
                errors:          {},

                openCreate() { this.editMode=false; this.itemId=null; this.errors={}; this.form=emptyForm(); this.showModal=true; },
                openEdit(item) {
                    this.editMode=true; this.itemId=item.id; this.errors={};
                    this.form = { employee_id:String(item.employee_id), type:item.type, start_date:item.start_date, end_date:item.end_date, reason:item.reason??'' };
                    this.showModal=true;
                },
                closeModal() { this.showModal=false; },

                async save() {
                    this.errors={}; this.loading=true;
                    const url = this.editMode ? leaveUrls.updateBase.replace('__ID__', this.itemId) : leaveUrls.store;
                    try {
                        const res = await fetch(url, { method: this.editMode?'PUT':'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify(this.form) });
                        if (res.status===422) { const d=await res.json(); this.errors=Object.fromEntries(Object.entries(d.errors??{}).map(([k,v])=>[k,Array.isArray(v)?v[0]:v])); return; }
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Demande enregistrée',type:'success'}}));
                        this.closeModal(); setTimeout(()=>window.location.reload(), 600);
                    } catch(e) { window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de la sauvegarde',type:'error'}})); }
                    finally { this.loading=false; }
                },

                async deleteItem(id) {
                    if (!confirm('Supprimer cette demande ?')) return;
                    try {
                        const res = await fetch(leaveUrls.deleteBase.replace('__ID__', id), { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf} });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Demande supprimée',type:'success'}}));
                        setTimeout(()=>window.location.reload(), 400);
                    } catch(e) { window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de la suppression',type:'error'}})); }
                },

                async approve(id) {
                    if (!confirm('Approuver cette demande et annuler les shifts concernés ?')) return;
                    this.loading=true;
                    try {
                        const res = await fetch(leaveUrls.approveBase.replace('__ID__', id), { method:'POST', headers:{'X-CSRF-TOKEN':csrf} });
                        if (!res.ok) throw new Error();
                        const d = await res.json().catch(()=>({}));
                        const msg = d.shifts_cancelled > 0 ? `Approuvé — ${d.shifts_cancelled} shift(s) annulé(s)` : 'Demande approuvée';
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:msg,type:'success'}}));
                        setTimeout(()=>window.location.reload(), 800);
                    } catch(e) { window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de l\'approbation',type:'error'}})); }
                    finally { this.loading=false; }
                },

                openReject(id) { this.rejectId=id; this.rejectReason=''; this.showRejectModal=true; },

                async confirmReject() {
                    this.loading=true;
                    try {
                        const res = await fetch(leaveUrls.rejectBase.replace('__ID__', this.rejectId), { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({rejection_reason:this.rejectReason}) });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Demande rejetée',type:'warning'}}));
                        setTimeout(()=>window.location.reload(), 600);
                    } catch(e) { window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors du rejet',type:'error'}})); }
                    finally { this.loading=false; this.showRejectModal=false; }
                },
            };
        }
    </script>
    @include('modules.timetable._crud-script')
    @endpush
</x-app-layout>
