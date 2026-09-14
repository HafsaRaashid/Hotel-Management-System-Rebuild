<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-003 (BL-002/BL-003). AC-N references are to
 * .specclaw/changes/003-room-rate-management/spec.md.
 *
 * Only the CSRF tests override app('env') to 'production' - see
 * tests/Feature/CsrfProtectionTest.php's comment on why every other test
 * relies on the default APP_ENV=testing bypass instead.
 */
class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_shows_room_category_and_status(): void
    {
        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 0]);

        $response = $this->get(route('rooms.index'));

        $response->assertStatus(200);
        $response->assertSee('Single_101');
        $response->assertSee($category->name);
        $response->assertSee('Available');
    }

    public function test_list_filters_by_category(): void
    {
        $single = RoomCategory::where('name', 'Single Room')->firstOrFail();
        $double = RoomCategory::where('name', 'Double Room')->firstOrFail();

        Room::create(['room' => 'Single_101', 'category_id' => $single->id, 'status' => 0]);
        Room::create(['room' => 'Double_201', 'category_id' => $double->id, 'status' => 0]);

        $response = $this->get(route('rooms.index', ['category_id' => $single->id]));

        $response->assertSee('Single_101');
        $response->assertDontSee('Double_201');
    }

    public function test_create_persists_room_category_and_status(): void
    {
        $category = RoomCategory::first();

        $this->post(route('rooms.store'), [
            'room' => 'Deluxe_301',
            'category_id' => $category->id,
            'status' => 1,
        ])->assertRedirect(route('rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'room' => 'Deluxe_301',
            'category_id' => $category->id,
            'status' => 1,
        ]);
    }

    public function test_create_rejects_nonexistent_category_id(): void
    {
        $this->post(route('rooms.store'), [
            'room' => 'Bad_101',
            'category_id' => 9999,
            'status' => 0,
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('rooms', ['room' => 'Bad_101']);
    }

    public function test_edit_updates_room_category_and_status(): void
    {
        $originalCategory = RoomCategory::where('name', 'Single Room')->firstOrFail();
        $newCategory = RoomCategory::where('name', 'Double Room')->firstOrFail();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $originalCategory->id, 'status' => 0]);

        $this->put(route('rooms.update', $room), [
            'room' => 'Single_101B',
            'category_id' => $newCategory->id,
            'status' => 1,
        ])->assertRedirect(route('rooms.index'));

        $room->refresh();

        $this->assertSame('Single_101B', $room->room);
        $this->assertSame($newCategory->id, $room->category_id);
        $this->assertSame(1, $room->status);
    }

    public function test_store_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $category = RoomCategory::first();

        $this->post(route('rooms.store'), [
            'room' => 'No_Token',
            'category_id' => $category->id,
            'status' => 0,
        ])->assertStatus(419);

        $this->assertDatabaseMissing('rooms', ['room' => 'No_Token']);
    }

    public function test_update_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 0]);

        $this->put(route('rooms.update', $room), [
            'room' => 'Changed',
            'category_id' => $category->id,
            'status' => 1,
        ])->assertStatus(419);

        $room->refresh();
        $this->assertSame('Single_101', $room->room);
    }
}
