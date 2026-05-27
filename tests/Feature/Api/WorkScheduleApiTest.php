<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\EmployeeScheduleOverride;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $employee_user;
    private Department $dept;
    private Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'hr_manager',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr_employee', 'guard_name' => 'web']);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('hr_manager');

        $this->employee_user = User::factory()->create();
        $this->employee_user->assignRole('hr_employee');

        $this->dept     = Department::create(['name' => 'Production', 'code' => 'PROD']);
        $this->position = Position::create(['title' => 'Opérateur', 'base_hourly_rate' => 18.00]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeSchedule(array $overrides = []): WorkSchedule
    {
        $schedule = WorkSchedule::create(array_merge([
            'name'          => 'Horaire Standard',
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'department_id' => $this->dept->id,
            'color'         => '#3B82F6',
            'is_active'     => true,
        ], $overrides));

        $schedule->days()->create([
            'day_of_week'   => 1, // lundi
            'start_time'    => '09:00',
            'end_time'      => '17:00',
            'break_minutes' => 60,
        ]);

        return $schedule;
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Horaire Test',
            'start_date'    => '2026-06-01',
            'department_id' => $this->dept->id,
            'color'         => '#10B981',
            'is_active'     => true,
            'days'          => [
                [
                    'day_of_week'   => 1,
                    'start_time'    => '08:00',
                    'end_time'      => '16:00',
                    'break_minutes' => 30,
                ],
            ],
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Unauthenticated
    // -------------------------------------------------------------------------

    public function test_unauthenticated_cannot_access_schedules(): void
    {
        $this->getJson('/api/work-schedules')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_schedules(): void
    {
        $this->makeSchedule();
        $this->makeSchedule(['name' => 'Horaire Nuit', 'is_active' => false]);
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/work-schedules')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'start_date', 'is_active', 'days']],
                'meta' => ['total'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_filters_by_department(): void
    {
        $other = Department::create(['name' => 'Logistique', 'code' => 'LOG']);
        $this->makeSchedule();
        $this->makeSchedule(['department_id' => $other->id]);
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/work-schedules?department_id=' . $this->dept->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_filters_by_active_status(): void
    {
        $this->makeSchedule(['is_active' => true]);
        $this->makeSchedule(['is_active' => false]);
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/work-schedules?active=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_hr_employee_can_list_schedules(): void
    {
        $this->makeSchedule();
        Sanctum::actingAs($this->employee_user);

        $this->getJson('/api/work-schedules')->assertOk();
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_schedule_with_days(): void
    {
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/work-schedules/' . $schedule->id)
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'days', 'department']])
            ->assertJsonPath('data.id', $schedule->id)
            ->assertJsonCount(1, 'data.days');
    }

    public function test_show_returns_404_for_missing_schedule(): void
    {
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/work-schedules/9999')->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_manager_can_create_schedule(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/work-schedules', $this->validPayload())
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'days']])
            ->assertJsonPath('data.name', 'Horaire Test')
            ->assertJsonCount(1, 'data.days');

        $this->assertDatabaseHas('hr_work_schedules', ['name' => 'Horaire Test']);
    }

    public function test_store_requires_name(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/work-schedules', $this->validPayload(['name' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_store_requires_at_least_one_day(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/work-schedules', $this->validPayload(['days' => []]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('days');
    }

    public function test_store_validates_day_of_week_range(): void
    {
        Sanctum::actingAs($this->manager);

        $payload         = $this->validPayload();
        $payload['days'] = [['day_of_week' => 8, 'start_time' => '08:00', 'end_time' => '16:00']];

        $this->postJson('/api/work-schedules', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('days.0.day_of_week');
    }

    public function test_hr_employee_cannot_create_schedule(): void
    {
        Sanctum::actingAs($this->employee_user);

        $this->postJson('/api/work-schedules', $this->validPayload())
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_manager_can_update_schedule(): void
    {
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($this->manager);

        $payload = $this->validPayload([
            'name'  => 'Horaire Modifié',
            'days'  => [
                ['day_of_week' => 1, 'start_time' => '07:00', 'end_time' => '15:00', 'break_minutes' => 45],
                ['day_of_week' => 2, 'start_time' => '07:00', 'end_time' => '15:00', 'break_minutes' => 45],
            ],
        ]);

        $this->putJson('/api/work-schedules/' . $schedule->id, $payload)
            ->assertOk()
            ->assertJsonPath('data.name', 'Horaire Modifié')
            ->assertJsonCount(2, 'data.days');

        $this->assertDatabaseHas('hr_work_schedules', ['id' => $schedule->id, 'name' => 'Horaire Modifié']);
        $this->assertDatabaseCount('hr_work_schedule_days', 2);
    }

    public function test_hr_employee_cannot_update_schedule(): void
    {
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($this->employee_user);

        $this->putJson('/api/work-schedules/' . $schedule->id, $this->validPayload())
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_manager_can_delete_schedule(): void
    {
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($this->manager);

        $this->deleteJson('/api/work-schedules/' . $schedule->id)
            ->assertOk()
            ->assertJsonPath('message', 'Horaire supprimé.');

        $this->assertSoftDeleted('hr_work_schedules', ['id' => $schedule->id]);
    }

    public function test_hr_employee_cannot_delete_schedule(): void
    {
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($this->employee_user);

        $this->deleteJson('/api/work-schedules/' . $schedule->id)
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Generate Shifts
    // -------------------------------------------------------------------------

    public function test_generate_shifts_creates_shifts_for_department_employees(): void
    {
        $schedule = $this->makeSchedule(); // lundi actif
        $this->makeEmployee();             // employé du même département
        Sanctum::actingAs($this->manager);

        // 2026-06-01 est un lundi
        $this->postJson('/api/work-schedules/' . $schedule->id . '/generate-shifts', [
            'start_date' => '2026-06-01',
            'end_date'   => '2026-06-01',
        ])
            ->assertOk()
            ->assertJsonStructure(['created'])
            ->assertJsonPath('created', 1);

        $this->assertDatabaseHas('hr_shifts', [
            'start_at' => '2026-06-01 09:00:00',
            'status'   => 'planned',
        ]);
    }

    public function test_generate_shifts_skips_days_without_config(): void
    {
        $schedule = $this->makeSchedule(); // seulement lundi configuré
        $this->makeEmployee();
        Sanctum::actingAs($this->manager);

        // 2026-06-02 est un mardi — pas configuré dans ce schedule
        $this->postJson('/api/work-schedules/' . $schedule->id . '/generate-shifts', [
            'start_date' => '2026-06-02',
            'end_date'   => '2026-06-02',
        ])
            ->assertOk()
            ->assertJsonPath('created', 0);
    }

    public function test_generate_shifts_avoids_duplicates(): void
    {
        $schedule = $this->makeSchedule();
        $this->makeEmployee();
        Sanctum::actingAs($this->manager);

        $payload = ['start_date' => '2026-06-01', 'end_date' => '2026-06-01'];

        $this->postJson('/api/work-schedules/' . $schedule->id . '/generate-shifts', $payload)
            ->assertJsonPath('created', 1);

        // Deuxième appel — ne doit pas créer de doublon
        $this->postJson('/api/work-schedules/' . $schedule->id . '/generate-shifts', $payload)
            ->assertJsonPath('created', 0);
    }

    public function test_generate_shifts_requires_date_range(): void
    {
        $schedule = $this->makeSchedule();
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/work-schedules/' . $schedule->id . '/generate-shifts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    // -------------------------------------------------------------------------
    // Employee Schedule
    // -------------------------------------------------------------------------

    public function test_employee_schedule_returns_department_schedule(): void
    {
        $schedule = $this->makeSchedule(['start_date' => '2026-01-01', 'end_date' => '2026-12-31']);
        $emp      = $this->makeEmployee();
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/employees/' . $emp->id . '/schedule?date=2026-06-15')
            ->assertOk()
            ->assertJsonStructure(['schedule', 'expected_minutes'])
            ->assertJsonPath('schedule.id', $schedule->id);
    }

    public function test_employee_schedule_returns_null_when_no_schedule(): void
    {
        $emp = $this->makeEmployee();
        Sanctum::actingAs($this->manager);

        $this->getJson('/api/employees/' . $emp->id . '/schedule?date=2026-06-15')
            ->assertOk()
            ->assertJsonPath('schedule', null)
            ->assertJsonPath('expected_minutes', 0);
    }

    public function test_employee_schedule_respects_override(): void
    {
        $default  = $this->makeSchedule(['name' => 'Défaut', 'start_date' => '2026-01-01']);
        $override = $this->makeSchedule(['name' => 'Override', 'start_date' => '2026-01-01']);
        $emp      = $this->makeEmployee();

        EmployeeScheduleOverride::create([
            'employee_id'         => $emp->id,
            'work_schedule_id'    => $override->id,
            'override_start_date' => '2026-06-01',
            'override_end_date'   => '2026-06-30',
        ]);

        Sanctum::actingAs($this->manager);

        $this->getJson('/api/employees/' . $emp->id . '/schedule?date=2026-06-15')
            ->assertOk()
            ->assertJsonPath('schedule.id', $override->id);
    }

    // -------------------------------------------------------------------------
    // Store Override
    // -------------------------------------------------------------------------

    public function test_manager_can_create_schedule_override(): void
    {
        $schedule = $this->makeSchedule();
        $emp      = $this->makeEmployee();
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/employees/' . $emp->id . '/schedule-override', [
            'work_schedule_id'    => $schedule->id,
            'override_start_date' => '2026-07-01',
            'override_end_date'   => '2026-07-31',
            'reason'              => 'Formation spéciale',
        ])
            ->assertStatus(201)
            ->assertJsonPath('employee_id', $emp->id)
            ->assertJsonPath('work_schedule_id', $schedule->id);

        $this->assertDatabaseHas('hr_employee_schedule_overrides', [
            'employee_id'      => $emp->id,
            'work_schedule_id' => $schedule->id,
        ]);
    }

    public function test_store_override_requires_valid_schedule(): void
    {
        $emp = $this->makeEmployee();
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/employees/' . $emp->id . '/schedule-override', [
            'work_schedule_id'    => 9999,
            'override_start_date' => '2026-07-01',
            'override_end_date'   => '2026-07-31',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('work_schedule_id');
    }

    public function test_hr_employee_cannot_create_override(): void
    {
        $schedule = $this->makeSchedule();
        $emp      = $this->makeEmployee();
        Sanctum::actingAs($this->employee_user);

        $this->postJson('/api/employees/' . $emp->id . '/schedule-override', [
            'work_schedule_id'    => $schedule->id,
            'override_start_date' => '2026-07-01',
            'override_end_date'   => '2026-07-31',
        ])
            ->assertStatus(403);
    }
}
