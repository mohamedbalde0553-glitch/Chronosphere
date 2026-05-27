<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private Department $dept;
    private Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr_manager',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr_employee',  'guard_name' => 'web']);

        $this->dept     = Department::create(['name' => 'IT', 'code' => 'IT']);
        $this->position = Position::create(['title' => 'Dev', 'base_hourly_rate' => 25]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function makeEmployeeFor(User $user): Employee
    {
        return Employee::create([
            'user_id'              => $user->id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-' . fake()->unique()->numerify('###'),
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdi',
            'status'               => 'active',
            'weekly_hours_minutes' => 2400,
        ]);
    }

    // -------------------------------------------------------------------------
    // Utilisateur sans rôle RH
    // -------------------------------------------------------------------------

    public function test_user_without_role_cannot_list_employees(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/employees')->assertStatus(403);
    }

    public function test_user_without_role_cannot_create_employee(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/employees', [])->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // hr_manager — accès complet
    // -------------------------------------------------------------------------

    public function test_hr_manager_can_list_all_employees(): void
    {
        $manager = $this->userWithRole('hr_manager');
        $this->makeEmployeeFor(User::factory()->create());
        $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($manager);

        $this->getJson('/api/employees')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_hr_manager_can_view_any_employee(): void
    {
        $manager  = $this->userWithRole('hr_manager');
        $employee = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($manager);

        $this->getJson("/api/employees/{$employee->id}")->assertOk();
    }

    public function test_hr_manager_can_create_employee(): void
    {
        $manager = $this->userWithRole('hr_manager');
        $newUser = User::factory()->create();
        Sanctum::actingAs($manager);

        $this->postJson('/api/employees', [
            'user_id'              => $newUser->id,
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => 'EMP-NEW',
            'hire_date'            => '2026-06-01',
            'contract_type'        => 'cdi',
            'weekly_hours_minutes' => 2400,
        ])->assertStatus(201);
    }

    public function test_hr_manager_can_update_employee(): void
    {
        $manager  = $this->userWithRole('hr_manager');
        $employee = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($manager);

        $this->putJson("/api/employees/{$employee->id}", [
            'department_id'        => $this->dept->id,
            'position_id'          => $this->position->id,
            'employee_code'        => $employee->employee_code,
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdd',
            'weekly_hours_minutes' => 1800,
            'status'               => 'active',
        ])->assertOk();
    }

    public function test_hr_manager_can_delete_employee(): void
    {
        $manager  = $this->userWithRole('hr_manager');
        $employee = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($manager);

        $this->deleteJson("/api/employees/{$employee->id}")->assertOk();
    }

    // -------------------------------------------------------------------------
    // hr_employee — lecture de sa propre fiche uniquement
    // -------------------------------------------------------------------------

    public function test_hr_employee_index_shows_only_own_record(): void
    {
        $empUser  = $this->userWithRole('hr_employee');
        $own      = $this->makeEmployeeFor($empUser);

        // Un autre employé qui ne doit pas apparaître
        $this->makeEmployeeFor(User::factory()->create());

        Sanctum::actingAs($empUser);

        $this->getJson('/api/employees')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $own->id);
    }

    public function test_hr_employee_can_view_own_record(): void
    {
        $empUser  = $this->userWithRole('hr_employee');
        $own      = $this->makeEmployeeFor($empUser);
        Sanctum::actingAs($empUser);

        $this->getJson("/api/employees/{$own->id}")->assertOk();
    }

    public function test_hr_employee_cannot_view_other_employee(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        $other   = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($empUser);

        $this->getJson("/api/employees/{$other->id}")->assertStatus(403);
    }

    public function test_hr_employee_cannot_create_employee(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        Sanctum::actingAs($empUser);

        $this->postJson('/api/employees', [])->assertStatus(403);
    }

    public function test_hr_employee_cannot_update_employee(): void
    {
        $empUser  = $this->userWithRole('hr_employee');
        $own      = $this->makeEmployeeFor($empUser);
        Sanctum::actingAs($empUser);

        $this->putJson("/api/employees/{$own->id}", [])->assertStatus(403);
    }

    public function test_hr_employee_cannot_delete_employee(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        $own     = $this->makeEmployeeFor($empUser);
        Sanctum::actingAs($empUser);

        $this->deleteJson("/api/employees/{$own->id}")->assertStatus(403);
    }

    public function test_hr_employee_can_view_own_shifts(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        $own     = $this->makeEmployeeFor($empUser);
        Shift::create([
            'employee_id'    => $own->id,
            'start_at'       => '2026-06-01 08:00:00',
            'end_at'         => '2026-06-01 16:00:00',
            'worked_minutes' => 480,
            'status'         => 'completed',
        ]);
        Sanctum::actingAs($empUser);

        $this->getJson("/api/employees/{$own->id}/shifts")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_hr_employee_cannot_view_other_shifts(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        $this->makeEmployeeFor($empUser);
        $other   = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($empUser);

        $this->getJson("/api/employees/{$other->id}/shifts")->assertStatus(403);
    }

    public function test_hr_employee_can_view_own_leave_requests(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        $own     = $this->makeEmployeeFor($empUser);
        LeaveRequest::create([
            'employee_id' => $own->id,
            'type'        => 'conge_paye',
            'start_date'  => '2026-07-01',
            'end_date'    => '2026-07-05',
            'status'      => 'pending',
        ]);
        Sanctum::actingAs($empUser);

        $this->getJson("/api/employees/{$own->id}/leave-requests")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_hr_employee_cannot_view_other_leave_requests(): void
    {
        $empUser = $this->userWithRole('hr_employee');
        $this->makeEmployeeFor($empUser);
        $other   = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($empUser);

        $this->getJson("/api/employees/{$other->id}/leave-requests")->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // super_admin — accès total via before()
    // -------------------------------------------------------------------------

    public function test_super_admin_can_do_everything(): void
    {
        $admin    = $this->userWithRole('super_admin');
        $employee = $this->makeEmployeeFor(User::factory()->create());
        Sanctum::actingAs($admin);

        $this->getJson('/api/employees')->assertOk();
        $this->getJson("/api/employees/{$employee->id}")->assertOk();
        $this->deleteJson("/api/employees/{$employee->id}")->assertOk();
    }
}
