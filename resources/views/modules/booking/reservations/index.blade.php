<x-app-layout>
    <x-slot name="title">Toutes les réservations</x-slot>

    <div x-data="reservationsApp()" @keydown.escape.window="closeModal()">

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Réservations</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $bookings->total() }} réservation(s)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('booking.index') }}" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Retour</a>
                <a href="{{ route('booking.calendar') }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg">+ Nouvelle réservation</a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Titre</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Ressource</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Demandeur</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Début</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Fin</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Participants</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($bookings as $b)
                    @php
                        $statusColors = [
                            'pending'   => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
                            'confirmed' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
                            'cancelled' => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                            'rejected'  => 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                        ];
                        $statusLabels = [
                            'pending'   => 'En attente',
                            'confirmed' => 'Confirmé',
                            'cancelled' => 'Annulé',
                            'rejected'  => 'Rejeté',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $b->title }}</div>
                            @if($b->description)
                            <div class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-xs">{{ $b->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $b->resource->color ?? '#EA580C' }}"></span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $b->resource->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $b->user->name }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $b->start_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $b->end_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $b->attendees_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$b->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                {{ $statusLabels[$b->status] ?? $b->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($b->status === 'pending')
                            <button @click="approve({{ $b->id }})"
                                    class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium mr-2">Approuver</button>
                            <button @click="openReject({{ $b->id }})"
                                    class="text-xs text-red-500 dark:text-red-400 hover:text-red-600 font-medium mr-3">Rejeter</button>
                            @endif
                            @if(in_array($b->status, ['pending','confirmed']))
                            <button @click="cancel({{ $b->id }})"
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-medium mr-3">Annuler</button>
                            @endif
                            <button @click="deleteItem({{ $b->id }})"
                                    class="text-xs text-red-500 dark:text-red-400 hover:text-red-600 font-medium">Supprimer</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucune réservation.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($bookings->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $bookings->links() }}</div>
            @endif
        </div>

        {{-- Reject modal --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showRejectModal=false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Rejeter la réservation</h3>
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
                        <span x-show="!loading">Confirmer</span>
                        <span x-show="loading">Traitement…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const resaUrls = {
            approveBase: '{{ url('booking/reservations/__ID__/approve') }}',
            rejectBase:  '{{ url('booking/reservations/__ID__/reject') }}',
            cancelBase:  '{{ url('booking/reservations/__ID__/cancel') }}',
            deleteBase:  '{{ url('booking/reservations/__ID__') }}',
        };

        function reservationsApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            return {
                showRejectModal: false,
                loading:         false,
                rejectId:        null,
                rejectReason:    '',

                closeModal() { this.showRejectModal = false; },

                async approve(id) {
                    if (!confirm('Approuver cette réservation ?')) return;
                    this.loading = true;
                    try {
                        const res = await fetch(resaUrls.approveBase.replace('__ID__', id), {
                            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf },
                        });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Réservation approuvée',type:'success'}}));
                        setTimeout(() => window.location.reload(), 600);
                    } catch(e) {
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de l\'approbation',type:'error'}}));
                    } finally { this.loading = false; }
                },

                openReject(id) {
                    this.rejectId     = id;
                    this.rejectReason = '';
                    this.showRejectModal = true;
                },

                async confirmReject() {
                    this.loading = true;
                    try {
                        const res = await fetch(resaUrls.rejectBase.replace('__ID__', this.rejectId), {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf },
                            body:    JSON.stringify({ rejection_reason: this.rejectReason }),
                        });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Réservation rejetée',type:'warning'}}));
                        setTimeout(() => window.location.reload(), 600);
                    } catch(e) {
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors du rejet',type:'error'}}));
                    } finally { this.loading = false; this.showRejectModal = false; }
                },

                async cancel(id) {
                    if (!confirm('Annuler cette réservation ?')) return;
                    try {
                        const res = await fetch(resaUrls.cancelBase.replace('__ID__', id), {
                            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf },
                        });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Réservation annulée',type:'warning'}}));
                        setTimeout(() => window.location.reload(), 600);
                    } catch(e) {
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de l\'annulation',type:'error'}}));
                    }
                },

                async deleteItem(id) {
                    if (!confirm('Supprimer définitivement cette réservation ?')) return;
                    try {
                        const res = await fetch(resaUrls.deleteBase.replace('__ID__', id), {
                            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf },
                        });
                        if (!res.ok) throw new Error();
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Réservation supprimée',type:'success'}}));
                        setTimeout(() => window.location.reload(), 400);
                    } catch(e) {
                        window.dispatchEvent(new CustomEvent('toast', {detail:{message:'Erreur lors de la suppression',type:'error'}}));
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
