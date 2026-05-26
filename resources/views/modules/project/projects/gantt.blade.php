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
    </style>
    @endpush

    <div x-data="{}" class="space-y-5">

        {{-- Topbar --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('project.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Projets</a>
            <span class="text-gray-300">/</span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background:{{ $project->color ?? '#4F46E5' }}"></span>
                <h2 class="text-base font-semibold text-gray-900">{{ $project->name }}</h2>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('project.projects.board', $project) }}"
                   class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg">
                    ← Vue Kanban
                </a>
                {{-- View mode switcher --}}
                @foreach(['Day'=>'Jour','Week'=>'Semaine','Month'=>'Mois','Year'=>'Année'] as $mode => $label)
                <button data-gantt-view="{{ $mode }}"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                               {{ $mode === 'Week' ? 'bg-indigo-600 text-white' : 'text-gray-600 bg-white border border-gray-300 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Gantt --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
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
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Tâche</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Assigné à</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Début</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Échéance</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Avancement</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tasks as $task)
                    @php
                        $statusColors = ['todo'=>'bg-gray-100 text-gray-500','in_progress'=>'bg-blue-50 text-blue-700','review'=>'bg-violet-50 text-violet-700','done'=>'bg-emerald-50 text-emerald-700','cancelled'=>'bg-red-50 text-red-500'];
                        $statusLabels = ['todo'=>'À faire','in_progress'=>'En cours','review'=>'Révision','done'=>'Terminé','cancelled'=>'Annulé'];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $task->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $task->assignedTo?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $task->start_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                            {{ $task->due_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full" style="width:{{ $task->progress }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8 text-right">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? 'bg-gray-100' }}">
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
