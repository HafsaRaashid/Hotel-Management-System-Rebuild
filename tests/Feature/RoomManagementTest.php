<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
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

    private function makeBookingReferencing(Room $room, int $status): Booking
    {
        $customer = Customer::create([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => (string) random_int(1000000, 9999999),
            'address' => '1 Test St',
            'charges' => 0,
        ]);

        return Booking::create([
            'ref_no' => Booking::generateUniqueRefNo(),
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'mail' => $customer->mail,
            'phone' => $customer->phone,
            'category_id' => $room->category_id,
            'room_id' => $room->id,
            'adult' => 1,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-03',
            'days_of_stay' => 2,
            'status' => $status,
        ]);
    }

    public function test_delete_removes_unreferenced_room(): void
    {
        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 0]);

        $this->delete(route('rooms.destroy', $room))->assertRedirect(route('rooms.index'));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_delete_rejects_room_referenced_by_active_booking(): void
    {
        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 1]);
        $this->makeBookingReferencing($room, Booking::STATUS_CHECKED_IN);

        $this->delete(route('rooms.destroy', $room))->assertRedirect(route('rooms.index'));

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_delete_allows_room_referenced_only_by_checked_out_or_cancelled_booking(): void
    {
        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 0]);
        $this->makeBookingReferencing($room, Booking::STATUS_CHECKED_OUT);

        $this->delete(route('rooms.destroy', $room))->assertRedirect(route('rooms.index'));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_destroy_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 0]);

        $this->delete(route('rooms.destroy', $room))->assertStatus(419);

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
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
