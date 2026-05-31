<?php

namespace Tests\Feature\Timetable;

use App\Models\User;
use App\Modules\Timetable\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRolePermissions;

class RoomTest extends TestCase
{
    use RefreshDatabase, SeedsRolePermissions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seedRole('uni_admin', [
            'timetable.view', 'timetable.create', 'timetable.edit',
            'timetable.delete', 'timetable.manage_rooms', 'timetable.manage_teachers',
        ], $this->user);
    }

    public function test_guest_is_redirected_from_rooms(): void
    {
        $this->get('/timetable/rooms')->assertRedirect('/login');
    }

    public function test_index_returns_ok(): void
    {
        $this->actingAs($this->user)
            ->get('/timetable/rooms')
            ->assertOk();
    }

    public function test_can_create_room(): void
    {
        $this->actingAs($this->user)
            ->postJson('/timetable/rooms', [
                'code'     => 'TD-001',
                'name'     => 'Salle TD 001',
                'capacity' => 30,
                'type'     => 'td',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['code' => 'TD-001', 'capacity' => 30]);

        $this->assertDatabaseHas('uni_rooms', ['code' => 'TD-001']);
    }

    public function test_create_room_requires_all_fields(): void
    {
        $this->actingAs($this->user)
            ->postJson('/timetable/rooms', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'capacity', 'type']);
    }

    public function test_room_type_must_be_valid(): void
    {
        $this->actingAs($this->user)
            ->postJson('/timetable/rooms', [
                'code'     => 'XX-001',
                'name'     => 'Salle X',
                'capacity' => 20,
                'type'     => 'invalid_type',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_room_code_must_be_unique(): void
    {
        Room::create(['code' => 'TD-DUP', 'name' => 'Salle A', 'capacity' => 20, 'type' => 'td', 'is_active' => true]);

        $this->actingAs($this->user)
            ->postJson('/timetable/rooms', [
                'code'     => 'TD-DUP',
                'name'     => 'Salle B',
                'capacity' => 25,
                'type'     => 'td',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_update_room(): void
    {
        $room = Room::create(['code' => 'TD-002', 'name' => 'Ancien nom', 'capacity' => 20, 'type' => 'td', 'is_active' => true]);

        $this->actingAs($this->user)
            ->putJson("/timetable/rooms/{$room->id}", [
                'code'     => 'TD-002',
                'name'     => 'Nouveau nom',
                'capacity' => 35,
                'type'     => 'tp',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Nouveau nom', 'capacity' => 35]);

        $this->assertDatabaseHas('uni_rooms', ['id' => $room->id, 'name' => 'Nouveau nom', 'capacity' => 35]);
    }

    public function test_update_allows_same_code_on_same_room(): void
    {
        $room = Room::create(['code' => 'TD-003', 'name' => 'Salle C', 'capacity' => 20, 'type' => 'td', 'is_active' => true]);

        $this->actingAs($this->user)
            ->putJson("/timetable/rooms/{$room->id}", [
                'code'     => 'TD-003',
                'name'     => 'Salle C modifiée',
                'capacity' => 40,
                'type'     => 'td',
            ])
            ->assertOk();
    }

    public function test_can_delete_room(): void
    {
        $room = Room::create(['code' => 'TD-DEL', 'name' => 'À supprimer', 'capacity' => 20, 'type' => 'td', 'is_active' => true]);

        $this->actingAs($this->user)
            ->deleteJson("/timetable/rooms/{$room->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('uni_rooms', ['id' => $room->id]);
    }
}
