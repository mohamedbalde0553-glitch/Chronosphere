<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Models\ShiftType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeSubResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);

        $this->actor = User::factory()->create();
        $this->actor->assignRole('hr_manager');

        $dept     = Department::create(['name' => 'Informatique', 'code' => 'IT']);
        $position = Position::create(['title' => 'Développeur', 'base_hourly_rate' => 25.00]);
        $empUser  = User::factory()->create();

        $this->employee = Employee::create([
            'user_id'              => $empUser->id,
            'department_id'        => $dept->id,
            'position_id'          => $position->id,
            'employee_code'        => 'EMP-001',
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdi',
            'status'               => 'active',
            'weekly_hours_minutes' => 2400,
        ]);
    }

    // -------------------------------------------------------------------------
    // Shifts
    // -------------------------------------------------------------------------

    public function test_shifts_returns_paginated_list(): void
    {
        $shiftType = ShiftType::create([
            'name'               => 'Matin',
            'start_time'         => '08:00',
            'end_time'           => '16:00',
            'color'              => '#10B981',
            'overtime_multiplier'=> 1.25,
        ]);

        Shift::create([
            'employee_id'    => $this->employee->id,
            'shift_type_id'  => $shiftType->id,
            'start_at'       => '2026-06-01 08:00:00',
            'end_at'         => '2026-06-01 16:00:00',
            'worked_minutes' => 480,
            'status'         => 'completed',
        ]);

        Shift::create([
            'employee_id'    => $this->employee->id,
            'shift_type_id'  => $shiftType->id,
            'start_at'       => '2026-06-02 08:00:00',
            'end_at'         => '2026-06-02 16:00:00',
            'worked_minutes' => 480,
            'status'         => 'planned',
        ]);

        Sanctum::actingAs($this->actor);

        $this->getJson("/api/employees/{$this->employee->id}/shifts")
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure([
                'data' => [['id', 'start_at', 'end_at', 'status', 'shift_type']],
            ]);
    }

    public function test_shifts_filters_by_date_range(): void
    {
        Shift::create([
            'employee_id'    => $this->employee->id,
            'start_at'       => '2026-06-01 08:00:00',
            'end_at'         => '2026-06-01 16:00:00',
            'worked_minutes' => 480,
            'status'         => 'completed',
        ]);

        // Hors plage
        Shift::create([
            'employee_id'    => $this->employee->id,
            'start_at'       => '2026-07-15 08:00:00',
            'end_at'         => '2026-07-15 16:00:00',
            'worked_minutes' => 480,
            'status'         => 'planned',
        ]);

        Sanctum::actingAs($this->actor);

        $this->getJson("/api/employees/{$this->employee->id}/shifts?from=2026-06-01+00:00:00&to=2026-06-30+23:59:59")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_shifts_returns_empty_for_other_employee(): void
    {
        $otherUser = User::factory()->create();
        $dept      = Department::first();
        $position  = Position::first();

        $other = Employee::create([
            'user_id'              => $otherUser->id,
            'department_id'        => $dept->id,
            'position_id'          => $position->id,
            'employee_code'        => 'EMP-002',
            'hire_date'            => '2024-01-01',
            'contract_type'        => 'cdi',
            'status'               => 'active',
            'weekly_hours_minutes' => 2400,
        ]);

        Shift::create([
            'employee_id'    => $other->id,
            'start_at'       => '2026-06-01 08:00:00',
            'end_at'         => '2026-06-01 16:00:00',
            'worked_minutes' => 480,
            'status'         => 'planned',
        ]);

        Sanctum::actingAs($this->actor);

        $this->getJson("/api/employees/{$this->employee->id}/shifts")
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    // -------------------------------------------------------------------------
    // Leave requests
    // -------------------------------------------------------------------------

    public function test_leave_requests_returns_paginated_list(): void
    {
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'conge_paye',
            'start_date'  => '2026-07-01',
            'end_date'    => '2026-07-05',
            'status'      => 'pending',
        ]);

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'rtt',
            'start_date'  => '2026-08-01',
            'end_date'    => '2026-08-02',
            'status'      => 'approved',
        ]);

        Sanctum::actingAs($this->actor);

        $this->getJson("/api/employees/{$this->employee->id}/leave-requests")
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure([
                'data' => [['id', 'type', 'start_date', 'end_date', 'status']],
            ]);
    }

    public function test_leave_requests_filter_by_status(): void
    {
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'conge_paye',
            'start_date'  => '2026-07-01',
            'end_date'    => '2026-07-05',
            'status'      => 'pending',
        ]);

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'rtt',
            'start_date'  => '2026-08-01',
            'end_date'    => '2026-08-02',
            'status'      => 'approved',
        ]);

        Sanctum::actingAs($this->actor);

        $this->getJson("/api/employees/{$this->employee->id}/leave-requests?status=pending")
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_leave_requests_returns_404_for_unknown_employee(): void
    {
        Sanctum::actingAs($this->actor);

        $this->getJson('/api/employees/9999/leave-requests')->assertStatus(404);
    }
}
