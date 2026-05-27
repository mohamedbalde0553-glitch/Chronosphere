<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\Resource;
use App\Modules\Booking\Models\ResourceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Resource $resource;
    private Resource $approvalResource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = ResourceCategory::create(['name' => 'Salle', 'color' => '#4F46E5']);

        $this->resource = Resource::create([
            'category_id'       => $category->id,
            'name'              => 'Salle Libre',
            'capacity'          => 20,
            'is_active'         => true,
            'requires_approval' => false,
        ]);

        $this->approvalResource = Resource::create([
            'category_id'       => $category->id,
            'name'              => 'Salle Réservée',
            'capacity'          => 50,
            'is_active'         => true,
            'requires_approval' => true,
        ]);
    }

    public function test_guest_is_redirected_from_reservations(): void
    {
        $this->get('/booking/reservations')->assertRedirect('/login');
    }

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)
            ->get('/booking/reservations')
            ->assertOk();
    }

    public function test_store_creates_confirmed_reservation_when_no_approval_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/booking/reservations', [
                'resource_id'     => $this->resource->id,
                'title'           => 'Réunion équipe',
                'start_at'        => '2026-06-01 09:00:00',
                'end_at'          => '2026-06-01 10:00:00',
                'attendees_count' => 5,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('booking_bookings', [
            'resource_id' => $this->resource->id,
            'title'       => 'Réunion équipe',
            'status'      => 'confirmed',
            'user_id'     => $this->user->id,
        ]);
    }

    public function test_store_creates_pending_reservation_when_approval_required(): void
    {
        $this->actingAs($this->user)
            ->postJson('/booking/reservations', [
                'resource_id'     => $this->approvalResource->id,
                'title'           => 'Conférence',
                'start_at'        => '2026-06-02 14:00:00',
                'end_at'          => '2026-06-02 16:00:00',
                'attendees_count' => 30,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('booking_bookings', [
            'resource_id' => $this->approvalResource->id,
            'status'      => 'pending',
        ]);
    }

    public function test_store_requires_mandatory_fields(): void
    {
        $this->actingAs($this->user)
            ->postJson('/booking/reservations', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id', 'title', 'start_at', 'end_at', 'attendees_count']);
    }

    public function test_end_at_must_be_after_start_at(): void
    {
        $this->actingAs($this->user)
            ->postJson('/booking/reservations', [
                'resource_id'     => $this->resource->id,
                'title'           => 'Test',
                'start_at'        => '2026-06-01 10:00:00',
                'end_at'          => '2026-06-01 09:00:00',
                'attendees_count' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_at']);
    }

    public function test_store_returns_409_on_conflict(): void
    {
        Booking::create([
            'resource_id'      => $this->resource->id,
            'user_id'          => $this->user->id,
            'title'            => 'Première résa',
            'start_at'         => '2026-06-01 09:00:00',
            'end_at'           => '2026-06-01 11:00:00',
            'duration_minutes' => 120,
            'attendees_count'  => 3,
            'status'           => 'confirmed',
        ]);

        $this->actingAs($this->user)
            ->postJson('/booking/reservations', [
                'resource_id'     => $this->resource->id,
                'title'           => 'Conflit',
                'start_at'        => '2026-06-01 10:00:00',
                'end_at'          => '2026-06-01 12:00:00',
                'attendees_count' => 2,
            ])
            ->assertStatus(409)
            ->assertJsonStructure(['conflicts']);
    }

    public function test_can_force_override_conflict(): void
    {
        Booking::create([
            'resource_id'      => $this->resource->id,
            'user_id'          => $this->user->id,
            'title'            => 'Existante',
            'start_at'         => '2026-06-03 09:00:00',
            'end_at'           => '2026-06-03 11:00:00',
            'duration_minutes' => 120,
            'attendees_count'  => 2,
            'status'           => 'confirmed',
        ]);

        $this->actingAs($this->user)
            ->postJson('/booking/reservations', [
                'resource_id'     => $this->resource->id,
                'title'           => 'Forcée',
                'start_at'        => '2026-06-03 10:00:00',
                'end_at'          => '2026-06-03 12:00:00',
                'attendees_count' => 1,
                'force'           => true,
            ])
            ->assertStatus(201);
    }

    public function test_can_approve_reservation(): void
    {
        $booking = Booking::create([
            'resource_id'      => $this->approvalResource->id,
            'user_id'          => $this->user->id,
            'title'            => 'En attente',
            'start_at'         => '2026-06-04 09:00:00',
            'end_at'           => '2026-06-04 10:00:00',
            'duration_minutes' => 60,
            'attendees_count'  => 10,
            'status'           => 'pending',
        ]);

        $this->actingAs($this->user)
            ->postJson("/booking/reservations/{$booking->id}/approve")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('booking_bookings', [
            'id'          => $booking->id,
            'status'      => 'confirmed',
            'approved_by' => $this->user->id,
        ]);
    }

    public function test_can_reject_reservation(): void
    {
        $booking = Booking::create([
            'resource_id'      => $this->approvalResource->id,
            'user_id'          => $this->user->id,
            'title'            => 'À rejeter',
            'start_at'         => '2026-06-05 09:00:00',
            'end_at'           => '2026-06-05 10:00:00',
            'duration_minutes' => 60,
            'attendees_count'  => 10,
            'status'           => 'pending',
        ]);

        $this->actingAs($this->user)
            ->postJson("/booking/reservations/{$booking->id}/reject", [
                'rejection_reason' => 'Salle non disponible.',
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('booking_bookings', [
            'id'               => $booking->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Salle non disponible.',
        ]);
    }

    public function test_can_cancel_reservation(): void
    {
        $booking = Booking::create([
            'resource_id'      => $this->resource->id,
            'user_id'          => $this->user->id,
            'title'            => 'À annuler',
            'start_at'         => '2026-06-06 09:00:00',
            'end_at'           => '2026-06-06 10:00:00',
            'duration_minutes' => 60,
            'attendees_count'  => 5,
            'status'           => 'confirmed',
        ]);

        $this->actingAs($this->user)
            ->postJson("/booking/reservations/{$booking->id}/cancel")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('booking_bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_can_delete_reservation(): void
    {
        $booking = Booking::create([
            'resource_id'      => $this->resource->id,
            'user_id'          => $this->user->id,
            'title'            => 'À supprimer',
            'start_at'         => '2026-06-07 09:00:00',
            'end_at'           => '2026-06-07 10:00:00',
            'duration_minutes' => 60,
            'attendees_count'  => 2,
            'status'           => 'confirmed',
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/booking/reservations/{$booking->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSoftDeleted('booking_bookings', ['id' => $booking->id]);
    }
}
