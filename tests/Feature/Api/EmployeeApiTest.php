<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;
    private Department $dept;
    private Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);

        $this->actor = User::factory()->create();
        $this->actor->assignRole('hr_manager');

        $this->dept     = Department::create(['name' => 'Informatique', 'code' => 'IT']);
        $this->position = Position::create(['title' => 'Développeur', 'base_hourly_rate' => 25.00]);
    }

    private function makeEmployee(array $overrides = []): Employee
    {
        $user = User::factory()->create();

        return Employee::create(array_merge([
            'user_id'              => $user->id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-' . fake()->unique()->numerify('###'),
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdi',
            'status'               => 'active',
            'weekly_hours_minutes' => 2400,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_unauthenticated_cannot_list_employees(): void
    {
        $this->getJson('/api/employees')->assertStatus(401);
    }

    public function test_index_returns_paginated_employees(): void
    {
        $this->makeEmployee();
        $this->makeEmployee();
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'employee_code', 'status', 'user', 'department', 'position']],
                'meta' => ['total'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_filters_by_department(): void
    {
        $otherDept = Department::create(['name' => 'RH', 'code' => 'RH']);
        $this->makeEmployee();
        $this->makeEmployee(['department_id' => $otherDept->id]);
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees?department_id=' . $this->dept->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_filters_by_status(): void
    {
        $this->makeEmployee(['status' => 'active']);
        $this->makeEmployee(['status' => 'inactive']);
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees?status=inactive')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.status', 'inactive');
    }

    public function test_index_searches_by_employee_code(): void
    {
        $this->makeEmployee(['employee_code' => 'EMP-FIND']);
        $this->makeEmployee(['employee_code' => 'EMP-OTHER']);
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees?search=FIND')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.employee_code', 'EMP-FIND');
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_employee_with_skills(): void
    {
        $employee = $this->makeEmployee();
        $skill    = Skill::create(['name' => 'PHP', 'category' => 'backend']);
        $employee->skills()->attach($skill->id, ['level' => 3]);
        Sanctum::actingAs($this->actor);

        $this->getJson("/api/employees/{$employee->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $employee->id)
            ->assertJsonStructure(['data' => ['skills']])
            ->assertJsonPath('data.skills.0.name', 'PHP')
            ->assertJsonPath('data.skills.0.level', 3);
    }

    public function test_show_returns_404_for_unknown_employee(): void
    {
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees/9999')->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_employee(): void
    {
        $newUser = User::factory()->create();
        Sanctum::actingAs($this->actor);

        $this->postJson('/api/employees', [
            'user_id'              => $newUser->id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-NEW',
            'hire_date'            => '2026-06-01',
            'contract_type'        => 'cdi',
            'weekly_hours_minutes' => 2400,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.employee_code', 'EMP-NEW')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('hr_employees', ['employee_code' => 'EMP-NEW']);
    }

    public function test_store_requires_mandatory_fields(): void
    {
        Sanctum::actingAs($this->actor);

        $this->postJson('/api/employees', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'user_id', 'department_id', 'position_id',
                'employee_code', 'hire_date', 'contract_type', 'weekly_hours_minutes',
            ]);
    }

    public function test_store_rejects_duplicate_employee_code(): void
    {
        $this->makeEmployee(['employee_code' => 'EMP-DUP']);
        $newUser = User::factory()->create();
        Sanctum::actingAs($this->actor);

        $this->postJson('/api/employees', [
            'user_id'              => $newUser->id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-DUP',
            'hire_date'            => '2026-01-01',
            'contract_type'        => 'cdd',
            'weekly_hours_minutes' => 1800,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['employee_code']);
    }

    public function test_store_rejects_user_already_employee(): void
    {
        $existing = $this->makeEmployee();
        Sanctum::actingAs($this->actor);

        $this->postJson('/api/employees', [
            'user_id'              => $existing->user_id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-X',
            'hire_date'            => '2026-01-01',
            'contract_type'        => 'cdi',
            'weekly_hours_minutes' => 2400,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_store_rejects_invalid_contract_type(): void
    {
        $newUser = User::factory()->create();
        Sanctum::actingAs($this->actor);

        $this->postJson('/api/employees', [
            'user_id'              => $newUser->id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-Y',
            'hire_date'            => '2026-01-01',
            'contract_type'        => 'stage',
            'weekly_hours_minutes' => 2400,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contract_type']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_employee(): void
    {
        $employee = $this->makeEmployee(['employee_code' => 'EMP-UPD']);
        $newDept  = Department::create(['name' => 'Marketing', 'code' => 'MKT']);
        Sanctum::actingAs($this->actor);

        $this->putJson("/api/employees/{$employee->id}", [
            'department_id'        => $newDept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-UPD',
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdd',
            'weekly_hours_minutes' => 1800,
            'status'               => 'inactive',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.contract_type', 'cdd');

        $this->assertDatabaseHas('hr_employees', [
            'id'            => $employee->id,
            'department_id' => $newDept->id,
            'status'        => 'inactive',
        ]);
    }

    public function test_update_rejects_invalid_status(): void
    {
        $employee = $this->makeEmployee();
        Sanctum::actingAs($this->actor);

        $this->putJson("/api/employees/{$employee->id}", [
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => $employee->employee_code,
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdi',
            'weekly_hours_minutes' => 2400,
            'status'               => 'fired',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_employee(): void
    {
        $employee = $this->makeEmployee();
        Sanctum::actingAs($this->actor);

        $this->deleteJson("/api/employees/{$employee->id}")
            ->assertOk()
            ->assertJson(['message' => 'Employé supprimé.']);

        $this->assertSoftDeleted('hr_employees', ['id' => $employee->id]);
    }

    public function test_destroyed_employee_not_in_index(): void
    {
        $employee = $this->makeEmployee();
        $employee->delete();
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    // -------------------------------------------------------------------------
    // Reference data
    // -------------------------------------------------------------------------

    public function test_departments_returns_list(): void
    {
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/departments')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'code']]]);
    }

    public function test_positions_returns_list(): void
    {
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/positions')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'title']]]);
    }
}
