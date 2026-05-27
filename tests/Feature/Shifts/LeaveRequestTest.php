<?php

namespace Tests\Feature\Shifts;

use App\Models\User;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use App\Modules\Shifts\Models\LeaveRequest;
use App\Modules\Shifts\Models\Position;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $dept     = Department::create(['name' => 'Informatique', 'code' => 'IT']);
        $position = Position::create(['title' => 'Développeur', 'base_hourly_rate' => 25.00]);

        $empUser = User::factory()->create();
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

    public function test_guest_is_redirected_from_leaves(): void
    {
        $this->get('/shifts/leaves')->assertRedirect('/login');
    }

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)
            ->get('/shifts/leaves')
            ->assertOk();
    }

    public function test_can_create_leave_request(): void
    {
        $this->actingAs($this->user)
            ->postJson('/shifts/leaves', [
                'employee_id' => $this->employee->id,
                'type'        => 'conge_paye',
                'start_date'  => '2026-07-01',
                'end_date'    => '2026-07-10',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['status' => 'pending']);

        $this->assertDatabaseHas('hr_leave_requests', [
            'employee_id' => $this->employee->id,
            'type'        => 'conge_paye',
            'status'      => 'pending',
        ]);
    }

    public function test_create_leave_requires_mandatory_fields(): void
    {
        $this->actingAs($this->user)
            ->postJson('/shifts/leaves', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['employee_id', 'type', 'start_date', 'end_date']);
    }

    public function test_leave_type_must_be_valid(): void
    {
        $this->actingAs($this->user)
            ->postJson('/shifts/leaves', [
                'employee_id' => $this->employee->id,
                'type'        => 'vacances',
                'start_date'  => '2026-07-01',
                'end_date'    => '2026-07-05',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_end_date_must_be_after_or_equal_start_date(): void
    {
        $this->actingAs($this->user)
            ->postJson('/shifts/leaves', [
                'employee_id' => $this->employee->id,
                'type'        => 'rtt',
                'start_date'  => '2026-07-10',
                'end_date'    => '2026-07-05',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_can_approve_leave_and_sets_status(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'maladie',
            'start_date'  => '2026-08-01',
            'end_date'    => '2026-08-05',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->user)
            ->postJson("/shifts/leaves/{$leave->id}/approve")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('hr_leave_requests', [
            'id'           => $leave->id,
            'status'       => 'approved',
            'validated_by' => $this->user->id,
        ]);
    }

    public function test_approving_leave_cancels_overlapping_shifts(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'conge_paye',
            'start_date'  => '2026-08-10',
            'end_date'    => '2026-08-15',
            'status'      => 'pending',
        ]);

        // Shift qui chevauche le congé
        $shift = Shift::create([
            'employee_id'   => $this->employee->id,
            'start_at'      => '2026-08-12 08:00:00',
            'end_at'        => '2026-08-12 16:00:00',
            'status'        => 'planned',
            'worked_minutes'=> 0,
        ]);

        // Shift hors congé (ne doit pas être annulé)
        $shiftOutside = Shift::create([
            'employee_id'   => $this->employee->id,
            'start_at'      => '2026-08-20 08:00:00',
            'end_at'        => '2026-08-20 16:00:00',
            'status'        => 'planned',
            'worked_minutes'=> 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/shifts/leaves/{$leave->id}/approve");

        $response->assertOk()
            ->assertJsonFragment(['shifts_cancelled' => 1]);

        $this->assertDatabaseHas('hr_shifts', ['id' => $shift->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('hr_shifts', ['id' => $shiftOutside->id, 'status' => 'planned']);
    }

    public function test_can_reject_leave(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'rtt',
            'start_date'  => '2026-09-01',
            'end_date'    => '2026-09-03',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->user)
            ->postJson("/shifts/leaves/{$leave->id}/reject", [
                'rejection_reason' => 'Période chargée.',
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('hr_leave_requests', [
            'id'               => $leave->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Période chargée.',
        ]);
    }

    public function test_can_delete_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'type'        => 'sans_solde',
            'start_date'  => '2026-10-01',
            'end_date'    => '2026-10-02',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/shifts/leaves/{$leave->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSoftDeleted('hr_leave_requests', ['id' => $leave->id]);
    }
}
