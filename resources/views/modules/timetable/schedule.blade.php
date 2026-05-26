<x-app-layout>
    <x-slot name="title">Grille des séances</x-slot>

    @push('styles')
    <style>
        .fc { font-family: 'Inter', sans-serif; }
        .fc-toolbar-title { font-size: 1rem !important; font-weight: 600; }
        .fc-button { border-radius: 0.5rem !important; font-size: 0.78rem !important; font-weight: 500 !important; }
        .fc-button-primary { background-color: #1E40AF !important; border-color: #1E40AF !important; }
        .fc-button-primary:hover { background-color: #1e3a8a !important; }
        .fc-button-active { background-color: #1e3a8a !important; }
        .fc-event { border-radius: 3px !important; font-size: 0.75rem; cursor: pointer; }
        .fc-timegrid-slot { height: 2.5rem; }
        .fc-col-header-cell-cushion { font-size: 0.78rem; font-weight: 600; }
    </style>
    @endpush

    <div x-data="scheduleApp()" class="space-y-4">

        {{-- Toolbar --}}
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex flex-wrap items-center gap-3">

            {{-- Filter by --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-500 font-medium">Voir par :</span>
                <select x-model="filterType" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="group">Groupe</option>
                    <option value="teacher">Enseignant</option>
                    <option value="room">Salle</option>
                </select>
            </div>

            {{-- Filter selector --}}
            <div x-show="filterType === 'group'">
                <select x-model="filterId" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ $filterId == $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->level->name ?? '' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div x-show="filterType === 'teacher'">
                <select x-model="filterId" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $filterId == $t->id ? 'selected' : '' }}>
                        {{ $t->user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div x-show="filterType === 'room'">
                <select x-model="filterId" @change="applyFilter()"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach($rooms as $r)
                    <option value="{{ $r->id }}" {{ $filterId == $r->id ? 'selected' : '' }}>
                        {{ $r->code }} — {{ $r->name }} ({{ $r->capacity }} places)
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button @click="openNewSession()"
                        class="flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter une séance
                </button>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
            <div id="cs-timetable"
                 data-feed-url="{{ route('timetable.schedule.feed') }}"
                 data-filter-type="{{ $filterType }}"
                 data-filter-id="{{ $filterId }}">
            </div>
        </div>

        {{-- Conflict alert --}}
        <div x-show="conflicts.length > 0" x-cloak
             class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-red-700">Conflits détectés</p>
                    <ul class="mt-1 space-y-1">
                        <template x-for="c in conflicts">
                            <li class="text-sm text-red-600" x-text="c.message"></li>
                        </template>
                    </ul>
                    <div class="mt-3 flex gap-3">
                        <button @click="forceSave()" class="px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Forcer l'enregistrement
                        </button>
                        <button @click="conflicts=[]" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-100 rounded-lg">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Session modal --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900"
                        x-text="editMode ? 'Modifier la séance' : 'Nouvelle séance'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cours <span class="text-red-500">*</span></label>
                        <select x-model="form.course_id"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="errors.course_id ? 'border-red-400' : ''">
                            <option value="">— Sélectionner —</option>
                            @foreach($courses as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->subject->name }} — {{ $c->classGroup?->name }} ({{ $c->teacher?->user?->name ?? 'TBA' }})
                            </option>
                            @endforeach
                        </select>
                        <p x-show="errors.course_id" x-text="errors.course_id" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Salle</label>
                        <select x-model="form.room_id"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Sans salle —</option>
                            @foreach($rooms as $r)
                            <option value="{{ $r->id }}">{{ $r->code }} — {{ $r->name }} ({{ $r->capacity }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                            <input type="datetime-local" x-model="form.start_at"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   :class="errors.start_at ? 'border-red-400' : ''">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                            <input type="datetime-local" x-model="form.end_at"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <p x-show="errors.start_at" x-text="errors.start_at" class="text-xs text-red-600"></p>
                    <p x-show="errors.end_at" x-text="errors.end_at" class="text-xs text-red-600"></p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" x-model="form.notes" placeholder="Optionnel…"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <button x-show="editMode" @click="deleteSession()"
                            class="text-sm text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg">Supprimer</button>
                    <div class="flex gap-3 ml-auto">
                        <button @click="closeModal()" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 bg-white rounded-lg hover:bg-gray-50">Annuler</button>
                        <button @click="saveSession()" :disabled="loading"
                                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-60">
                            <span x-show="!loading">Enregistrer</span>
                            <span x-show="loading">…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        window.csTimetableUrls = {
            store:      '{{ route('timetable.sessions.store') }}',
            updateBase: '{{ url('timetable/sessions/__ID__') }}',
            deleteBase: '{{ url('timetable/sessions/__ID__') }}',
        };

        function scheduleApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            return {
                filterType: '{{ $filterType }}',
                filterId:   '{{ $filterId }}',
                showModal:  false,
                editMode:   false,
                loading:    false,
                sessionId:  null,
                pendingData:null,
                conflicts:  [],
                errors:     {},
                form: {
                    course_id: '',
                    room_id:   '',
                    start_at:  '',
                    end_at:    '',
                    notes:     '',
                },

                applyFilter() {
                    if (window.csTimetable) {
                        const src = window.csTimetable.getEventSources()[0];
                        src.remove();
                        window.csTimetable.addEventSource({
                            url: document.getElementById('cs-timetable').dataset.feedUrl,
                            extraParams: { by: this.filterType, id: this.filterId },
                        });
                    }
                },

                openNewSession(start, end) {
                    this.editMode  = false;
                    this.sessionId = null;
                    this.errors    = {};
                    this.conflicts = [];
                    this.form = { course_id:'', room_id:'', start_at: start??'', end_at: end??'', notes:'' };
                    this.showModal = true;
                },

                openEditSession(detail) {
                    this.editMode  = true;
                    this.sessionId = detail.id;
                    this.errors    = {};
                    this.conflicts = [];
                    this.form = {
                        course_id: String(detail.course_id),
                        room_id:   detail.room_id ? String(detail.room_id) : '',
                        start_at:  detail.start.substring(0,16),
                        end_at:    detail.end.substring(0,16),
                        notes:     detail.notes ?? '',
                    };
                    this.showModal = true;
                },

                closeModal() { this.showModal = false; this.conflicts = []; },

                async saveSession(force = false) {
                    this.errors  = {};
                    this.loading = true;
                    const url    = this.editMode
                        ? window.csTimetableUrls.updateBase.replace('__ID__', this.sessionId)
                        : window.csTimetableUrls.store;
                    const method = this.editMode ? 'PUT' : 'POST';
                    const body   = force ? { ...this.form, force: true } : this.form;

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify(body),
                        });

                        if (res.status === 422) {
                            const d = await res.json();
                            this.errors = Object.fromEntries(
                                Object.entries(d.errors ?? {}).map(([k,v]) => [k, Array.isArray(v) ? v[0] : v])
                            );
                            return;
                        }
                        if (res.status === 409) {
                            const d = await res.json();
                            this.pendingData = body;
                            this.conflicts   = d.conflicts ?? [];
                            this.showModal   = false;
                            return;
                        }
                        if (!res.ok) throw new Error();

                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('cs:refresh-timetable'));
                    } catch(e) { console.error(e); }
                    finally { this.loading = false; }
                },

                async forceSave() {
                    this.showModal = true;
                    await this.saveSession(true);
                },

                async deleteSession() {
                    if (!confirm('Supprimer cette séance ?')) return;
                    this.loading = true;
                    try {
                        const res = await fetch(
                            window.csTimetableUrls.deleteBase.replace('__ID__', this.sessionId),
                            { method:'DELETE', headers:{'X-CSRF-TOKEN': csrf} }
                        );
                        if (!res.ok) throw new Error();
                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('cs:refresh-timetable'));
                    } catch(e) { console.error(e); }
                    finally { this.loading = false; }
                },
            };
        }
    </script>
    @vite('resources/js/timetable.js')
    @endpush
</x-app-layout>
