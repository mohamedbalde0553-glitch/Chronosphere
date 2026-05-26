<?php

namespace App\Modules\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total'        => Project::count(),
            'active'       => Project::where('status', 'active')->count(),
            'overdue_tasks'=> Task::overdue()->count(),
            'my_tasks'     => Task::where('assigned_to', auth()->id())
                                ->whereNotIn('status', ['done', 'cancelled'])
                                ->count(),
        ];

        $projects = Project::with('owner')
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            ])
            ->orderByDesc('updated_at')
            ->get();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('modules.project.index', compact('stats', 'projects', 'users'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:7',
            'status'      => 'required|string|in:active,on_hold,completed,archived',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'budget'      => 'nullable|numeric|min:0',
        ]);

        $data['owner_id'] = auth()->id();
        $project = Project::create($data);
        $project->load('owner');

        return response()->json($project, 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:7',
            'status'      => 'required|string|in:active,on_hold,completed,archived',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'budget'      => 'nullable|numeric|min:0',
        ]);

        if ($data['status'] === 'completed' && $project->status !== 'completed') {
            $data['completed_at'] = now();
        }

        $project->update($data);
        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();
        return response()->json(['ok' => true]);
    }

    public function board(Project $project): View
    {
        $columns = ['todo', 'in_progress', 'review', 'done'];
        $tasks   = [];

        foreach ($columns as $status) {
            $tasks[$status] = $project->tasks()
                ->whereNull('parent_task_id')
                ->where('status', $status)
                ->with(['assignedTo', 'subtasks'])
                ->withCount('comments')
                ->orderBy('sort_order')
                ->get();
        }

        $members = $project->members->merge(collect([$project->owner]))->unique('id');
        $allTasks = $project->tasks()->whereNull('parent_task_id')->get(['id', 'name']);

        return view('modules.project.projects.board', compact('project', 'tasks', 'members', 'allTasks'));
    }

    public function gantt(Project $project): View
    {
        $tasks = $project->tasks()
            ->whereNull('parent_task_id')
            ->with(['assignedTo', 'dependencies'])
            ->orderBy('start_date')
            ->orderBy('sort_order')
            ->get();

        return view('modules.project.projects.gantt', compact('project', 'tasks'));
    }
}
