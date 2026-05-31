<?php

namespace Tests\Feature\Project;

use App\Models\User;
use App\Modules\Project\Models\Project;
use App\Modules\Project\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolePermissions;

class TaskTest extends TestCase
{
    use RefreshDatabase, SeedsRolePermissions;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = User::factory()->create();
        $this->seedRole('proj_manager', [
            'project.view', 'project.create', 'project.edit',
            'project.delete', 'project.manage_team', 'project.manage_all',
        ], $this->user);
        $this->project = Project::create([
            'owner_id' => $this->user->id,
            'name'     => 'Projet Test',
            'status'   => 'active',
        ]);
    }

    public function test_can_create_task(): void
    {
        $this->actingAs($this->user)
            ->postJson("/project/projects/{$this->project->id}/tasks", [
                'name'     => 'Ma tâche',
                'status'   => 'todo',
                'priority' => 'medium',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Ma tâche', 'status' => 'todo']);

        $this->assertDatabaseHas('project_tasks', [
            'name'       => 'Ma tâche',
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_create_task_requires_name_status_priority(): void
    {
        $this->actingAs($this->user)
            ->postJson("/project/projects/{$this->project->id}/tasks", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status', 'priority']);
    }

    public function test_task_status_must_be_valid(): void
    {
        $this->actingAs($this->user)
            ->postJson("/project/projects/{$this->project->id}/tasks", [
                'name'     => 'Tâche',
                'status'   => 'inexistant',
                'priority' => 'low',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_can_show_task(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'name'       => 'Tâche visible',
            'status'     => 'todo',
            'priority'   => 'low',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->getJson("/project/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $task->id, 'name' => 'Tâche visible']);
    }

    public function test_can_update_task(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'name'       => 'Avant',
            'status'     => 'todo',
            'priority'   => 'low',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->putJson("/project/tasks/{$task->id}", [
                'name'     => 'Après',
                'status'   => 'in_progress',
                'priority' => 'high',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Après']);

        $this->assertDatabaseHas('project_tasks', ['id' => $task->id, 'name' => 'Après', 'status' => 'in_progress']);
    }

    public function test_updating_task_to_done_sets_completed_at(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'name'       => 'Terminée',
            'status'     => 'in_progress',
            'priority'   => 'medium',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->putJson("/project/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk();

        $fresh = $task->fresh();
        $this->assertNotNull($fresh->completed_at);
        $this->assertEquals(100, $fresh->progress);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'name'       => 'À supprimer',
            'status'     => 'todo',
            'priority'   => 'low',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/project/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSoftDeleted('project_tasks', ['id' => $task->id]);
    }

    public function test_can_post_comment_on_task(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'name'       => 'Tâche commentée',
            'status'     => 'todo',
            'priority'   => 'low',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->postJson("/project/tasks/{$task->id}/comments", [
                'content' => 'Super avancement !',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['content' => 'Super avancement !']);

        $this->assertDatabaseHas('project_task_comments', [
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'content' => 'Super avancement !',
        ]);
    }

    public function test_comment_requires_content(): void
    {
        $task = Task::create([
            'project_id' => $this->project->id,
            'name'       => 'Tâche',
            'status'     => 'todo',
            'priority'   => 'low',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->postJson("/project/tasks/{$task->id}/comments", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }
}
