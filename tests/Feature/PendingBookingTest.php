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
 * MOD-004 (BL-016/BL-017). AC-N references are to
 * .specclaw/changes/005-booking-stay-lifecycle/spec.md.
 */
class PendingBookingTest extends TestCase
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

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'address' => '1 Test St',
            'charges' => 0,
        ]);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        $category = RoomCategory::where('name', 'Single Room')->firstOrFail();

        return Booking::create(array_merge([
            'ref_no' => Booking::generateUniqueRefNo(),
            'customer_id' => $this->makeCustomer()->id,
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'category_id' => $category->id,
            'adult' => 2,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-05',
            'days_of_stay' => 4,
            'status' => Booking::STATUS_BOOKED,
            'message' => null,
        ], $overrides));
    }

    public function test_pending_list_shows_only_status_zero_bookings(): void
    {
        $this->makeBooking(['status' => Booking::STATUS_BOOKED, 'ref_no' => 111]);
        $this->makeBooking(['status' => Booking::STATUS_CHECKED_IN, 'ref_no' => 222]);

        $response = $this->get(route('bookings.pending'));

        $response->assertSee('111');
        $response->assertDontSee('222');
    }

    public function test_cancel_sets_status_to_cancelled_without_deleting(): void
    {
        $booking = $this->makeBooking();

        $this->delete(route('bookings.pending.destroy', $booking))
            ->assertRedirect(route('bookings.pending'));

        $booking->refresh();
        $this->assertSame(Booking::STATUS_CANCELLED, $booking->status);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    public function test_convert_assigns_matching_category_available_room(): void
    {
        $booking = $this->makeBooking();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $booking->category_id, 'status' => 0]);

        $this->post(route('bookings.pending.convert', $booking), ['room_id' => $room->id])
            ->assertRedirect(route('bookings.pending'));

        $booking->refresh();
        $room->refresh();
        $this->assertSame(Booking::STATUS_CHECKED_IN, $booking->status);
        $this->assertSame($room->id, $booking->room_id);
        $this->assertSame(1, $room->status);
    }

    public function test_convert_rejects_wrong_category_room(): void
    {
        $booking = $this->makeBooking();
        $wrongCategory = RoomCategory::where('name', 'Deluxe Room')->firstOrFail();
        $room = Room::create(['room' => 'Deluxe_301', 'category_id' => $wrongCategory->id, 'status' => 0]);

        $this->post(route('bookings.pending.convert', $booking), ['room_id' => $room->id])
            ->assertSessionHasErrors('room_id');

        $booking->refresh();
        $this->assertSame(Booking::STATUS_BOOKED, $booking->status);
    }

    public function test_convert_rejects_unavailable_room(): void
    {
        $booking = $this->makeBooking();
        $room = Room::create(['room' => 'Single_102', 'category_id' => $booking->category_id, 'status' => 1]);

        $this->post(route('bookings.pending.convert', $booking), ['room_id' => $room->id])
            ->assertSessionHasErrors('room_id');

        $booking->refresh();
        $this->assertSame(Booking::STATUS_BOOKED, $booking->status);
    }

    public function test_cancel_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $booking = $this->makeBooking();

        $this->delete(route('bookings.pending.destroy', $booking))->assertStatus(419);

        $booking->refresh();
        $this->assertSame(Booking::STATUS_BOOKED, $booking->status);
    }

    public function test_convert_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $booking = $this->makeBooking();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $booking->category_id, 'status' => 0]);

        $this->post(route('bookings.pending.convert', $booking), ['room_id' => $room->id])->assertStatus(419);

        $booking->refresh();
        $this->assertSame(Booking::STATUS_BOOKED, $booking->status);
    }
}
