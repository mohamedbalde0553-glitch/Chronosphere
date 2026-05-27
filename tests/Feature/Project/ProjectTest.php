<?php

namespace Tests\Feature\Project;

use App\Models\User;
use App\Modules\Project\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_is_redirected_from_project_index(): void
    {
        $this->get('/project')->assertRedirect('/login');
    }

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)
            ->get('/project')
            ->assertOk();
    }

    public function test_can_create_project(): void
    {
        $this->actingAs($this->user)
            ->postJson('/project/projects', [
                'name'   => 'Projet Test',
                'status' => 'active',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Projet Test', 'status' => 'active']);

        $this->assertDatabaseHas('project_projects', ['name' => 'Projet Test', 'owner_id' => $this->user->id]);
    }

    public function test_create_project_requires_name_and_status(): void
    {
        $this->actingAs($this->user)
            ->postJson('/project/projects', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_project_status_must_be_valid(): void
    {
        $this->actingAs($this->user)
            ->postJson('/project/projects', [
                'name'   => 'Test',
                'status' => 'invalid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_can_update_project(): void
    {
        $project = Project::create([
            'owner_id' => $this->user->id,
            'name'     => 'Avant',
            'status'   => 'active',
        ]);

        $this->actingAs($this->user)
            ->putJson("/project/projects/{$project->id}", [
                'name'   => 'Après',
                'status' => 'on_hold',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Après', 'status' => 'on_hold']);

        $this->assertDatabaseHas('project_projects', ['id' => $project->id, 'name' => 'Après']);
    }

    public function test_updating_to_completed_sets_completed_at(): void
    {
        $project = Project::create([
            'owner_id' => $this->user->id,
            'name'     => 'Projet A',
            'status'   => 'active',
        ]);

        $this->assertNull($project->completed_at);

        $this->actingAs($this->user)
            ->putJson("/project/projects/{$project->id}", [
                'name'   => 'Projet A',
                'status' => 'completed',
            ])
            ->assertOk();

        $this->assertNotNull($project->fresh()->completed_at);
    }

    public function test_can_delete_project(): void
    {
        $project = Project::create([
            'owner_id' => $this->user->id,
            'name'     => 'À supprimer',
            'status'   => 'active',
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/project/projects/{$project->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSoftDeleted('project_projects', ['id' => $project->id]);
    }

    public function test_board_view_renders(): void
    {
        $project = Project::create([
            'owner_id' => $this->user->id,
            'name'     => 'Kanban',
            'status'   => 'active',
        ]);

        $this->actingAs($this->user)
            ->get("/project/projects/{$project->id}/board")
            ->assertOk();
    }

    public function test_gantt_view_renders(): void
    {
        $project = Project::create([
            'owner_id' => $this->user->id,
            'name'     => 'Gantt',
            'status'   => 'active',
        ]);

        $this->actingAs($this->user)
            ->get("/project/projects/{$project->id}/gantt")
            ->assertOk();
    }
}
