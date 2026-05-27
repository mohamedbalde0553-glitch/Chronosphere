<x-app-layout>
    <x-slot name="title">Agenda</x-slot>

    @push('styles')
    <style>
        /* FullCalendar overrides */
        .fc { font-family: 'Inter', sans-serif; }
        .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 600; }
        .fc-button { border-radius: 0.5rem !important; font-size: 0.8rem !important; font-weight: 500 !important; }
        .fc-button-primary { background-color: #4F46E5 !important; border-color: #4F46E5 !important; }
        .fc-button-primary:hover { background-color: #4338CA !important; border-color: #4338CA !important; }
        .fc-button-active { background-color: #3730A3 !important; border-color: #3730A3 !important; }
        .fc-event { border-radius: 4px !important; font-size: 0.78rem; cursor: pointer; }
        .fc-daygrid-day-number { font-size: 0.8rem; }
        .fc-col-header-cell-cushion { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .dark .fc { color: #e5e7eb; }
        .dark .fc-scrollgrid, .dark .fc-scrollgrid td, .dark .fc-scrollgrid th { border-color: #374151 !important; }
        .dark .fc-col-header-cell { background: #1f2937; }
        .dark .fc-timegrid-slot-lane { background: #111827; }
        .dark .fc-daygrid-day { background: #111827; }
        .dark .fc-day-today { background: #1e1b4b !important; }
        .dark .fc-toolbar-title { color: #f9fafb; }
        .dark .fc-col-header-cell-cushion { color: #9ca3af; }
        .dark .fc-timegrid-axis-cushion { color: #9ca3af; }
        .dark .fc-daygrid-day-number { color: #9ca3af; }
        .dark .fc-list-day-cushion { background: #1f2937 !important; color: #e5e7eb; }
        .dark .fc-list-event:hover td { background: #374151 !important; }
    </style>
    @endpush

    <div
        x-data="calendarModal()"
        @cs:event-click.window="openEdit($event.detail)"
        @cs:date-click.window="openNew($event.detail)"
        class="flex gap-5 h-full"
    >

        {{-- ===== SIDEBAR CALENDRIERS ===== --}}
        <aside class="w-52 shrink-0 space-y-4">

            <button
                @click="openNew({ start: null, end: null, allDay: false })"
                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvel événement
            </button>

            {{-- Mes calendriers --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">Mes calendriers</p>
                <div class="space-y-2" id="cal-filter-list">
                    @foreach($calendars as $cal)
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" value="{{ $cal->id }}"
                               class="cal-filter-cb rounded"
                               style="accent-color: {{ $cal->color }}"
                               checked
                               @change="filterCalendar($event)">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background:{{ $cal->color }}"></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $cal->name }}</span>
                        @if($cal->is_default)
                        <span class="ml-auto text-xs text-gray-400">défaut</span>
                        @endif
                    </label>
                    @endforeach
                </div>

                {{-- Ajouter un calendrier --}}
                <form method="POST" action="{{ route('calendar.calendars.store') }}" class="mt-4 space-y-2" x-data="{ showForm: false }">
                    @csrf
                    <button type="button" @click="showForm=!showForm"
                            class="flex items-center gap-1 text-xs text-violet-600 hover:text-violet-700 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau calendrier
                    </button>
                    <div x-show="showForm" x-cloak class="space-y-2">
                        <input name="name" type="text" placeholder="Nom" required
                               class="w-full text-xs px-2 py-1.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <div class="flex items-center gap-2">
                            <input name="color" type="color" value="#7C3AED"
                                   class="w-8 h-7 rounded cursor-pointer border border-gray-300">
                            <button type="submit" class="flex-1 py-1.5 bg-violet-600 text-white text-xs font-semibold rounded-lg hover:bg-violet-700">
                                Créer
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Catégories --}}
            @if($categories->count())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">Catégories</p>
                <div class="space-y-1.5">
                    @foreach($categories as $cat)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $cat->color }}"></span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $cat->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </aside>

        {{-- ===== FULLCALENDAR ===== --}}
        <div class="flex-1 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 min-w-0">
            <div
                id="cs-calendar"
                data-feed-url="{{ route('calendar.feed') }}"
            ></div>
        </div>

        {{-- ===== MODAL CRÉER / MODIFIER ===== --}}
        <div
            x-show="showModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @keydown.escape.window="closeModal()"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- Panel --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg z-10 flex flex-col max-h-[90vh]"
                 @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                        x-text="editMode ? 'Modifier l\'événement' : 'Nouvel événement'">
                    </h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <div class="px-6 py-5 space-y-4 overflow-y-auto">

                    {{-- Titre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.title" placeholder="Nom de l'événement"
                               class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                               :class="errors.title ? 'border-red-400' : ''">
                        <p x-show="errors.title" x-text="errors.title" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    {{-- Calendrier --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Calendrier</label>
                        <select x-model="form.calendar_id"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            @foreach($calendars as $cal)
                            <option value="{{ $cal->id }}">{{ $cal->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dates --}}
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-3 cursor-pointer">
                            <input type="checkbox" x-model="form.is_all_day" class="rounded border-gray-300 dark:border-gray-600 text-violet-600 focus:ring-violet-500">
                            Journée entière
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Début</label>
                                <input x-model="form.start_at"
                                       :type="form.is_all_day ? 'date' : 'datetime-local'"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
                                       :class="errors.start_at ? 'border-red-400' : ''">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fin</label>
                                <input x-model="form.end_at"
                                       :type="form.is_all_day ? 'date' : 'datetime-local'"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                            </div>
                        </div>
                        <p x-show="errors.start_at" x-text="errors.start_at" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    {{-- Lieu + Couleur --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lieu</label>
                            <input type="text" x-model="form.location" placeholder="Salle, adresse…"
                                   class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur</label>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @foreach(['#7C3AED','#2563EB','#059669','#EA580C','#DC2626','#DB2777','#0891B2','#6B7280'] as $c)
                                <button type="button"
                                        @click="form.color = '{{ $c }}'"
                                        class="w-6 h-6 rounded-full border-2 transition-shadow hover:shadow-md"
                                        :class="form.color === '{{ $c }}' ? 'border-gray-800 dark:border-white scale-110' : 'border-transparent'"
                                        style="background:{{ $c }}">
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea x-model="form.description" rows="2" placeholder="Notes optionnelles…"
                                  class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-violet-500"></textarea>
                    </div>

                    {{-- Statut (édition seulement) --}}
                    <div x-show="editMode">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                        <select x-model="form.status"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="confirmed">Confirmé</option>
                            <option value="tentative">Provisoire</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shrink-0">
                    <div>
                        <button x-show="editMode" @click="deleteEvent()"
                                class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                            Supprimer
                        </button>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Annuler
                        </button>
                        <button @click="saveEvent()" :disabled="loading"
                                class="px-5 py-2 text-sm font-semibold text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors disabled:opacity-60">
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
        // Pass route templates to calendar.js before it initialises
        window.csEventUpdateBase = '{{ url('calendar/events/__ID__') }}';

        function calendarModal() {
            const defaultCalId = '{{ $calendars->first()?->id ?? '' }}';
            const storeUrl     = '{{ route('calendar.events.store') }}';
            const csrf         = document.querySelector('meta[name="csrf-token"]').content;

            const emptyForm = () => ({
                title:       '',
                calendar_id: defaultCalId,
                is_all_day:  false,
                start_at:    '',
                end_at:      '',
                description: '',
                location:    '',
                color:       '',
                status:      'confirmed',
            });

            return {
                showModal: false,
                editMode:  false,
                loading:   false,
                eventId:   null,
                form:      emptyForm(),
                errors:    {},

                openNew(detail) {
                    this.editMode  = false;
                    this.eventId   = null;
                    this.errors    = {};
                    this.form      = emptyForm();
                    if (detail.start) this.form.start_at = detail.start;
                    if (detail.end)   this.form.end_at   = detail.end;
                    if (detail.allDay) this.form.is_all_day = true;
                    this.showModal = true;
                },

                openEdit(detail) {
                    this.editMode  = true;
                    this.eventId   = detail.id;
                    this.errors    = {};
                    this.form = {
                        title:       detail.title,
                        calendar_id: String(detail.calendar_id),
                        is_all_day:  detail.allDay,
                        start_at:    toInputDate(detail.start, detail.allDay),
                        end_at:      toInputDate(detail.end,   detail.allDay),
                        description: detail.description,
                        location:    detail.location,
                        color:       detail.color ?? '',
                        status:      detail.status ?? 'confirmed',
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                async saveEvent() {
                    this.errors  = {};
                    this.loading = true;

                    const url    = this.editMode
                        ? `{{ url('calendar/events') }}/${this.eventId}`
                        : storeUrl;
                    const method = this.editMode ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify(this.form),
                        });

                        if (res.status === 422) {
                            const data = await res.json();
                            this.errors = flattenErrors(data.errors ?? {});
                            return;
                        }

                        if (!res.ok) throw new Error('Erreur serveur');

                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('cs:refresh-calendar'));

                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteEvent() {
                    if (!confirm('Supprimer cet événement ?')) return;
                    this.loading = true;

                    try {
                        const res = await fetch(`{{ url('calendar/events') }}/${this.eventId}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrf },
                        });
                        if (!res.ok) throw new Error();
                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('cs:refresh-calendar'));
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                filterCalendar(e) {
                    // Rebuild event source with visible calendar IDs
                    if (window.csCalendar) {
                        window.csCalendar.refetchEvents();
                    }
                },
            };
        }

        function toInputDate(str, allDay) {
            if (!str) return '';
            return allDay ? str.substring(0, 10) : str.substring(0, 16);
        }

        function flattenErrors(errors) {
            const out = {};
            for (const [key, msgs] of Object.entries(errors)) {
                out[key] = Array.isArray(msgs) ? msgs[0] : msgs;
            }
            return out;
        }
    </script>
    @vite('resources/js/calendar.js')
    @endpush

</x-app-layout>
