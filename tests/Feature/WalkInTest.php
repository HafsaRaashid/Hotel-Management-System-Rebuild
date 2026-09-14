<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-004 (BL-018/BL-019). AC-N references are to
 * .specclaw/changes/005-booking-stay-lifecycle/spec.md.
 */
class WalkInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // BL-011 (change 007-admin-authentication): every admin route now
        // requires an authenticated session. Acts as the seeded admin user
        // (see database/migrations/2026_09_14_000006_create_users_table.php).
        $this->actingAs(User::first());
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'room' => 'Single_101',
            'category_id' => RoomCategory::first()->id,
            'status' => 0,
        ], $overrides));
    }

    private function validPayload(Room $room, array $overrides = []): array
    {
        return array_merge([
            'room_id' => $room->id,
            'name' => 'Walk-In Guest',
            'mail' => 'walkin@example.com',
            'phone' => '5553334444',
            'adult' => 1,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-03',
            'message' => '',
        ], $overrides);
    }

    public function test_available_list_shows_only_status_zero_rooms(): void
    {
        $available = $this->makeRoom(['room' => 'Available_1', 'status' => 0]);
        $this->makeRoom(['room' => 'Occupied_1', 'status' => 1]);

        $response = $this->get(route('walk-in.available'));

        $response->assertSee('Available_1');
        $response->assertDontSee('Occupied_1');
    }

    public function test_store_creates_checked_in_booking_and_occupies_room(): void
    {
        $room = $this->makeRoom();

        $this->post(route('walk-in.store'), $this->validPayload($room))
            ->assertRedirect(route('walk-in.available'));

        $this->assertSame(1, Booking::count());
        $booking = Booking::first();
        $this->assertSame(Booking::STATUS_CHECKED_IN, $booking->status);
        $this->assertSame($room->id, $booking->room_id);
        $this->assertGreaterThanOrEqual(0, $booking->ref_no);
        $this->assertLessThanOrEqual(999999999, $booking->ref_no);

        $room->refresh();
        $this->assertSame(1, $room->status);
    }

    public function test_store_dedups_customer_by_phone(): void
    {
        $room = $this->makeRoom();
        $customer = Customer::create([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Existing',
            'mail' => 'existing@example.com',
            'phone' => '5553334444',
            'address' => '1 Test St',
            'charges' => 0,
        ]);

        $this->post(route('walk-in.store'), $this->validPayload($room));

        $this->assertSame(1, Customer::count());
        $this->assertSame($customer->id, Booking::first()->customer_id);
    }

    public function test_store_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $room = $this->makeRoom();

        $this->post(route('walk-in.store'), $this->validPayload($room))->assertStatus(419);

        $this->assertSame(0, Booking::count());
        $room->refresh();
        $this->assertSame(0, $room->status);
    }
}
