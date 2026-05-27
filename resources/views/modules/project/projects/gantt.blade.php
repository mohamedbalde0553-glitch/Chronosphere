<x-app-layout>
    <x-slot name="title">{{ $project->name }} — Gantt</x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css">
    <style>
        .gantt-container { overflow-x: auto; }
        .gantt .bar-wrapper .bar { fill-opacity: 0.85 !important; }
        .gantt .bar-label { font-size: 12px !important; font-family: 'Inter', sans-serif !important; }
        .gantt .grid-header { fill: #F9FAFB !important; }
        .gantt .row-line { stroke: #E5E7EB !important; }

        .dark .gantt .grid-background { fill: #111827 !important; }
        .dark .gantt .grid-header { fill: #1f2937 !important; }
        .dark .gantt .grid-row { fill: #111827 !important; }
        .dark .gantt .grid-row:nth-child(even) { fill: #1a2332 !important; }
        .dark .gantt .row-line { stroke: #374151 !important; }
        .dark .gantt .tick { stroke: #374151 !important; }
        .dark .gantt .today-highlight { fill: #1e3a5f !important; fill-opacity: 0.5 !important; }
        .dark .gantt .upper-text,
        .dark .gantt .lower-text { fill: #9ca3af !important; }
        .dark .gantt .bar-label { fill: #f9fafb !important; }
        .dark .gantt-container .popup-wrapper .pointer { border-top-color: #1f2937 !important; }
        .dark .gantt-container .popup-wrapper .details-container { background: #1f2937 !important; color: #e5e7eb !important; }
    </style>
    @endpush

    <div x-data="{}" class="space-y-5">

        {{-- Topbar --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('project.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">← Projets</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background:{{ $project->color ?? '#4F46E5' }}"></span>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $project->name }}</h2>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('project.projects.board', $project) }}"
                   class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 rounded-lg">
                    ← Vue Kanban
                </a>
                {{-- View mode switcher --}}
                @foreach(['Day'=>'Jour','Week'=>'Semaine','Month'=>'Mois','Year'=>'Année'] as $mode => $label)
                <button data-gantt-view="{{ $mode }}"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                               {{ $mode === 'Week' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Gantt --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            @if($tasks->isEmpty())
            <p class="text-gray-400 text-sm text-center py-12">
                Aucune tâche pour afficher le Gantt. Ajoutez des tâches avec des dates depuis la vue Kanban.
            </p>
            @else
            <div id="cs-gantt"
                 class="gantt-container"
                 data-update-url="{{ url('project/tasks/__ID__') }}"
                 data-tasks="{{ json_encode($tasks->map(fn($t) => [
                     'id'           => $t->id,
                     'name'         => $t->name,
                     'start_date'   => $t->start_date?->format('Y-m-d'),
                     'due_date'     => $t->due_date?->format('Y-m-d'),
                     'progress'     => $t->progress,
                     'dependencies' => $t->dependencies->toArray(),
                 ])) }}">
            </div>
            @endif
        </div>

        {{-- Task table --}}
        @if($tasks->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tâche</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Assigné à</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Début</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Échéance</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Avancement</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($tasks as $task)
                    @php
                        $statusColors = ['todo'=>'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400','in_progress'=>'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300','review'=>'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300','done'=>'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300','cancelled'=>'bg-red-50 text-red-500 dark:bg-red-900/30 dark:text-red-400'];
                        $statusLabels = ['todo'=>'À faire','in_progress'=>'En cours','review'=>'Révision','done'=>'Terminé','cancelled'=>'Annulé'];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $task->name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $task->assignedTo?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">{{ $task->start_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'text-red-500 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $task->due_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full" style="width:{{ $task->progress }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 w-8 text-right">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? 'bg-gray-100 dark:bg-gray-700' }}">
                                {{ $statusLabels[$task->status] ?? $task->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    @push('scripts')
    @vite('resources/js/project.js')
    @endpush
</x-app-layout>
