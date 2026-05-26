<?php

namespace App\Modules\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\Task;
use App\Modules\Project\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:191',
            'description'       => 'nullable|string',
            'status'            => 'required|string|in:todo,in_progress,review,done,cancelled',
            'priority'          => 'required|string|in:low,medium,high,urgent',
            'assigned_to'       => 'nullable|exists:users,id',
            'start_date'        => 'nullable|date',
            'due_date'          => 'nullable|date',
            'estimated_minutes' => 'nullable|integer|min:0',
            'parent_task_id'    => 'nullable|exists:project_tasks,id',
            'color'             => 'nullable|string|max:7',
            'progress'          => 'nullable|integer|min:0|max:100',
        ]);

        $data['project_id'] = $project->id;
        $data['created_by'] = auth()->id();
        $data['sort_order'] = Task::where('project_id', $project->id)
            ->where('status', $data['status'])
            ->max('sort_order') + 1;

        $task = Task::create($data);
        $task->load('assignedTo');
        $task->loadCount(['comments', 'subtasks']);

        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        $task->load(['assignedTo', 'createdBy', 'subtasks.assignedTo', 'comments.user']);
        $task->loadCount(['comments', 'subtasks']);
        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'sometimes|required|string|max:191',
            'description'       => 'nullable|string',
            'status'            => 'sometimes|required|string|in:todo,in_progress,review,done,cancelled',
            'priority'          => 'sometimes|required|string|in:low,medium,high,urgent',
            'assigned_to'       => 'nullable|exists:users,id',
            'start_date'        => 'nullable|date',
            'due_date'          => 'nullable|date',
            'estimated_minutes' => 'nullable|integer|min:0',
            'progress'          => 'nullable|integer|min:0|max:100',
            'sort_order'        => 'nullable|integer|min:0',
            'color'             => 'nullable|string|max:7',
        ]);

        if (isset($data['status']) && $data['status'] === 'done' && $task->status !== 'done') {
            $data['completed_at'] = now();
            $data['progress']     = 100;
        } elseif (isset($data['status']) && $data['status'] !== 'done') {
            $data['completed_at'] = null;
        }

        $task->update($data);
        return response()->json($task->fresh('assignedTo'));
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $items = $request->validate([
            'items'              => 'required|array',
            'items.*.id'         => 'required|exists:project_tasks,id',
            'items.*.status'     => 'required|string|in:todo,in_progress,review,done,cancelled',
            'items.*.sort_order' => 'required|integer|min:0',
        ])['items'];

        foreach ($items as $item) {
            Task::where('id', $item['id'])->update([
                'status'     => $item['status'],
                'sort_order' => $item['sort_order'],
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function storeComment(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'content'           => 'required|string',
            'parent_comment_id' => 'nullable|exists:project_task_comments,id',
        ]);

        $comment = TaskComment::create([
            'task_id'           => $task->id,
            'user_id'           => auth()->id(),
            'content'           => $data['content'],
            'parent_comment_id' => $data['parent_comment_id'] ?? null,
        ]);

        $comment->load('user');
        return response()->json($comment, 201);
    }

    public function destroyComment(TaskComment $comment): JsonResponse
    {
        $comment->delete();
        return response()->json(['ok' => true]);
    }
}
