<?php

namespace Tests\Feature\Shifts;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_is_redirected_from_departments(): void
    {
        $this->get('/shifts/departments')->assertRedirect('/login');
    }

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)
            ->get('/shifts/departments')
            ->assertOk();
    }

    public function test_can_create_department(): void
    {
        $this->actingAs($this->user)
            ->postJson('/shifts/departments', [
                'name' => 'Informatique',
                'code' => 'IT',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Informatique', 'code' => 'IT']);

        $this->assertDatabaseHas('hr_departments', ['name' => 'Informatique', 'code' => 'IT']);
    }

    public function test_create_department_requires_name_and_code(): void
    {
        $this->actingAs($this->user)
            ->postJson('/shifts/departments', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'code']);
    }

    public function test_department_code_must_be_unique(): void
    {
        Department::create(['name' => 'RH', 'code' => 'HR']);

        $this->actingAs($this->user)
            ->postJson('/shifts/departments', [
                'name' => 'Ressources Humaines',
                'code' => 'HR',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_update_department(): void
    {
        $dept = Department::create(['name' => 'Ancien', 'code' => 'OLD']);

        $this->actingAs($this->user)
            ->putJson("/shifts/departments/{$dept->id}", [
                'name' => 'Nouveau',
                'code' => 'NEW',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Nouveau', 'code' => 'NEW']);

        $this->assertDatabaseHas('hr_departments', ['id' => $dept->id, 'name' => 'Nouveau']);
    }

    public function test_update_allows_same_code_on_same_department(): void
    {
        $dept = Department::create(['name' => 'Dev', 'code' => 'DEV']);

        $this->actingAs($this->user)
            ->putJson("/shifts/departments/{$dept->id}", [
                'name'  => 'Développement',
                'code'  => 'DEV',
            ])
            ->assertOk();
    }

    public function test_can_delete_department(): void
    {
        $dept = Department::create(['name' => 'À supprimer', 'code' => 'DEL']);

        $this->actingAs($this->user)
            ->deleteJson("/shifts/departments/{$dept->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('hr_departments', ['id' => $dept->id]);
    }
}
