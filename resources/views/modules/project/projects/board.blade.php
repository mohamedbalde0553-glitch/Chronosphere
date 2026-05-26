<x-app-layout>
    <x-slot name="title">{{ $project->name }} — Kanban</x-slot>

    @php
        $columns = [
            'todo'        => ['label'=>'À faire',    'color'=>'gray',   'dot'=>'bg-gray-400'],
            'in_progress' => ['label'=>'En cours',   'color'=>'blue',   'dot'=>'bg-blue-500'],
            'review'      => ['label'=>'Révision',   'color'=>'violet', 'dot'=>'bg-violet-500'],
            'done'        => ['label'=>'Terminé',    'color'=>'emerald','dot'=>'bg-emerald-500'],
        ];
        $priorityColors = [
            'low'    => 'bg-gray-100 text-gray-500',
            'medium' => 'bg-blue-50 text-blue-600',
            'high'   => 'bg-amber-50 text-amber-600',
            'urgent' => 'bg-red-50 text-red-600',
        ];
        $priorityLabels = ['low'=>'Bas','medium'=>'Moyen','high'=>'Haut','urgent'=>'Urgent'];
    @endphp

    <div x-data="boardApp()" class="flex flex-col h-full">

        {{-- Topbar --}}
        <div class="flex items-center gap-3 mb-5">
            <a href="{{ route('project.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Projets</a>
            <span class="text-gray-300">/</span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background:{{ $project->color ?? '#4F46E5' }}"></span>
                <h2 class="text-base font-semibold text-gray-900">{{ $project->name }}</h2>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('project.projects.gantt', $project) }}"
                   class="px-3 py-1.5 text-xs font-semibold text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-lg">
                    Vue Gantt →
                </a>
            </div>
        </div>

        {{-- Board --}}
        <div id="cs-board"
             data-reorder-url="{{ route('project.tasks.reorder') }}"
             class="grid grid-cols-4 gap-4 items-start overflow-x-auto pb-4">

            @foreach($columns as $status => $col)
            <div class="flex flex-col gap-3 min-w-[260px]">

                {{-- Column header --}}
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $col['dot'] }}"></span>
                        <span class="text-sm font-semibold text-gray-700">{{ $col['label'] }}</span>
                        <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">{{ count($tasks[$status]) }}</span>
                    </div>
                    <button @click="openCreate('{{ $status }}')"
                            class="text-gray-400 hover:text-gray-600 text-lg leading-none">+</button>
                </div>

                {{-- Task list --}}
                <div data-kanban-column="{{ $status }}"
                     class="flex flex-col gap-2 min-h-[120px] rounded-xl bg-gray-50/70 p-2">

                    @foreach($tasks[$status] as $task)
                    <div data-task-id="{{ $task->id }}"
                         class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm cursor-pointer hover:border-indigo-300 hover:shadow-md transition-all group"
                         @click="openTask({{ $task->id }})">

                        {{-- Drag handle --}}
                        <div class="drag-handle flex items-center gap-2 mb-2 cursor-grab active:cursor-grabbing">
                            <svg class="w-3 h-3 text-gray-300 group-hover:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm6 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM7 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm6 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM7 14a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm6 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/>
                            </svg>
                            <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $priorityColors[$task->priority] ?? '' }}">
                                {{ $priorityLabels[$task->priority] ?? $task->priority }}
                            </span>
                        </div>

                        <p class="text-sm font-medium text-gray-900 line-clamp-2 mb-2">{{ $task->name }}</p>

                        {{-- Progress bar --}}
                        @if($task->progress > 0)
                        <div class="h-1 bg-gray-100 rounded-full mb-2 overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width:{{ $task->progress }}%"></div>
                        </div>
                        @endif

                        {{-- Footer --}}
                        <div class="flex items-center gap-2 mt-2">
                            @if($task->assignedTo)
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center shrink-0"
                                  title="{{ $task->assignedTo->name }}">
                                {{ strtoupper(substr($task->assignedTo->name, 0, 1)) }}
                            </span>
                            @endif

                            @if($task->due_date)
                            <span class="text-xs {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                {{ $task->due_date->format('d/m') }}
                            </span>
                            @endif

                            <div class="ml-auto flex items-center gap-2 text-gray-400">
                                @if($task->subtasks_count > 0)
                                <span class="text-xs">☰ {{ $task->subtasks_count }}</span>
                                @endif
                                @if($task->comments_count > 0)
                                <span class="text-xs">💬 {{ $task->comments_count }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
            @endforeach
        </div>

        {{-- Task detail drawer --}}
        <div x-show="showDrawer" x-cloak
             class="fixed inset-0 z-50 flex justify-end"
             @keydown.escape.window="closeDrawer()">
            <div class="absolute inset-0 bg-black/30" @click="closeDrawer()"></div>
            <div class="relative w-full max-w-lg bg-white shadow-2xl flex flex-col h-full" @click.stop>

                {{-- Drawer header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 truncate pr-4"
                        x-text="taskForm.name || 'Tâche'"></h3>
                    <div class="flex gap-2">
                        <button @click="saveTask()" :disabled="taskLoading"
                                class="px-4 py-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-60">
                            <span x-show="!taskLoading">Sauvegarder</span><span x-show="taskLoading">…</span>
                        </button>
                        <button @click="closeDrawer()" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Drawer body --}}
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Titre</label>
                        <input type="text" x-model="taskForm.name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                            <select x-model="taskForm.status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="todo">À faire</option>
                                <option value="in_progress">En cours</option>
                                <option value="review">Révision</option>
                                <option value="done">Terminé</option>
                                <option value="cancelled">Annulé</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Priorité</label>
                            <select x-model="taskForm.priority"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="low">Bas</option>
                                <option value="medium">Moyen</option>
                                <option value="high">Haut</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Assigné à</label>
                        <select x-model="taskForm.assigned_to"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Non assigné —</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                            <input type="date" x-model="taskForm.start_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Échéance</label>
                            <input type="date" x-model="taskForm.due_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Avancement : <span x-text="taskForm.progress + '%'"></span>
                        </label>
                        <input type="range" x-model="taskForm.progress" min="0" max="100" step="5"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Estimation (min.)</label>
                        <input type="number" x-model="taskForm.estimated_minutes" min="0" step="30"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                        <textarea x-model="taskForm.description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>

                    {{-- Delete --}}
                    <div class="pt-2 border-t border-gray-100">
                        <button @click="deleteTask()"
                                class="text-sm text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
                            Supprimer la tâche
                        </button>
                    </div>

                    {{-- Comments --}}
                    <div class="pt-2 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Commentaires</h4>

                        <div class="space-y-3 mb-4" id="comments-list">
                            <template x-for="c in comments" :key="c.id">
                                <div class="flex gap-3">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0"
                                         x-text="c.user.name.charAt(0).toUpperCase()"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="text-xs font-semibold text-gray-800" x-text="c.user.name"></span>
                                            <span class="text-xs text-gray-400" x-text="c.created_at ? new Date(c.created_at).toLocaleDateString('fr') : ''"></span>
                                        </div>
                                        <p class="text-sm text-gray-700" x-text="c.content"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!comments.length">
                                <p class="text-sm text-gray-400">Aucun commentaire.</p>
                            </template>
                        </div>

                        <div class="flex gap-2">
                            <input type="text" x-model="newComment" placeholder="Ajouter un commentaire…"
                                   @keydown.enter="postComment()"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <button @click="postComment()"
                                    class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                Envoyer
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Add task modal (quick) --}}
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showAddModal=false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm z-10 p-6 space-y-4" @click.stop>
                <h3 class="text-sm font-semibold text-gray-900">Nouvelle tâche</h3>

                <div>
                    <input type="text" x-model="newTaskName" placeholder="Nom de la tâche…" @keydown.enter="quickCreate()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           x-ref="addInput">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Priorité</label>
                        <select x-model="newTaskPriority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="low">Bas</option>
                            <option value="medium">Moyen</option>
                            <option value="high">Haut</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Assigné à</label>
                        <select x-model="newTaskAssignee"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Aucun —</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="showAddModal=false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button @click="quickCreate()" :disabled="taskLoading"
                            class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-60">
                        <span x-show="!taskLoading">Créer</span><span x-show="taskLoading">…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const boardUrls = {
            tasksBase:   '{{ url('project/projects/' . $project->id . '/tasks') }}',
            taskBase:    '{{ url('project/tasks/__ID__') }}',
            commentBase: '{{ url('project/tasks/__ID__/comments') }}',
            reorder:     '{{ route('project.tasks.reorder') }}',
        };

        function boardApp() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            return {
                showDrawer:      false,
                showAddModal:    false,
                taskLoading:     false,
                currentTaskId:   null,
                newTaskStatus:   'todo',
                newTaskName:     '',
                newTaskPriority: 'medium',
                newTaskAssignee: '',
                newComment:      '',
                comments:        [],
                taskForm: {
                    name:'', description:'', status:'todo', priority:'medium',
                    assigned_to:'', start_date:'', due_date:'',
                    estimated_minutes:0, progress:0,
                },

                openCreate(status) {
                    this.newTaskStatus   = status;
                    this.newTaskName     = '';
                    this.newTaskPriority = 'medium';
                    this.newTaskAssignee = '';
                    this.showAddModal    = true;
                    this.$nextTick(() => this.$refs.addInput?.focus());
                },

                async quickCreate() {
                    if (!this.newTaskName.trim()) return;
                    this.taskLoading = true;
                    try {
                        const res = await fetch(boardUrls.tasksBase, {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf },
                            body: JSON.stringify({
                                name: this.newTaskName,
                                status: this.newTaskStatus,
                                priority: this.newTaskPriority,
                                assigned_to: this.newTaskAssignee || null,
                            }),
                        });
                        if (!res.ok) throw new Error();
                        this.showAddModal = false;
                        window.location.reload();
                    } catch(e) { console.error(e); }
                    finally { this.taskLoading = false; }
                },

                async openTask(id) {
                    this.currentTaskId = id;
                    this.showDrawer    = true;
                    this.comments      = [];
                    try {
                        const res = await fetch(boardUrls.taskBase.replace('__ID__', id));
                        const t   = await res.json();
                        this.taskForm = {
                            name:               t.name,
                            description:        t.description ?? '',
                            status:             t.status,
                            priority:           t.priority,
                            assigned_to:        t.assigned_to ? String(t.assigned_to) : '',
                            start_date:         t.start_date ?? '',
                            due_date:           t.due_date ?? '',
                            estimated_minutes:  t.estimated_minutes ?? 0,
                            progress:           t.progress ?? 0,
                        };
                        this.comments = t.comments ?? [];
                    } catch(e) { console.error(e); }
                },

                closeDrawer() { this.showDrawer = false; },

                async saveTask() {
                    this.taskLoading = true;
                    try {
                        const res = await fetch(boardUrls.taskBase.replace('__ID__', this.currentTaskId), {
                            method: 'PUT',
                            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf },
                            body: JSON.stringify(this.taskForm),
                        });
                        if (!res.ok) throw new Error();
                        this.closeDrawer();
                        window.location.reload();
                    } catch(e) { console.error(e); }
                    finally { this.taskLoading = false; }
                },

                async deleteTask() {
                    if (!confirm('Supprimer cette tâche ?')) return;
                    const res = await fetch(boardUrls.taskBase.replace('__ID__', this.currentTaskId), {
                        method: 'DELETE', headers: {'X-CSRF-TOKEN':csrf},
                    });
                    if (res.ok) { this.closeDrawer(); window.location.reload(); }
                },

                async postComment() {
                    if (!this.newComment.trim()) return;
                    try {
                        const res = await fetch(boardUrls.commentBase.replace('__ID__', this.currentTaskId), {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf },
                            body: JSON.stringify({ content: this.newComment }),
                        });
                        const c = await res.json();
                        this.comments.push(c);
                        this.newComment = '';
                    } catch(e) { console.error(e); }
                },
            };
        }
    </script>
    @vite('resources/js/project.js')
    @endpush
</x-app-layout>
