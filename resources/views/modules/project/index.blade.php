<x-app-layout>
    <x-slot name="title">Projets</x-slot>

    <div x-data="projectsApp()">

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            @foreach([
                ['label'=>'Total projets',       'value'=>$stats['total'],        'color'=>'indigo'],
                ['label'=>'Projets actifs',       'value'=>$stats['active'],       'color'=>'emerald'],
                ['label'=>'Tâches en retard',     'value'=>$stats['overdue_tasks'],'color'=>'red'],
                ['label'=>'Mes tâches ouvertes',  'value'=>$stats['my_tasks'],     'color'=>'amber'],
            ] as $s)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-2xl font-bold text-gray-900">{{ $s['value'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900">Tous les projets</h2>
            <button @click="openCreate()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                + Nouveau projet
            </button>
        </div>

        {{-- Project cards --}}
        @if($projects->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400">Aucun projet. Créez-en un pour commencer.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($projects as $project)
            @php
                $progress = $project->tasks_count > 0
                    ? round($project->done_tasks_count / $project->tasks_count * 100)
                    : 0;
                $statusColors = ['active'=>'bg-emerald-50 text-emerald-700','on_hold'=>'bg-amber-50 text-amber-700','completed'=>'bg-blue-50 text-blue-700','archived'=>'bg-gray-100 text-gray-500'];
                $statusLabels = ['active'=>'Actif','on_hold'=>'En pause','completed'=>'Terminé','archived'=>'Archivé'];
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 flex flex-col gap-4">
                {{-- Top --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background:{{ $project->color ?? '#4F46E5' }}"></div>
                        <h3 class="font-semibold text-gray-900 truncate">{{ $project->name }}</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0 {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $statusLabels[$project->status] ?? $project->status }}
                    </span>
                </div>

                @if($project->description)
                <p class="text-sm text-gray-500 line-clamp-2 -mt-1">{{ $project->description }}</p>
                @endif

                {{-- Progress --}}
                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ $project->done_tasks_count }}/{{ $project->tasks_count }} tâches</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width:{{ $progress }}%; background:{{ $project->color ?? '#4F46E5' }}"></div>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    @if($project->due_date)
                    <span class="{{ $project->due_date->isPast() && $project->status !== 'completed' ? 'text-red-500 font-medium' : '' }}">
                        Échéance : {{ $project->due_date->format('d/m/Y') }}
                    </span>
                    @endif
                    <span class="ml-auto">{{ $project->owner->name }}</span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                    <a href="{{ route('project.projects.board', $project) }}"
                       class="flex-1 text-center px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        Kanban
                    </a>
                    <a href="{{ route('project.projects.gantt', $project) }}"
                       class="flex-1 text-center px-3 py-1.5 text-xs font-semibold text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors">
                        Gantt
                    </a>
                    <button @click="openEdit({{ $project }})"
                            class="px-3 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Modifier
                    </button>
                    <button @click="deleteProject({{ $project->id }})"
                            class="px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                        Suppr.
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Project modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="editMode ? 'Modifier le projet' : 'Nouveau projet'"></h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" x-model="form.name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           :class="errors.name ? 'border-red-400' : ''">
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-600"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                        <select x-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Actif</option>
                            <option value="on_hold">En pause</option>
                            <option value="completed">Terminé</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="form.color"
                                   class="w-10 h-9 rounded border border-gray-300 cursor-pointer p-0.5">
                            <input type="text" x-model="form.color"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Début</label>
                        <input type="date" x-model="form.start_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Échéance</label>
                        <input type="date" x-model="form.due_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget (€)</label>
                    <input type="number" x-model="form.budget" min="0" step="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="save()" :disabled="loading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-60">
                        <span x-show="!loading">Enregistrer</span><span x-show="loading">…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const projectUrls = {
            store:      '{{ route('project.projects.store') }}',
            updateBase: '{{ url('project/projects/__ID__') }}',
            deleteBase: '{{ url('project/projects/__ID__') }}',
        };

        function projectsApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const empty = () => ({ name:'', description:'', color:'#4F46E5', status:'active', start_date:'', due_date:'', budget:'' });

            return {
                showModal: false,
                editMode:  false,
                loading:   false,
                projectId: null,
                form:      empty(),
                errors:    {},

                openCreate() { this.editMode=false; this.projectId=null; this.errors={}; this.form=empty(); this.showModal=true; },
                openEdit(p) {
                    this.editMode=true; this.projectId=p.id; this.errors={};
                    this.form = { name:p.name, description:p.description??'', color:p.color??'#4F46E5', status:p.status, start_date:p.start_date??'', due_date:p.due_date??'', budget:p.budget??'' };
                    this.showModal=true;
                },
                closeModal() { this.showModal=false; },

                async save() {
                    this.errors={};this.loading=true;
                    const url    = this.editMode ? projectUrls.updateBase.replace('__ID__', this.projectId) : projectUrls.store;
                    const method = this.editMode ? 'PUT' : 'POST';
                    try {
                        const res = await fetch(url, { method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify(this.form) });
                        if (res.status===422) { const d=await res.json(); this.errors=Object.fromEntries(Object.entries(d.errors??{}).map(([k,v])=>[k,Array.isArray(v)?v[0]:v])); return; }
                        if (!res.ok) throw new Error();
                        this.closeModal(); window.location.reload();
                    } catch(e) { console.error(e); }
                    finally { this.loading=false; }
                },

                async deleteProject(id) {
                    if (!confirm('Supprimer ce projet et toutes ses tâches ?')) return;
                    const res = await fetch(projectUrls.deleteBase.replace('__ID__', id), { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf} });
                    if (res.ok) window.location.reload();
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
